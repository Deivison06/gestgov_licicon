<?php

namespace App\Services;

use App\Models\PncpContratacaoCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PncpSincronizarService
{
    // Apenas as modalidades relevantes para pesquisa de preço de mercado
    protected const MODALIDADES = [6, 8, 9]; // Pregão Eletrônico, Dispensa, Inexigibilidade

    // Atraso entre páginas para não sobrecarregar a API
    protected int $delayMs;

    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.pncp.base_url', 'https://pncp.gov.br/api/consulta/v1');
        $this->delayMs = (int) config('services.pncp.sync_delay_ms', 1500);
    }

    /**
     * Sincroniza contratações do PNCP para o cache local.
     *
     * @param  int           $meses      Quantos meses retroativos buscar
     * @param  string|null   $uf         Limitar a uma UF (null = todas)
     * @param  callable|null $onProgress Callback(int $total, int $totalAcumulado, int $modalidade, int $pagina)
     * @return array{sincronizados: int, erros: int, paginas: int}
     */
    public function sincronizar(int $meses = 3, ?string $uf = null, ?callable $onProgress = null): array
    {
        $dataFim = now()->format('Ymd');
        $dataIni = now()->subMonths($meses)->format('Ymd');

        return $this->sincronizarIntervalo($dataIni, $dataFim, $uf, $onProgress);
    }

    /**
     * Sincronização incremental: apenas os últimos N dias (para atualização diária).
     */
    public function sincronizarIncremental(int $dias = 2, ?string $uf = null, ?callable $onProgress = null): array
    {
        $dataFim = now()->format('Ymd');
        $dataIni = now()->subDays($dias)->format('Ymd');

        return $this->sincronizarIntervalo($dataIni, $dataFim, $uf, $onProgress);
    }

    private function sincronizarIntervalo(string $dataIni, string $dataFim, ?string $uf, ?callable $onProgress): array
    {
        $totalSincronizados = 0;
        $totalErros         = 0;
        $totalPaginas       = 0;

        // Divide o intervalo em janelas semanais para evitar o problema de paginação ao vivo:
        // a API PNCP recalcula totalPaginas entre requests em ranges longos, causando pages vazias.
        $chunks = $this->gerarJanelasSemanais($dataIni, $dataFim);

        $primeiroRequest = true;

        foreach (self::MODALIDADES as $modalidade) {
            foreach ($chunks as [$chunkIni, $chunkFim]) {
                // Delay antes de toda requisição (exceto a primeira) para respeitar rate limit
                if (!$primeiroRequest) {
                    usleep($this->delayMs * 1000);
                }
                $primeiroRequest = false;

                $resultado = $this->sincronizarModalidade(
                    $modalidade, $chunkIni, $chunkFim, $uf,
                    function (int $lote, int $acumulado, int $pagina, int $paginas) use ($modalidade, $totalSincronizados, $onProgress) {
                        if ($onProgress) {
                            $onProgress($totalSincronizados + $acumulado, $modalidade, $pagina, $paginas);
                        }
                    }
                );

                $totalSincronizados += $resultado['sincronizados'];
                $totalErros         += $resultado['erros'];
                $totalPaginas       += $resultado['paginas'];
            }
        }

        return [
            'sincronizados' => $totalSincronizados,
            'erros'         => $totalErros,
            'paginas'       => $totalPaginas,
        ];
    }

    /**
     * Divide um intervalo de datas em janelas de 7 dias para estabilizar a paginação.
     * @return array<array{string, string}>
     */
    private function gerarJanelasSemanais(string $dataIni, string $dataFim): array
    {
        $inicio = \Carbon\Carbon::createFromFormat('Ymd', $dataIni)->startOfDay();
        $fim    = \Carbon\Carbon::createFromFormat('Ymd', $dataFim)->startOfDay();

        $chunks = [];
        $atual  = $inicio->copy();

        while ($atual->lte($fim)) {
            $fimChunk = $atual->copy()->addDays(6);
            if ($fimChunk->gt($fim)) {
                $fimChunk = $fim->copy();
            }
            $chunks[] = [$atual->format('Ymd'), $fimChunk->format('Ymd')];
            $atual->addDays(7);
        }

        return $chunks;
    }

    private function sincronizarModalidade(
        int      $modalidade,
        string   $dataIni,
        string   $dataFim,
        ?string  $uf,
        callable $onProgress
    ): array {
        $sincronizados = 0;
        $erros         = 0;
        $pagina        = 1;
        $tamanhoPagina = 50;
        $totalPaginas  = null;

        while (true) {
            // Delay entre todas as requisições (inclusive chunks vazios) para não bater rate limit
            if ($pagina > 1) {
                usleep($this->delayMs * 1000);
            }

            $params = array_filter([
                'dataInicial'                 => $dataIni,
                'dataFinal'                   => $dataFim,
                'codigoModalidadeContratacao' => $modalidade,
                'pagina'                      => $pagina,
                'tamanhoPagina'               => $tamanhoPagina,
                'uf'                          => $uf,
            ], fn($v) => $v !== null && $v !== '');

            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; GestGov/1.0)',
                    'Accept'     => 'application/json',
                ])->timeout(30)->get($this->baseUrl . '/contratacoes/publicacao', $params);

                if ($response->failed()) {
                    Log::warning('PNCP Sync: falha na API', [
                        'status'     => $response->status(),
                        'body'       => substr($response->body(), 0, 200),
                        'modalidade' => $modalidade,
                        'pagina'     => $pagina,
                    ]);
                    $erros++;
                    break;
                }

                $json         = $response->json();
                $items        = $json['data'] ?? [];
                $totalPaginas = $json['totalPaginas'] ?? $pagina;

                if (empty($items)) {
                    break;
                }

                $lote = $this->prepararLote($items);
                $this->upsertLote($lote);
                $sincronizados += count($lote);

                $onProgress(count($lote), $sincronizados, $pagina, $totalPaginas);

            } catch (Exception $e) {
                Log::error('PNCP Sync: exceção', [
                    'message'    => $e->getMessage(),
                    'modalidade' => $modalidade,
                    'pagina'     => $pagina,
                ]);
                $erros++;
                break;
            }

            $pagina++;

            if ($totalPaginas !== null && $pagina > $totalPaginas) {
                break;
            }
        }

        return [
            'sincronizados' => $sincronizados,
            'erros'         => $erros,
            'paginas'       => $pagina - 1,
        ];
    }

    private function prepararLote(array $items): array
    {
        $now = now();

        return array_map(function ($item) use ($now) {
            $dataPublicacao = null;
            if (!empty($item['dataPublicacaoPncp'])) {
                $dataPublicacao = substr($item['dataPublicacaoPncp'], 0, 10);
            }

            $dataResultado = null;
            if (!empty($item['dataResultadoCompra'])) {
                $dataResultado = substr($item['dataResultadoCompra'], 0, 10);
            }

            return [
                'cnpj'                   => $item['orgaoEntidade']['cnpj'] ?? '',
                'ano_compra'             => (int) ($item['anoCompra'] ?? 0),
                'sequencial_compra'      => (int) ($item['sequencialCompra'] ?? 0),
                'modalidade_codigo'      => isset($item['modalidadeId']) ? (int) $item['modalidadeId'] : null,
                'modalidade_nome'        => $item['modalidadeNome'] ?? null,
                'objeto'                 => $item['objetoCompra'] ?? null,
                'uf'                     => $item['unidadeOrgao']['ufSigla'] ?? null,
                'municipio'              => $item['unidadeOrgao']['municipioNome'] ?? null,
                'orgao_nome'             => $item['orgaoEntidade']['razaoSocial'] ?? null,
                'codigo_situacao_compra' => isset($item['situacaoCompraId']) ? (int) $item['situacaoCompraId'] : null,
                'situacao_nome'          => $item['situacaoCompraNome'] ?? null,
                'valor_total_estimado'   => isset($item['valorTotalEstimado']) ? (float) $item['valorTotalEstimado'] : null,
                'valor_total_homologado' => isset($item['valorTotalHomologado']) ? (float) $item['valorTotalHomologado'] : null,
                'data_publicacao_pncp'   => $dataPublicacao,
                'data_resultado_compra'  => $dataResultado,
                'synced_at'              => $now,
                'created_at'             => $now,
                'updated_at'             => $now,
            ];
        }, $items);
    }

    private function upsertLote(array $lote): void
    {
        if (empty($lote)) {
            return;
        }

        PncpContratacaoCache::upsert(
            $lote,
            ['cnpj', 'ano_compra', 'sequencial_compra'],
            [
                'modalidade_codigo',
                'modalidade_nome',
                'objeto',
                'uf',
                'municipio',
                'orgao_nome',
                'codigo_situacao_compra',
                'situacao_nome',
                'valor_total_estimado',
                'valor_total_homologado',
                'data_publicacao_pncp',
                'data_resultado_compra',
                'synced_at',
                'updated_at',
            ]
        );
    }
}
