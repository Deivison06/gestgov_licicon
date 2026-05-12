<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PncpService;
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
     * Pesquisa contratações no PNCP.
     */
    public function search(Request $request)
    {
        $request->validate([
            'termo' => 'required|string|min:3',
            'pagina' => 'nullable|integer|min:1',
            'uf' => 'nullable|string|size:2',
            'municipio' => 'nullable|string',
        ]);

        $termo = $request->input('termo');
        $filtros = $request->only(['uf', 'municipio']);
        $pagina = $request->input('pagina', 1);

        Log::info('Busca PNCP iniciada', ['termo' => $termo, 'user_id' => auth()->id()]);

        $results = $this->pncpService->buscarContratacoes($termo, $filtros, $pagina);

        if (isset($results['error'])) {
            return response()->json([
                'success' => false,
                'message' => $results['error']
            ], 502);
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    /**
     * Busca itens de uma contratação.
     */
    public function getItems(Request $request, $cnpj, $ano, $sequencial)
    {
        $results = $this->pncpService->buscarItensContratacao($cnpj, $ano, $sequencial);

        if (isset($results['error'])) {
            return response()->json([
                'success' => false,
                'message' => $results['error']
            ], 502);
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }
}
