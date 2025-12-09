<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\UnidadeRequest;

class UnidadeController extends Controller
{
        /**
     * Store a new unidade.
     */
    public function storeUnidade(Request $request, $prefeituraId)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'servidor_responsavel' => 'required|string|max:255',
            'numero_portaria' => 'nullable|string|max:20',
            'data_portaria' => 'nullable|date',
        ]);

        try {
            Unidade::create([
                'prefeitura_id' => $prefeituraId,
                'nome' => $request->nome,
                'servidor_responsavel' => $request->servidor_responsavel,
                'numero_portaria' => $request->numero_portaria,
                'data_portaria' => $request->data_portaria,
            ]);

            return response()->json(['success' => 'Unidade cadastrada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao cadastrar unidade: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update a unidade.
     */
    public function updateUnidade(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'servidor_responsavel' => 'required|string|max:255',
            'numero_portaria' => 'nullable|string|max:20',
            'data_portaria' => 'nullable|date',
        ]);

        try {
            $unidade = Unidade::findOrFail($id);
            $unidade->update([
                'nome' => $request->nome,
                'servidor_responsavel' => $request->servidor_responsavel,
                'numero_portaria' => $request->numero_portaria,
                'data_portaria' => $request->data_portaria,
            ]);

            return response()->json(['success' => 'Unidade atualizada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao atualizar unidade: ' . $e->getMessage()], 500);
        }
    }

    public function destroyUnidade($id)
    {
        try {
            $unidade = Unidade::findOrFail($id);

            // Verificar se a unidade está sendo usada em algum processo antes de excluir
            // Você pode adicionar uma verificação aqui se necessário

            $unidade->delete();

            return response()->json([
                'success' => 'Unidade removida com sucesso!',
                'message' => 'Unidade excluída com sucesso.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Unidade não encontrada.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir unidade: ' . $e->getMessage());

            return response()->json([
                'error' => 'Erro ao remover unidade. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Get unidade data for editing.
     */
    public function getUnidade($id)
    {
        try {
            $unidade = Unidade::findOrFail($id);
            return response()->json($unidade);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unidade não encontrada'], 404);
        }
    }
}
