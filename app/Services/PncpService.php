<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class PncpService
{
    protected string $baseUrl;
    protected string $searchUrl;
    protected int $cacheTtl;

    public function __construct()
    {
        $this->baseUrl = config('services.pncp.base_url');
        $this->searchUrl = config('services.pncp.search_url');
        $this->cacheTtl = config('services.pncp.cache_ttl');
    }

    /**
     * Busca contratações no PNCP com base em um termo e filtros.
     */
    public function buscarContratacoes(string $termo, array $filtros = [], int $pagina = 1, int $tamanho = 10): array
    {
        $cacheKey = 'pncp_search_' . md5($termo . serialize($filtros) . $pagina . $tamanho);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($termo, $filtros, $pagina, $tamanho) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'application/json',
                    'Origin' => 'https://pncp.gov.br',
                    'Referer' => 'https://pncp.gov.br/app/editais',
                ])
                ->timeout(15)
                ->retry(2, 200)
                ->get($this->searchUrl, [
                    'q' => $termo,
                    'tipos_documento' => 'edital',
                    'pagina' => $pagina,
                    'tam_pagina' => $tamanho,
                    'status' => 'recebendo_proposta', // Opcional, mas comum em buscas de editais
                ]);

                if ($response->status() === 502) {
                    return ['error' => 'O serviço de busca do PNCP está temporariamente instável (502). Tente novamente em instantes.', 'data' => []];
                }

                if ($response->failed()) {
                    Log::error('Erro ao consultar API PNCP Search', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'termo' => $termo
                    ]);
                    return ['error' => 'Falha na comunicação com o serviço de busca do PNCP', 'data' => []];
                }

                $json = $response->json();
                
                // Normalização para manter compatibilidade com o frontend existente
                $normalizedItems = array_map(function($item) {
                    return [
                        'orgaoEntidade' => [
                            'cnpj' => $item['orgao_cnpj'] ?? '',
                            'razaoSocial' => $item['orgao_nome'] ?? ''
                        ],
                        'anoCompra' => $item['ano'] ?? '',
                        'sequencialCompra' => $item['numero_sequencial'] ?? '',
                        'modalidadeNome' => $item['modalidade_licitacao_nome'] ?? '',
                        'objeto' => $item['description'] ?? '',
                        'dataPublicacaoPncp' => $item['data_publicacao_pncp'] ?? ''
                    ];
                }, $json['items'] ?? []);

                return [
                    'data' => $normalizedItems,
                    'totalRegistros' => $json['total'] ?? 0,
                    'totalPaginas' => ceil(($json['total'] ?? 0) / $tamanho)
                ];
            } catch (Exception $e) {
                Log::error('Exceção ao consultar API PNCP Search', [
                    'message' => $e->getMessage(),
                    'termo' => $termo
                ]);
                return ['error' => 'Erro interno ao processar busca no PNCP', 'data' => []];
            }
        });
    }

    /**
     * Busca os itens de uma contratação específica.
     */
    public function buscarItensContratacao(string $cnpj, string $ano, string $sequencial): array
    {
        $cacheKey = "pncp_itens_{$cnpj}_{$ano}_{$sequencial}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($cnpj, $ano, $sequencial) {
            try {
                // A URL de itens exige o prefixo /orgaos/... na API de manutenção/consulta
                // O PNCP usa: https://pncp.gov.br/api/pncp/v1/orgaos/{cnpj}/compras/{ano}/{sequencial}/itens
                // Mas o baseUrl já aponta para /api/consulta/v1
                // Precisamos garantir que a URL final esteja correta.
                
                $url = "https://pncp.gov.br/api/pncp/v1/orgaos/{$cnpj}/compras/{$ano}/{$sequencial}/itens";

                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'application/json',
                ])
                ->timeout(10)
                ->retry(2, 200)
                ->get($url, [
                    'pagina' => 1,
                    'tamanhoPagina' => 100
                ]);

                if ($response->failed()) {
                    return ['error' => 'Falha ao buscar itens do PNCP (Status: ' . $response->status() . ')', 'data' => []];
                }

                $json = $response->json();

                // Normalização dos itens para garantir que o campo valorUnitario exista
                return array_map(function($item) {
                    return array_merge($item, [
                        'valorUnitario' => $item['valorUnitarioEstimado'] ?? $item['valorUnitarioHomologado'] ?? 0
                    ]);
                }, $json);
            } catch (Exception $e) {
                return ['error' => 'Erro ao processar itens: ' . $e->getMessage(), 'data' => []];
            }
        });
    }
}
