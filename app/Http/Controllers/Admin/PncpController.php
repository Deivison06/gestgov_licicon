<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PncpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PncpController extends Controller
{
    protected PncpService $pncpService;

    public function __construct(PncpService $pncpService)
    {
        $this->pncpService = $pncpService;
    }

    /**
     * Busca contratações com status "recebendo_proposta" — usada pelo modal do ETP.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'termo'     => 'required|string|min:3',
            'pagina'    => 'nullable|integer|min:1',
            'uf'        => 'nullable|string|size:2',
            'municipio' => 'nullable|string',
        ]);

        $termo   = $request->input('termo');
        $filtros = $request->only(['uf', 'municipio']);
        $pagina  = $request->integer('pagina', 1);

        Log::info('Busca PNCP (ETP modal) iniciada', ['termo' => $termo, 'user_id' => auth()->id()]);

        $results = $this->pncpService->buscarContratacoes($termo, $filtros, $pagina);

        if (isset($results['error'])) {
            return response()->json(['success' => false, 'message' => $results['error']], 502);
        }

        return response()->json(['success' => true, 'data' => $results]);
    }

    /**
     * Busca os itens de uma contratação com campos completos para a Pesquisa de Preços.
     */
    public function getItems(Request $request, string $cnpj, string $ano, string $sequencial): JsonResponse
    {
        $results = $this->pncpService->buscarItensContratacaoMercado($cnpj, $ano, $sequencial);

        if (isset($results['error'])) {
            return response()->json(['success' => false, 'message' => $results['error']], 502);
        }

        return response()->json(['success' => true, 'data' => $results]);
    }

    /**
     * Busca contratações para Pesquisa de Preço de Mercado.
     * Prioriza cache local quando disponível (permite combinar termo + filtros sem limitações de endpoint).
     * Fallback: modo filtrado (/consulta/v1) ou textual (/api/search/).
     */
    public function buscarMercado(Request $request): JsonResponse
    {
        $request->validate([
            'termo'        => 'required|string|min:3',
            'pagina'       => 'nullable|integer|min:1',
            'data_inicial' => 'nullable|date_format:Y-m-d',
            'data_final'   => 'nullable|date_format:Y-m-d|after_or_equal:data_inicial',
            'uf'           => 'nullable|string|size:2',
            'modalidade'   => 'nullable|integer|min:1|max:12',
            'situacao'     => 'nullable|integer|min:1|max:12',
        ]);

        $termo   = $request->input('termo');
        $filtros = $request->only(['data_inicial', 'data_final', 'uf', 'modalidade', 'situacao']);
        $pagina  = $request->integer('pagina', 1);

        $modoFiltrado = !empty($filtros['modalidade'])
            && !empty($filtros['data_inicial'])
            && !empty($filtros['data_final']);

        $temFiltroAtivo = $modoFiltrado || !empty($filtros['situacao']) || !empty($filtros['uf']);
        $usarCache      = $this->pncpService->cacheEstaDisponivel() && ($modoFiltrado || $temFiltroAtivo);

        Log::info('Busca PNCP Mercado', [
            'termo'   => $termo,
            'modo'    => $usarCache ? 'cache_local' : ($modoFiltrado ? 'filtrado' : 'textual'),
            'user_id' => auth()->id(),
        ]);

        if ($usarCache) {
            $results = $this->pncpService->buscarNoCache($termo, $filtros, $pagina);
        } elseif ($modoFiltrado) {
            $results = $this->pncpService->buscarContratacoesFiltradas($termo, $filtros, $pagina);
        } else {
            $results = $this->pncpService->buscarContratacoesMercado($termo, $filtros, $pagina);
        }

        if (isset($results['error'])) {
            return response()->json(['success' => false, 'message' => $results['error']], 502);
        }

        return response()->json(['success' => true, 'data' => $results]);
    }

    /**
     * Retorna o status atual do cache local de contratações PNCP.
     */
    public function cacheStatus(): JsonResponse
    {
        return response()->json($this->pncpService->statusCache());
    }

    /**
     * Retorna o detalhe completo de uma contratação — usado pelo painel "Ver objeto".
     */
    public function getContratacao(string $cnpj, string $ano, string $sequencial): JsonResponse
    {
        $result = $this->pncpService->buscarDetalheContratacao($cnpj, $ano, $sequencial);

        if (isset($result['error'])) {
            return response()->json(['success' => false, 'message' => $result['error']], 502);
        }

        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * Retorna os vencedores (resultados) de um item específico de uma contratação.
     */
    public function getResultadosItem(string $cnpj, string $ano, string $sequencial, int $item): JsonResponse
    {
        $results = $this->pncpService->buscarResultadosItem($cnpj, $ano, $sequencial, $item);

        return response()->json(['success' => true, 'data' => $results]);
    }

    /**
     * Busca em Atas de Registro de Preço — endpoint preparado para implementação futura.
     */
    public function buscarAtas(Request $request): JsonResponse
    {
        $request->validate([
            'termo'        => 'required|string|min:3',
            'pagina'       => 'nullable|integer|min:1',
            'data_inicial' => 'nullable|date_format:Y-m-d',
            'data_final'   => 'nullable|date_format:Y-m-d|after_or_equal:data_inicial',
            'uf'           => 'nullable|string|size:2',
            'cnpj_orgao'   => 'nullable|string',
        ]);

        $termo   = $request->input('termo');
        $filtros = $request->only(['data_inicial', 'data_final', 'uf', 'cnpj_orgao']);
        $pagina  = $request->integer('pagina', 1);

        Log::info('Busca PNCP Atas iniciada', ['termo' => $termo, 'user_id' => auth()->id()]);

        $results = $this->pncpService->buscarAtasRegistroPreco($termo, $filtros, $pagina);

        if (isset($results['error'])) {
            return response()->json(['success' => false, 'message' => $results['error']], 502);
        }

        return response()->json(['success' => true, 'data' => $results]);
    }
}
