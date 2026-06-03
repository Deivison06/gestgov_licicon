<?php

namespace App\Services;

use App\Models\PncpContratacaoCache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PncpService
{
    protected string $baseUrl;
    protected string $searchUrl;
    protected int $cacheTtl;

    public function __construct()
    {
        $this->baseUrl   = config('services.pncp.base_url');
        $this->searchUrl = config('services.pncp.search_url');
        $this->cacheTtl  = config('services.pncp.cache_ttl');
    }

    // =========================================================================
    // MÉTODOS PARA O MODAL DO ETP (comportamento original preservado)
    // =========================================================================

    /**
     * Busca contratações com status "recebendo_proposta" — para o modal do ETP.
     */
    public function buscarContratacoes(string $termo, array $filtros = [], int $pagina = 1, int $tamanho = 10): array
    {
        $cacheKey = 'pncp_search_' . md5($termo . serialize($filtros) . $pagina . $tamanho);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($termo, $filtros, $pagina, $tamanho) {
            try {
                $response = $this->httpClient()
                    ->get($this->searchUrl, [
                        'q'               => $termo,
                        'tipos_documento' => 'edital',
                        'pagina'          => $pagina,
                        'tam_pagina'      => $tamanho,
                        'status'          => 'recebendo_proposta',
                    ]);

                if ($response->status() === 502) {
                    return ['error' => 'O serviço de busca do PNCP está temporariamente instável (502). Tente novamente em instantes.'];
                }

                if ($response->failed()) {
                    Log::error('Erro ao consultar API PNCP Search', [
                        'status' => $response->status(),
                        'termo'  => $termo,
                    ]);
                    return ['error' => 'Falha na comunicação com o serviço de busca do PNCP'];
                }

                $json = $response->json();

                return [
                    'data'          => $this->normalizarContratacoes($json['items'] ?? []),
                    'totalRegistros' => $json['total'] ?? 0,
                    'totalPaginas'  => ceil(($json['total'] ?? 0) / $tamanho),
                ];
            } catch (Exception $e) {
                Log::error('Exceção ao consultar API PNCP Search', ['message' => $e->getMessage(), 'termo' => $termo]);
                return ['error' => 'Erro interno ao processar busca no PNCP'];
            }
        });
    }

    /**
     * Busca itens de uma contratação — para o modal do ETP.
     * Prioriza valorUnitarioEstimado; valorUnitarioHomologado como fallback.
     */
    public function buscarItensContratacao(string $cnpj, string $ano, string $sequencial): array
    {
        $cacheKey = "pncp_itens_{$cnpj}_{$ano}_{$sequencial}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($cnpj, $ano, $sequencial) {
            try {
                $url = "https://pncp.gov.br/api/pncp/v1/orgaos/{$cnpj}/compras/{$ano}/{$sequencial}/itens";

                $response = $this->httpClient(timeout: 10)
                    ->get($url, ['pagina' => 1, 'tamanhoPagina' => 100]);

                if ($response->failed()) {
                    return ['error' => 'Falha ao buscar itens do PNCP (Status: ' . $response->status() . ')'];
                }

                return array_map(function ($item) {
                    return array_merge($item, [
                        'valorUnitario' => $item['valorUnitarioEstimado'] ?? $item['valorUnitarioHomologado'] ?? 0,
                    ]);
                }, $response->json() ?? []);
            } catch (Exception $e) {
                return ['error' => 'Erro ao processar itens: ' . $e->getMessage()];
            }
        });
    }

    // =========================================================================
    // MÉTODOS PARA PESQUISA DE PREÇO DE MERCADO (nova regra de negócio)
    // =========================================================================

    /**
     * Busca contratações para pesquisa de preço de mercado.
     * Usa /api/search/ para busca textual. Erros não são cacheados para permitir retry imediato.
     */
    public function buscarContratacoesMercado(string $termo, array $filtros = [], int $pagina = 1, int $tamanho = 10): array
    {
        $cacheKey = 'pncp_mercado_' . md5($termo . serialize($filtros) . $pagina . $tamanho);

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            // O endpoint /api/search/ só suporta status='recebendo_proposta'.
            // Outros valores (homologado, resultado_homologado, etc.) são ignorados pela API.
            // Para filtrar por Resultado Homologado, use buscarContratacoesFiltradas (codigoSituacaoCompra).
            $statusMap  = ['2' => 'recebendo_proposta'];
            $statusPncp = $statusMap[(string) ($filtros['situacao'] ?? '')] ?? null;

            $params = array_filter([
                'q'               => $termo,
                'tipos_documento' => 'edital',
                'pagina'          => $pagina,
                'tam_pagina'      => $tamanho,
                'status'          => $statusPncp,
            ]);

            // Timeout maior (API do gov pode estar lenta); 1 retry com espera para não triplicar a espera
            $response = $this->httpClient(timeout: 30)->retry(1, 2000)->get($this->searchUrl, $params);

            if ($response->status() === 502) {
                return ['error' => 'O serviço PNCP está temporariamente instável (502). Tente novamente em instantes.'];
            }

            if ($response->failed()) {
                Log::error('Erro ao consultar PNCP Mercado', ['status' => $response->status(), 'termo' => $termo]);
                return ['error' => 'Falha na comunicação com o PNCP'];
            }

            $json = $response->json();

            $result = [
                'data'           => $this->normalizarContratacoes($json['items'] ?? []),
                'totalRegistros' => $json['total'] ?? 0,
                'totalPaginas'   => ceil(($json['total'] ?? 0) / $tamanho),
                'paginaAtual'    => $pagina,
            ];

            Cache::put($cacheKey, $result, $this->cacheTtl);

            return $result;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('Timeout ao consultar PNCP Mercado', ['message' => $e->getMessage(), 'termo' => $termo]);
            return ['error' => 'A API do PNCP está lenta no momento. Aguarde alguns segundos e tente novamente.'];
        } catch (Exception $e) {
            Log::error('Exceção ao consultar PNCP Mercado', ['message' => $e->getMessage(), 'termo' => $termo]);
            return ['error' => 'Erro interno ao processar busca de mercado no PNCP'];
        }
    }

    /**
     * Busca contratações via /consulta/v1 — requer modalidade + período.
     * Filtros reais server-side: UF, modalidade, datas. Paginação nativa do endpoint.
     */
    public function buscarContratacoesFiltradas(string $termo, array $filtros, int $pagina = 1, int $tamanho = 10): array
    {
        $cacheKey = 'pncp_filtrado_' . md5($termo . serialize($filtros) . $pagina . $tamanho);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($termo, $filtros, $pagina, $tamanho) {
            try {
                $tamanhoApi = max(10, $tamanho);

                $params = array_filter([
                    'dataInicial'                 => str_replace('-', '', $filtros['data_inicial'] ?? ''),
                    'dataFinal'                   => str_replace('-', '', $filtros['data_final'] ?? ''),
                    'codigoModalidadeContratacao' => (int) ($filtros['modalidade'] ?? 0),
                    'codigoSituacaoCompra'        => !empty($filtros['situacao']) ? (int) $filtros['situacao'] : null,
                    'pagina'                      => $pagina,
                    'tamanhoPagina'               => $tamanhoApi,
                    'uf'                          => $filtros['uf'] ?? null,
                ]);

                $url      = $this->baseUrl . '/contratacoes/publicacao';
                $response = $this->httpClient(timeout: 20)->get($url, $params);

                if ($response->status() === 400) {
                    $msg = $response->json()['message'] ?? 'Parâmetros inválidos.';
                    return ['error' => "Filtro inválido: {$msg}"];
                }

                if ($response->failed()) {
                    Log::error('Erro ao consultar PNCP Filtrado', ['status' => $response->status()]);
                    return ['error' => 'Falha na consulta estruturada do PNCP (Status: ' . $response->status() . ')'];
                }

                $json    = $json = $response->json();
                $total   = $json['totalRegistros'] ?? 0;
                $paginas = $json['totalPaginas'] ?? (int) ceil($total / $tamanhoApi);

                return [
                    'data'           => $this->normalizarContratacoesFiltradas($json['data'] ?? []),
                    'totalRegistros' => $total,
                    'totalPaginas'   => $paginas,
                    'paginaAtual'    => $pagina,
                    'modoFiltrado'   => true,
                ];
            } catch (ConnectionException $e) {
                Log::warning('Timeout ao consultar PNCP Filtrado', ['message' => $e->getMessage(), 'filtros' => $filtros]);
                return ['error' => 'A consulta ao PNCP excedeu o tempo limite. Reduza o período de busca (tente 3 meses) ou selecione uma UF específica para diminuir o volume de resultados.'];
            } catch (Exception $e) {
                Log::error('Exceção ao consultar PNCP Filtrado', ['message' => $e->getMessage()]);
                return ['error' => 'Erro ao realizar a consulta estruturada. Tente novamente ou ajuste os filtros.'];
            }
        });
    }

    /**
     * Busca itens de uma contratação para pesquisa de mercado.
     * Prioriza valorUnitarioHomologado (valor real pago); estimado como fallback.
     * Expõe todos os campos necessários para o card da Pesquisa de Preços.
     */
    public function buscarItensContratacaoMercado(string $cnpj, string $ano, string $sequencial): array
    {
        $cacheKey = "pncp_itens_mercado_{$cnpj}_{$ano}_{$sequencial}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($cnpj, $ano, $sequencial) {
            try {
                $url = "https://pncp.gov.br/api/pncp/v1/orgaos/{$cnpj}/compras/{$ano}/{$sequencial}/itens";

                $response = $this->httpClient(timeout: 10)
                    ->get($url, ['pagina' => 1, 'tamanhoPagina' => 100]);

                if ($response->failed()) {
                    return ['error' => 'Falha ao buscar itens do PNCP (Status: ' . $response->status() . ')'];
                }

                return array_map(function ($item) {
                    $homologado = $item['valorUnitarioHomologado'] ?? null;
                    $estimado   = $item['valorUnitarioEstimado'] ?? null;

                    return array_merge($item, [
                        'valorUnitario'      => $homologado ?? $estimado ?? 0,
                        'tipoValor'          => $homologado !== null ? 'homologado' : 'estimado',
                        'valorEstimado'      => $estimado,
                        'valorHomologado'    => $homologado,
                        'nomeFornecedor'     => $item['nomeRazaoSocialFornecedor'] ?? null,
                        'cnpjFornecedorNorm' => $item['cnpjFornecedor'] ?? null,
                        'situacaoItem'       => $item['situacaoCompraItemNome'] ?? null,
                        'tipoItem'           => $item['materialOuServicoNome'] ?? self::extrairMaterialOuServico($item),
                        'categoriaItem'      => is_array($item['categoriaItem'] ?? null) ? ($item['categoriaItem']['nome'] ?? null) : null,
                    ]);
                }, $response->json() ?? []);
            } catch (Exception $e) {
                return ['error' => 'Erro ao processar itens de mercado: ' . $e->getMessage()];
            }
        });
    }

    /**
     * Busca o detalhe completo de uma contratação específica.
     * Retorna: numeroProcesso, valorTotalEstimado, valorTotalHomologado,
     *          dataResultadoCompra, situacaoCompra, linkSistemaOrigem.
     */
    public function buscarDetalheContratacao(string $cnpj, string $ano, string $sequencial): array
    {
        $cacheKey = "pncp_compra_{$cnpj}_{$ano}_{$sequencial}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($cnpj, $ano, $sequencial) {
            try {
                $url = "{$this->baseUrl}/orgaos/{$cnpj}/compras/{$ano}/{$sequencial}";

                $response = $this->httpClient(timeout: 30)->get($url);

                if ($response->status() === 404) {
                    return ['error' => 'Contratação não encontrada no PNCP.'];
                }

                if ($response->failed()) {
                    return ['error' => 'Falha ao buscar detalhe da contratação (Status: ' . $response->status() . ')'];
                }

                $c = $response->json();

                return [
                    'numeroProcesso'       => $c['numeroCompra'] ?? $c['processo'] ?? $c['numeroProcesso'] ?? null,
                    'valorTotalEstimado'   => $c['valorTotalEstimado'] ?? null,
                    'valorTotalHomologado' => $c['valorTotalHomologado'] ?? null,
                    'dataResultadoCompra'  => $c['dataResultadoCompra'] ?? null,
                    'situacaoCompra'       => $c['situacaoCompra']['nome'] ?? ($c['situacaoCompraNome'] ?? null),
                    'modalidadeNome'       => $c['modalidade']['nome'] ?? ($c['modalidadeNome'] ?? null),
                    'objeto'               => $c['objetoCompra'] ?? ($c['objeto'] ?? null),
                    'linkSistemaOrigem'    => $c['linkSistemaOrigem'] ?? null,
                    'orgaoNome'            => $c['orgaoEntidade']['razaoSocial'] ?? null,
                ];
            } catch (Exception $e) {
                Log::error('Exceção ao buscar detalhe contratação PNCP', [
                    'message'    => $e->getMessage(),
                    'cnpj'       => $cnpj,
                    'ano'        => $ano,
                    'sequencial' => $sequencial,
                ]);
                return ['error' => 'Erro ao processar detalhe da contratação: ' . $e->getMessage()];
            }
        });
    }

    /**
     * Busca os resultados (vencedores) de um item específico.
     * Retorna array de vencedores com niFornecedor, nomeRazaoSocialFornecedor, valorUnitarioHomologado, etc.
     */
    public function buscarResultadosItem(string $cnpj, string $ano, string $sequencial, int $itemNumero): array
    {
        $cacheKey = "pncp_resultados_item_{$cnpj}_{$ano}_{$sequencial}_{$itemNumero}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($cnpj, $ano, $sequencial, $itemNumero) {
            try {
                $url = "https://pncp.gov.br/api/pncp/v1/orgaos/{$cnpj}/compras/{$ano}/{$sequencial}/itens/{$itemNumero}/resultados";

                $response = $this->httpClient(timeout: 20)->get($url);

                if ($response->failed()) {
                    return [];
                }

                return $response->json() ?? [];
            } catch (Exception $e) {
                Log::error('Erro ao buscar resultados do item PNCP', [
                    'message'    => $e->getMessage(),
                    'cnpj'       => $cnpj,
                    'ano'        => $ano,
                    'sequencial' => $sequencial,
                    'item'       => $itemNumero
                ]);
                return [];
            }
        });
    }

    // =========================================================================
    // CACHE LOCAL DE CONTRATAÇÕES (busca SQL + filtros combinados)
    // =========================================================================

    /**
     * Busca contratações no cache local usando SQL.
     * Permite combinar termo livre (LIKE em objeto) + todos os filtros sem restrições de endpoint.
     * Retorna no mesmo formato de buscarContratacoesFiltradas para compatibilidade com o JS.
     */
    public function buscarNoCache(string $termo, array $filtros = [], int $pagina = 1, int $tamanho = 10): array
    {
        $query = PncpContratacaoCache::query();

        if (!empty($filtros['modalidade'])) {
            $query->where('modalidade_codigo', (int) $filtros['modalidade']);
        }
        if (!empty($filtros['uf'])) {
            $query->where('uf', strtoupper($filtros['uf']));
        }
        if (!empty($filtros['situacao'])) {
            $query->where('codigo_situacao_compra', (int) $filtros['situacao']);
        }
        if (!empty($filtros['data_inicial'])) {
            $query->whereDate('data_publicacao_pncp', '>=', $filtros['data_inicial']);
        }
        if (!empty($filtros['data_final'])) {
            $query->whereDate('data_publicacao_pncp', '<=', $filtros['data_final']);
        }

        if ($termo) {
            $query->where('objeto', 'LIKE', '%' . $termo . '%');
        }

        $total = $query->count();

        $contratacoes = $query
            ->orderByDesc('data_publicacao_pncp')
            ->forPage($pagina, $tamanho)
            ->get();

        return [
            'data'           => $this->normalizarContratacoesDaCache($contratacoes),
            'totalRegistros' => $total,
            'totalPaginas'   => (int) ceil($total / $tamanho),
            'paginaAtual'    => $pagina,
            'modoFiltrado'   => true,
            'viaCache'       => true,
        ];
    }

    /**
     * Retorna true se o cache local possui registros.
     * Usa metadados em cache por 5 minutos para evitar consulta ao banco a cada request.
     */
    public function cacheEstaDisponivel(): bool
    {
        return Cache::remember('pncp_cache_disponivel', 300, function () {
            return PncpContratacaoCache::exists();
        });
    }

    /**
     * Retorna estatísticas sobre o estado do cache local.
     */
    public function statusCache(): array
    {
        $ultimoSync = PncpContratacaoCache::max('synced_at');
        $total      = PncpContratacaoCache::count();

        return [
            'ativo'              => $total > 0,
            'total_contratacoes' => $total,
            'ultimo_sync'        => $ultimoSync,
            'defasado'           => $ultimoSync ? now()->diffInHours($ultimoSync) > 26 : true,
        ];
    }

    /**
     * Busca em Atas de Registro de Preço.
     * Implementação inicial — endpoint preparado para a próxima iteração.
     */
    public function buscarAtasRegistroPreco(string $termo, array $filtros = [], int $pagina = 1, int $tamanho = 10): array
    {
        // TODO: Implementar quando os endpoints de ARP forem mapeados.
        // A API esperada é: GET /api/pncp/v1/atas (a confirmar com documentação PNCP).
        Log::info('Busca de Atas de Registro de Preço ainda não implementada', ['termo' => $termo]);

        return [
            'data'           => [],
            'totalRegistros' => 0,
            'totalPaginas'   => 0,
            'paginaAtual'    => $pagina,
            'aviso'          => 'Busca por Atas de Registro de Preço será implementada em breve.',
        ];
    }

    // =========================================================================
    // UTILITÁRIOS PRIVADOS
    // =========================================================================

    private function normalizarContratacoesDaCache($contratacoes): array
    {
        return $contratacoes->map(function ($c) {
            return [
                'orgaoEntidade' => [
                    'cnpj'        => $c->cnpj,
                    'razaoSocial' => $c->orgao_nome ?? '',
                ],
                'anoCompra'            => $c->ano_compra,
                'sequencialCompra'     => $c->sequencial_compra,
                'modalidadeNome'       => $c->modalidade_nome ?? '',
                'objeto'               => $c->objeto ?? '',
                'dataPublicacaoPncp'   => $c->data_publicacao_pncp?->toDateString(),
                'uf'                   => $c->uf ?? '',
                'municipio'            => $c->municipio ?? '',
                'valorTotalHomologado' => $c->valor_total_homologado,
                'temResultado'         => $c->valor_total_homologado > 0,
            ];
        })->values()->toArray();
    }

    private static function extrairMaterialOuServico(array $item): ?string
    {
        $raw = $item['materialOuServico'] ?? null;
        if (is_array($raw))   return $raw['nome'] ?? null;
        if (is_string($raw))  return match(strtoupper(trim($raw))) { 'M' => 'Material', 'S' => 'Serviço', default => $raw };
        return $item['tipoBeneficio']['nome'] ?? null;
    }

    private function httpClient(int $timeout = 15): PendingRequest
    {
        return Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept'     => 'application/json',
            'Origin'     => 'https://pncp.gov.br',
            'Referer'    => 'https://pncp.gov.br/app/editais',
        ])
        ->timeout($timeout)
        ->retry(2, 200);
    }

    private function normalizarContratacoes(array $items): array
    {
        return array_map(function ($item) {
            return [
                'orgaoEntidade' => [
                    'cnpj'        => $item['orgao_cnpj'] ?? '',
                    'razaoSocial' => $item['orgao_nome'] ?? '',
                ],
                'anoCompra'          => $item['ano'] ?? '',
                'sequencialCompra'   => $item['numero_sequencial'] ?? '',
                'modalidadeNome'     => $item['modalidade_licitacao_nome'] ?? '',
                'objeto'             => $item['description'] ?? '',
                'dataPublicacaoPncp' => $item['data_publicacao_pncp'] ?? '',
                'uf'                 => $item['uf'] ?? '',
                'municipio'          => $item['municipio_nome'] ?? '',
                'temResultado'       => $item['tem_resultado'] ?? false,
            ];
        }, $items);
    }

    private function normalizarContratacoesFiltradas(array $items): array
    {
        return array_map(function ($item) {
            return [
                'orgaoEntidade' => [
                    'cnpj'        => $item['orgaoEntidade']['cnpj'] ?? '',
                    'razaoSocial' => $item['orgaoEntidade']['razaoSocial'] ?? '',
                ],
                'anoCompra'             => $item['anoCompra'] ?? '',
                'sequencialCompra'      => $item['sequencialCompra'] ?? '',
                'modalidadeNome'        => $item['modalidadeNome'] ?? '',
                'objeto'                => $item['objetoCompra'] ?? '',
                'dataPublicacaoPncp'    => $item['dataPublicacaoPncp'] ?? '',
                'uf'                    => $item['unidadeOrgao']['ufSigla'] ?? '',
                'municipio'             => $item['unidadeOrgao']['municipioNome'] ?? '',
                'valorTotalHomologado'  => $item['valorTotalHomologado'] ?? null,
            ];
        }, $items);
    }
}
