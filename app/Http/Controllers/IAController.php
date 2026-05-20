<?php

namespace App\Http\Controllers;

use App\Exceptions\OpenAiServiceException;
use App\Http\Requests\GerarConteudoIaRequest;
use App\Models\Processo;
use App\Services\IA\IaContextoService;
use App\Services\OpenAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class IAController extends Controller
{
    public function __construct(
        private readonly OpenAiService $openAi,
        private readonly IaContextoService $contexto,
    ) {}

    public function gerarConteudo(GerarConteudoIaRequest $request): JsonResponse
    {
        $campo     = $request->string('campo')->toString();
        $instrucao = $request->string('instrucao')->toString();
        $processo  = $request->filled('processo_id')
            ? Processo::find($request->integer('processo_id'))
            : null;

        try {
            $prompts = $this->contexto->montarPrompt($campo, $instrucao, $processo);

            $resultado = $this->openAi->gerarTexto(
                systemPrompt: $prompts['system'],
                userPrompt:   $prompts['user'],
            );

            Log::channel('ia')->info('IA gerou conteúdo', [
                'user_id'        => $request->user()?->id,
                'campo'          => $campo,
                'processo_id'    => $processo?->id,
                'instrucao_len'  => mb_strlen($instrucao),
                'prompt_tokens'  => $resultado['usage']['prompt_tokens']     ?? null,
                'completion_tokens' => $resultado['usage']['completion_tokens'] ?? null,
                'latency_ms'     => $resultado['latency_ms'],
            ]);

            return response()->json([
                'success'  => true,
                'campo'    => $campo,
                'conteudo' => $resultado['conteudo'],
            ]);
        } catch (OpenAiServiceException $e) {
            return $this->responderErroIa($e, $campo, $request->user()?->id);
        } catch (\Throwable $e) {
            Log::channel('ia')->error('Falha inesperada ao gerar conteúdo IA', [
                'campo' => $campo,
                'erro'  => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'code'    => 'INTERNAL',
                'message' => 'Erro inesperado ao gerar conteúdo. Tente novamente.',
            ], 500);
        }
    }

    private function responderErroIa(OpenAiServiceException $e, string $campo, ?int $userId): JsonResponse
    {
        $code = $e->getCodeSemantico();

        Log::channel('ia')->warning('IA falhou', [
            'code'    => $code,
            'campo'   => $campo,
            'user_id' => $userId,
            'erro'    => $e->getMessage(),
        ]);

        [$status, $mensagem] = match ($code) {
            OpenAiServiceException::TIMEOUT        => [408, 'A IA está demorando para responder. Tente novamente em alguns segundos.'],
            OpenAiServiceException::RATE_LIMIT     => [429, 'Limite de uso da IA atingido. Aguarde alguns segundos e tente de novo.'],
            OpenAiServiceException::INVALID_KEY    => [503, 'Recurso de IA indisponível no momento. Contate o suporte.'],
            OpenAiServiceException::CONTENT_FILTER => [422, 'Sua instrução foi bloqueada por política de uso. Reformule sem termos sensíveis.'],
            OpenAiServiceException::EMPTY_RESPONSE => [502, 'A IA não conseguiu gerar uma resposta. Reformule sua instrução.'],
            OpenAiServiceException::NETWORK        => [503, 'Falha de rede ao conectar com a IA. Tente novamente em instantes.'],
            default                                => [500, 'Erro ao gerar conteúdo com a IA. Tente novamente.'],
        };

        return response()->json([
            'success' => false,
            'code'    => $code,
            'message' => $mensagem,
        ], $status);
    }
}
