<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PesquisaPrecoItem;
use App\Models\Processo;
use App\Services\PncpService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PesquisaPrecoController extends Controller
{
    protected PncpService $pncpService;

    public function __construct(PncpService $pncpService)
    {
        $this->pncpService = $pncpService;
    }

    public function index(Request $request): View
    {
        $processoId = $request->integer('processo_id') ?: null;
        $processo   = $processoId ? Processo::find($processoId) : null;
        $termo      = $request->input('termo', '');

        return view('Admin.PesquisaPreco.index', compact('processo', 'termo'));
    }

    /**
     * Salva um item do PNCP no relatório de um processo.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'processo_id'       => 'required|integer|exists:processos,id',
            'etp_item_id'       => 'nullable|integer|exists:etp_itens,id',
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

        // Tenta buscar o numero_processo real no detalhe da contratação (API PNCP)
        try {
            $detalhe = $this->pncpService->buscarDetalheContratacao(
                $data['orgao_cnpj'],
                $data['ano_compra'],
                $data['sequencial_compra']
            );

            // Log para debug (pode remover depois)
            \Log::info('PNCP Detalhe recebido:', [
                'cnpj' => $data['orgao_cnpj'],
                'ano'  => $data['ano_compra'],
                'seq'  => $data['sequencial_compra'],
                'resp' => $detalhe
            ]);

            if (!isset($detalhe['error']) && !empty($detalhe['numeroProcesso'])) {
                $data['numero_processo'] = $detalhe['numeroProcesso'];
            }
        } catch (\Exception $e) {
            \Log::warning('Falha ao obter número do processo do PNCP ao salvar item.', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
        }

        // Tenta buscar o fornecedor vencedor e o valor homologado (real pago)
        try {
            if (!empty($data['numero_item'])) {
                $resultados = $this->pncpService->buscarResultadosItem(
                    $data['orgao_cnpj'],
                    $data['ano_compra'],
                    $data['sequencial_compra'],
                    (int) $data['numero_item']
                );

                if (!empty($resultados) && is_array($resultados)) {
                    // Pega o primeiro resultado (vencedor)
                    $vencedor = $resultados[0];
                    
                    if (!empty($vencedor['nomeRazaoSocialFornecedor'])) {
                        $data['fornecedor_nome'] = $vencedor['nomeRazaoSocialFornecedor'];
                    }
                    
                    if (!empty($vencedor['niFornecedor'])) {
                        $data['fornecedor_cnpj'] = $vencedor['niFornecedor'];
                    }

                    // Prioriza o valor homologado (final) se disponível
                    if (isset($vencedor['valorUnitarioHomologado']) && $vencedor['valorUnitarioHomologado'] > 0) {
                        $data['valor_unitario'] = $vencedor['valorUnitarioHomologado'];
                        $data['tipo_valor']     = 'homologado';
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Falha ao obter resultados (vencedor) do PNCP ao salvar item.', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
        }

        $data['valor_total'] = ($data['quantidade'] ?? 0) > 0
            ? round($data['quantidade'] * $data['valor_unitario'], 4)
            : null;

        $data['tipo_valor'] = $data['tipo_valor'] ?? 'estimado';

        $item = PesquisaPrecoItem::create($data);

        return response()->json(['success' => true, 'id' => $item->id], 201);
    }

    /**
     * Salva uma pesquisa de preço de fornecedor local manualmente.
     */
    public function storeLocal(Request $request): JsonResponse
    {
        $data = $request->validate([
            'processo_id'     => 'required|integer|exists:processos,id',
            'etp_item_id'     => 'nullable|integer|exists:etp_itens,id',
            'descricao'       => 'required|string',
            'valor_unitario'  => 'required|numeric|min:0',
            'fornecedor_nome' => 'required|string',
            'fornecedor_cnpj' => 'nullable|string',
            'data_publicacao' => 'required|date',
            'quantidade'      => 'nullable|numeric|min:0',
            'unidade_medida'  => 'nullable|string',
        ]);

        // Preenche campos obrigatórios do PNCP com valores de identificação local
        $data['orgao_nome']        = 'PREÇOS DO FORNECEDOR LOCAL';
        $data['ano_compra']        = date('Y');
        $data['sequencial_compra'] = 'LOCAL';
        $data['orgao_cnpj']        = '00.000.000/0000-00';
        $data['tipo_valor']        = 'estimado';
        $data['valor_total']       = ($data['quantidade'] ?? 0) > 0
            ? round($data['quantidade'] * $data['valor_unitario'], 4)
            : null;

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
