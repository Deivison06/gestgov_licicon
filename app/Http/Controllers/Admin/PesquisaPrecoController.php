<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PesquisaPrecoItem;
use App\Models\Processo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PesquisaPrecoController extends Controller
{
    public function index(Request $request): View
    {
        $processoId = $request->integer('processo_id') ?: null;
        $processo   = $processoId ? Processo::find($processoId) : null;

        return view('Admin.PesquisaPreco.index', compact('processo'));
    }

    /**
     * Salva um item do PNCP no relatório de um processo.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'processo_id'       => 'required|integer|exists:processos,id',
            'numero_item'       => 'nullable|max:20',
            'ano_compra'        => 'required|max:4',
            'sequencial_compra' => 'required',
            'orgao_cnpj'        => 'required|max:18',
            'orgao_nome'        => 'required|string|max:500',
            'uf'                => 'nullable|max:2',
            'municipio'         => 'nullable|string',
            'data_publicacao'   => 'nullable|date',
            'modalidade'        => 'nullable|string',
            'descricao'         => 'required|string',
            'quantidade'        => 'nullable|numeric|min:0',
            'unidade_medida'    => 'nullable|max:30',
            'valor_unitario'    => 'required|numeric|min:0',
            'tipo_valor'        => 'nullable|string|in:homologado,estimado',
            'fornecedor_nome'   => 'nullable|string',
            'fornecedor_cnpj'   => 'nullable|max:18',
            'link_pncp'         => 'nullable|string|url',
        ]);

        // Garante tipos string para campos que a API pode retornar como inteiro
        foreach (['numero_item', 'ano_compra', 'sequencial_compra', 'orgao_cnpj', 'uf', 'unidade_medida', 'fornecedor_cnpj'] as $campo) {
            if (isset($data[$campo]) && $data[$campo] !== null) {
                $data[$campo] = (string) $data[$campo];
            }
        }

        $data['valor_total'] = ($data['quantidade'] ?? 0) > 0
            ? round($data['quantidade'] * $data['valor_unitario'], 4)
            : null;

        $data['tipo_valor'] = $data['tipo_valor'] ?? 'estimado';

        $item = PesquisaPrecoItem::create($data);

        return response()->json(['success' => true, 'id' => $item->id], 201);
    }

    /**
     * Remove um item do relatório.
     */
    public function destroy(int $id): JsonResponse
    {
        $item = PesquisaPrecoItem::findOrFail($id);
        $item->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Retorna os itens salvos de um processo (para o badge e preview).
     */
    public function listarPorProcesso(int $processoId): JsonResponse
    {
        $itens = PesquisaPrecoItem::where('processo_id', $processoId)
            ->orderBy('id')
            ->get(['id', 'descricao', 'valor_unitario', 'valor_total', 'tipo_valor', 'orgao_nome', 'uf']);

        return response()->json(['success' => true, 'data' => $itens, 'total' => $itens->count()]);
    }
}
