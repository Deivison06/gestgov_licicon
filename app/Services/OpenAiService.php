<?php

namespace App\Services;

use App\Exceptions\OpenAiServiceException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente fino da OpenAI Chat Completions.
 *
 * Responsabilidade única: enviar (system + user) prompts e devolver
 * o texto resposta já limpo. Não conhece o domínio (justificativa, ETP etc.).
 */
class OpenAiService
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $baseUrl,
        private readonly string $model,
        private readonly int $timeout,
        private readonly int $maxTokens,
        private readonly float $temperature,
    ) {}

    public function disponivel(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * @return array{conteudo: string, usage: array, latency_ms: int}
     */
    public function gerarTexto(string $systemPrompt, string $userPrompt, array $options = []): array
    {
        if (!$this->disponivel()) {
            throw new OpenAiServiceException(
                OpenAiServiceException::INVALID_KEY,
                'OPENAI_API_KEY não configurada.'
            );
        }

        $payload = [
            'model'       => $options['model'] ?? $this->model,
            'temperature' => $options['temperature'] ?? $this->temperature,
            // `max_completion_tokens` substitui o antigo `max_tokens`: é exigido pelos
            // modelos mais novos (família GPT-5 / o-series) e aceito pelos demais.
            'max_completion_tokens' => $options['max_tokens'] ?? $this->maxTokens,
            'messages'    => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
        ];

        $inicio = microtime(true);

        $response = $this->enviarRequisicao($payload);

        // Self-healing: alguns modelos (GPT-5 / o-series) só aceitam temperature=1
        // (default) e recusam valores custom. Se for esse o caso, reenvia sem a
        // temperature — funciona para esses modelos e para os que aceitam custom.
        if ($response->status() === 400 && $this->erroDeParametro($response, 'temperature')) {
            unset($payload['temperature']);
            $response = $this->enviarRequisicao($payload);
        }

        $latencyMs = (int) round((microtime(true) - $inicio) * 1000);

        if ($response->status() === 401) {
            throw new OpenAiServiceException(
                OpenAiServiceException::INVALID_KEY,
                'API key da OpenAI rejeitada (401).'
            );
        }

        if ($response->status() === 429) {
            throw new OpenAiServiceException(
                OpenAiServiceException::RATE_LIMIT,
                'Rate limit da OpenAI atingido (429).'
            );
        }

        if ($response->failed()) {
            $body = $response->body();
            Log::channel('ia')->error('OpenAI respondeu erro', [
                'status' => $response->status(),
                'body'   => mb_substr($body, 0, 500),
            ]);
            throw new OpenAiServiceException(
                OpenAiServiceException::UNKNOWN,
                'OpenAI retornou erro ' . $response->status() . '.'
            );
        }

        $data = $response->json();
        $choice = $data['choices'][0] ?? null;

        if (!$choice) {
            throw new OpenAiServiceException(
                OpenAiServiceException::EMPTY_RESPONSE,
                'Resposta sem choices.'
            );
        }

        if (($choice['finish_reason'] ?? null) === 'content_filter') {
            throw new OpenAiServiceException(
                OpenAiServiceException::CONTENT_FILTER,
                'Conteúdo bloqueado pelo filtro da OpenAI.'
            );
        }

        $conteudoBruto = (string) ($choice['message']['content'] ?? '');
        $conteudoLimpo = $this->limparConteudo($conteudoBruto);

        if ($conteudoLimpo === '') {
            throw new OpenAiServiceException(
                OpenAiServiceException::EMPTY_RESPONSE,
                'OpenAI devolveu conteúdo vazio.'
            );
        }

        return [
            'conteudo'   => $conteudoLimpo,
            'usage'      => $data['usage'] ?? [],
            'latency_ms' => $latencyMs,
        ];
    }

    /**
     * Envia o payload ao endpoint de chat completions, tratando falhas de
     * conexão como exceções semânticas. Reutilizado pelo retry de self-healing.
     */
    private function enviarRequisicao(array $payload): \Illuminate\Http\Client\Response
    {
        try {
            return Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->retry(2, 250, function ($exception) {
                    // Retry só em falhas transitórias (conexão / 5xx),
                    // nunca em 401/403/429 — esses precisam subir limpos.
                    return $exception instanceof ConnectionException;
                }, throw: false)
                ->acceptJson()
                ->asJson()
                ->post(rtrim($this->baseUrl, '/') . '/chat/completions', $payload);
        } catch (ConnectionException $e) {
            throw new OpenAiServiceException(
                OpenAiServiceException::TIMEOUT,
                'Timeout ou falha de conexão com a OpenAI.',
                $e
            );
        } catch (\Throwable $e) {
            throw new OpenAiServiceException(
                OpenAiServiceException::NETWORK,
                'Falha de rede ao chamar OpenAI: ' . $e->getMessage(),
                $e
            );
        }
    }

    /**
     * Detecta se a resposta 400 da OpenAI reclama de um parâmetro específico
     * (ex.: 'temperature'), pelo campo error.param ou pela mensagem.
     */
    private function erroDeParametro(\Illuminate\Http\Client\Response $response, string $param): bool
    {
        $erro = $response->json('error') ?? [];

        if (($erro['param'] ?? null) === $param) {
            return true;
        }

        return str_contains(mb_strtolower((string) ($erro['message'] ?? '')), $param);
    }

    /**
     * Pós-processamento defensivo: remove introduções enviesadas, converte
     * Markdown remanescente em HTML e sanitiza o HTML mantendo só a whitelist
     * de tags seguras para o TinyMCE.
     */
    private function limparConteudo(string $texto): string
    {
        $texto = trim($texto);

        // Remove cercas de código markdown ```html ... ``` que a IA às vezes adiciona
        $texto = preg_replace('/^```[a-z]*\s*/im', '', $texto) ?? $texto;
        $texto = preg_replace('/```\s*$/m', '', $texto) ?? $texto;

        // Remove wrappers <html>/<body> caso a IA tenha adicionado
        $texto = preg_replace('#</?(?:html|body|head|!doctype)[^>]*>#i', '', $texto) ?? $texto;

        // Converte Markdown residual em HTML (defesa contra IA escapando do prompt)
        // **negrito** → <strong>negrito</strong>
        $texto = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $texto) ?? $texto;
        // __negrito__ → <strong>negrito</strong>
        $texto = preg_replace('/__(.+?)__/s', '<strong>$1</strong>', $texto) ?? $texto;
        // # Título → <p><strong>Título</strong></p>
        $texto = preg_replace('/^#{1,6}\s+(.+?)$/m', '<p><strong>$1</strong></p>', $texto) ?? $texto;

        // Remove primeira linha quando começa com fórmulas "Aqui está:", "Segue:" etc.
        // (verifica antes de eventual wrapping em <p>)
        $linhas = preg_split('/\r?\n/', $texto);
        if (!empty($linhas)) {
            $primeira = trim(strip_tags($linhas[0]));
            $padroes = [
                '/^(aqui está|aqui esta|segue|claro|vou|pronto|certo)[^\n]*:?$/i',
                '/^(texto|justificativa|descrição)\s*:$/i',
            ];
            foreach ($padroes as $regex) {
                if (preg_match($regex, $primeira)) {
                    array_shift($linhas);
                    break;
                }
            }
            $texto = implode("\n", $linhas);
        }

        // Sanitização: mantém só tags da whitelist
        $allowed = '<p><br><strong><b><em><i><u><ul><ol><li>';
        $texto = strip_tags($texto, $allowed);

        // Remove TODOS os atributos das tags remanescentes (class, style, onclick, etc.)
        $texto = preg_replace(
            '/<(\/?)(p|br|strong|b|em|i|u|ul|ol|li)(\s[^>]*)?>/i',
            '<$1$2>',
            $texto
        ) ?? $texto;

        $texto = trim($texto);

        // Se a IA retornou texto puro (sem tags), envolve em <p> para garantir formatação
        if ($texto !== '' && !preg_match('/<(p|ul|ol|br|strong|em)\b/i', $texto)) {
            $paragrafos = preg_split('/\n{2,}/', $texto);
            $texto = implode('', array_map(
                fn ($p) => '<p>' . nl2br(trim($p), false) . '</p>',
                array_filter($paragrafos, fn ($p) => trim($p) !== '')
            ));
        }

        return $texto;
    }
}
