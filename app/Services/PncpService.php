<?php

namespace App\Services;

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
     * Busca contratações para pesquisa de preço de mercado — sem filtro de status.
     * Suporta filtros por período, UF, código IBGE e CNPJ do órgão.
     */
    public function buscarContratacoesMercado(string $termo, array $filtros = [], int $pagina = 1, int $tamanho = 10): array
    {
        $cacheKey = 'pncp_mercado_' . md5($termo . serialize($filtros) . $pagina . $tamanho);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($termo, $filtros, $pagina, $tamanho) {
            try {
                $params = array_filter([
                    'q'               => $termo,
                    'tipos_documento' => 'edital',
                    'pagina'          => $pagina,
                    'tam_pagina'      => $tamanho,
                    'data_inicio'     => $filtros['data_inicial'] ?? null,
                    'data_fim'        => $filtros['data_final'] ?? null,
                    'uf'              => $filtros['uf'] ?? null,
                    'municipio_ibge'  => $filtros['codigo_ibge'] ?? null,
                    'orgao_cnpj'      => $filtros['cnpj_orgao'] ?? null,
                ]);

                $response = $this->httpClient()->get($this->searchUrl, $params);

                if ($response->status() === 502) {
                    return ['error' => 'O serviço PNCP está temporariamente instável (502). Tente novamente em instantes.'];
                }

                if ($response->failed()) {
                    Log::error('Erro ao consultar PNCP Mercado', ['status' => $response->status(), 'termo' => $termo]);
                    return ['error' => 'Falha na comunicação com o PNCP'];
                }

                $json = $response->json();

                return [
                    'data'           => $this->normalizarContratacoes($json['items'] ?? []),
                    'totalRegistros' => $json['total'] ?? 0,
                    'totalPaginas'   => ceil(($json['total'] ?? 0) / $tamanho),
                    'paginaAtual'    => $pagina,
                ];
            } catch (Exception $e) {
                Log::error('Exceção ao consultar PNCP Mercado', ['message' => $e->getMessage(), 'termo' => $termo]);
                return ['error' => 'Erro interno ao processar busca de mercado no PNCP'];
            }
        });
    }

    /**
     * Busca itens de uma contratação para pesquisa de mercado.
     * Prioriza valorUnitarioHomologado (valor real pago); estimado como fallback.
     * Inclui dados do vencedor quando disponíveis.
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
                    return array_merge($item, [
                        // Para pesquisa de mercado: homologado tem prioridade (é o preço efetivamente pago)
                        'valorUnitario'       => $item['valorUnitarioHomologado'] ?? $item['valorUnitarioEstimado'] ?? 0,
                        'tipoValor'           => isset($item['valorUnitarioHomologado']) ? 'homologado' : 'estimado',
                        'nomeVencedor'        => $item['nomeRazaoSocialFornecedor'] ?? null,
                        'cnpjVencedor'        => $item['cnpjFornecedor'] ?? null,
                        'situacaoItem'        => $item['situacaoCompraItem']['nome'] ?? null,
                    ]);
                }, $response->json() ?? []);
            } catch (Exception $e) {
                return ['error' => 'Erro ao processar itens de mercado: ' . $e->getMessage()];
            }
        });
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
                    'cnpj'       => $item['orgao_cnpj'] ?? '',
                    'razaoSocial' => $item['orgao_nome'] ?? '',
                ],
                'anoCompra'          => $item['ano'] ?? '',
                'sequencialCompra'   => $item['numero_sequencial'] ?? '',
                'modalidadeNome'     => $item['modalidade_licitacao_nome'] ?? '',
                'objeto'             => $item['description'] ?? '',
                'dataPublicacaoPncp' => $item['data_publicacao_pncp'] ?? '',
                'uf'                 => $item['uf_nome'] ?? '',
                'municipio'          => $item['municipio_nome'] ?? '',
            ];
        }, $items);
    }
}
