<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contrato;
use App\Services\IncidenteContratualService;

class IncidenteContratualController extends Controller
{
    protected $incidenteService;

    public function __construct(IncidenteContratualService $incidenteService)
    {
        $this->incidenteService = $incidenteService;
    }

    public function create($contrato_id)
    {
        $contrato = Contrato::with('processo')->findOrFail($contrato_id);
        
        return view('Admin.IncidentesContratuais.create', compact('contrato'));
    }

    public function store(Request $request, $contrato_id)
    {
        $contrato = Contrato::findOrFail($contrato_id);

        $validated = $request->validate([
            'tipo' => 'required|in:prazo,valor,prazo_valor',
            'categoria' => 'required|in:compras_servicos,obras',
        ]);

        $incidente = \App\Models\IncidenteContratual::create([
            'contrato_id' => $contrato->id,
            'tipo' => $validated['tipo'],
            'categoria' => $validated['categoria'],
        ]);

        return redirect()->route('admin.incidentes.documentos', [
            'contrato_id' => $contrato->id,
            'incidente_id' => $incidente->id
        ])->with('success', 'Rascunho de aditivo criado. Preencha os campos para finalizar.');
    }

    public function atualizarCampos(Request $request, $contrato_id, $incidente_id)
    {
        $contrato = Contrato::findOrFail($contrato_id);
        $incidente = \App\Models\IncidenteContratual::where('contrato_id', $contrato_id)->findOrFail($incidente_id);

        $dadosInput = $request->all();
        if (isset($dadosInput['percentual_valor']) && str_contains($dadosInput['percentual_valor'], ',')) {
            $dadosInput['percentual_valor'] = str_replace(',', '.', $dadosInput['percentual_valor']);
            $request->merge(['percentual_valor' => $dadosInput['percentual_valor']]);
        }

        $rules = [
            'tipo' => 'required|string',
            'categoria' => 'required|string',
            'meses_prorrogacao' => 'required_if:tipo,prazo,prazo_valor|nullable|integer|min:1',
            'percentual_valor' => 'required_if:tipo,valor,prazo_valor|nullable|numeric',
            'justificativa' => 'nullable|string',
            'arquivo_solicitacao' => 'nullable|file|mimes:pdf|max:10240',
        ];

        if ($incidente->categoria === 'obras' && in_array($incidente->tipo, ['valor', 'prazo_valor'])) {
            $rules['arquivo_orcamento_obra'] = 'nullable|file|mimes:pdf|max:10240';
        }

        $validated = $request->validate($rules);
        $dados = $validated;

        if ($request->hasFile('arquivo_solicitacao')) {
            $path = $request->file('arquivo_solicitacao')->store('incidentes/solicitacoes', 'public');
            $dados['arquivo_solicitacao_path'] = $path;
        } else {
            $dados['arquivo_solicitacao_path'] = $incidente->arquivo_solicitacao_path;
        }

        if ($request->hasFile('arquivo_orcamento_obra')) {
            $path = $request->file('arquivo_orcamento_obra')->store('incidentes/orcamentos', 'public');
            $dados['arquivo_orcamento_obra_path'] = $path;
        } else {
            $dados['arquivo_orcamento_obra_path'] = $incidente->arquivo_orcamento_obra_path;
        }

        $dados['tipo'] = $incidente->tipo;
        $dados['categoria'] = $incidente->categoria;

        $this->incidenteService->atualizarAditivo($incidente, $contrato, $dados);

        return redirect()->back()->with('success', 'Configurações do aditivo salvas com sucesso.');
    }

    public function documentos($contrato_id, $incidente_id)
    {
        $contrato = Contrato::with(['processo.prefeitura', 'processo.detalhe'])->findOrFail($contrato_id);
        $incidente = \App\Models\IncidenteContratual::where('contrato_id', $contrato_id)->findOrFail($incidente_id);
        $processo = $contrato->processo;

        $documentos = [
            'capa_aditivo' => [
                'titulo' => 'Capa',
                'cor' => '#9333ea', // roxo
                'campos' => []
            ],
            'solicitacao_aditivo' => [
                'titulo' => 'Solicitação do Aditivo',
                'cor' => '#009496',
                'campos' => [
                    [
                        'name' => 'justificativa',
                        'label' => 'Justificativa para a solicitação de Aditivo',
                        'tipo' => 'textarea',
                        'ia' => true,
                        'value' => $incidente->justificativa,
                    ],
                    [
                        'name' => 'arquivo_solicitacao',
                        'label' => 'Anexar PDF da solicitação de aditivo',
                        'tipo' => 'file',
                        'value' => $incidente->arquivo_solicitacao_path,
                    ]
                ]
            ],
            'parecer_juridico_aditivo' => [
                'titulo' => 'Parecer Jurídico',
                'cor' => '#dc2626', // vermelho
                'campos' => []
            ],
            'autorizacao_prefeito_aditivo' => [
                'titulo' => 'Autorização do Prefeito',
                'cor' => '#ea580c', // laranja
                'campos' => []
            ],
            'termo_aditivo' => [
                'titulo' => 'Termo Aditivo ao Contrato',
                'cor' => '#0284c7', // azul
                'campos' => []
            ]
        ];

        // Conditional fields for Termo Aditivo based on type and category
        if (in_array($incidente->tipo, ['prazo', 'prazo_valor'])) {
            $documentos['termo_aditivo']['campos'][] = [
                'name' => 'meses_prorrogacao',
                'label' => 'Meses Prorrogados',
                'tipo' => 'number',
                'value' => $incidente->meses_prorrogacao,
            ];
        }

        if (in_array($incidente->tipo, ['valor', 'prazo_valor'])) {
            $documentos['termo_aditivo']['campos'][] = [
                'name' => 'percentual_valor',
                'label' => 'Percentual de Acréscimo (%)',
                'tipo' => 'number',
                'step' => '0.01',
                'value' => $incidente->percentual_valor,
            ];

            if ($incidente->categoria === 'obras') {
                $documentos['termo_aditivo']['campos'][] = [
                    'name' => 'arquivo_orcamento_obra',
                    'label' => 'Planilha Orçamentária Atualizada (PDF)',
                    'tipo' => 'file',
                    'value' => $incidente->arquivo_orcamento_obra_path,
                ];
            }
        }

        return view('Admin.IncidentesContratuais.documentos', compact('contrato', 'incidente', 'processo', 'documentos'));
    }

    public function salvarCampoDocumento(Request $request, $contrato_id, $incidente_id)
    {
        $contrato = Contrato::findOrFail($contrato_id);
        $processo = $contrato->processo;

        $dados = $request->except(['_token', '_method']);

        foreach ($dados as $campo => $valor) {
            if (strpos($campo, 'data_doc_') === 0) {
                $tipoDocumento = substr($campo, 9);
                \App\Models\Documento::updateOrCreate(
                    [
                        'processo_id' => $processo->id,
                        'incidente_id' => $incidente_id,
                        'tipo_documento' => $tipoDocumento,
                    ],
                    [
                        'data_selecionada' => $valor,
                        'caminho' => 'gerado_dinamicamente'
                    ]
                );
            }
        }

        return response()->json(['success' => true, 'message' => 'Campos salvos com sucesso.']);
    }

    public function gerarDocumentoPdf($contrato_id, $incidente_id, $tipo)
    {
        $contrato = Contrato::with(['processo.prefeitura', 'processo.detalhe', 'processo.documentos'])->findOrFail($contrato_id);
        $incidente = \App\Models\IncidenteContratual::with(['itens.loteContratado'])->where('contrato_id', $contrato_id)->findOrFail($incidente_id);

        $processo = $contrato->processo;
        $prefeitura = $processo->prefeitura;
        $detalhe = $processo->detalhe;

        $viewName = '';
        $tituloArquivo = '';

        $numContratoSafe = str_replace(['/', '\\'], '-', $contrato->numero_contrato ?? 'Sem_Numero');

        switch ($tipo) {
            case 'capa_aditivo':
                $viewName = 'Admin.Processos.pdf.aditivos.capa';
                $tituloArquivo = 'Capa_Aditivo_' . $numContratoSafe;
                break;
            case 'solicitacao_aditivo':
                $viewName = 'Admin.Processos.pdf.aditivos.solicitacao';
                $tituloArquivo = 'Solicitacao_Aditivo_' . $numContratoSafe;
                break;
            case 'parecer_juridico_aditivo':
                $viewName = 'Admin.Processos.pdf.aditivos.parecer';
                $tituloArquivo = 'Parecer_Juridico_Aditivo_' . $numContratoSafe;
                break;
            case 'autorizacao_prefeito_aditivo':
                $viewName = 'Admin.Processos.pdf.aditivos.autorizacao';
                $tituloArquivo = 'Autorizacao_Aditivo_' . $numContratoSafe;
                break;
            case 'termo_aditivo':
                if ($incidente->categoria === 'obras') {
                    $viewName = 'Admin.Processos.pdf.aditivos.termo_obras';
                } else {
                    $viewName = 'Admin.Processos.pdf.aditivos.termo_compras';
                }
                $tituloArquivo = 'Termo_Aditivo_' . $numContratoSafe;
                break;
            default:
                abort(404, 'Documento não encontrado.');
        }

        // Recupera o documento para pegar a data, se houver
        $documento = \App\Models\Documento::where('processo_id', $processo->id)
                                          ->where('incidente_id', $incidente->id)
                                          ->where('tipo_documento', $tipo)
                                          ->first();
        
        $data_selecionada = $documento ? $documento->data_selecionada : null;

        // Marca como gerado
        if ($documento) {
            $documento->update(['gerado_em' => now()]);
        } else {
            $data_selecionada = now()->toDateString();
            \App\Models\Documento::create([
                'processo_id' => $processo->id,
                'incidente_id' => $incidente->id,
                'tipo_documento' => $tipo,
                'data_selecionada' => $data_selecionada,
                'caminho' => 'gerado_dinamicamente',
                'gerado_em' => now(),
            ]);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, compact(
            'contrato',
            'incidente',
            'processo',
            'prefeitura',
            'detalhe',
            'data_selecionada'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream($tituloArquivo . '.pdf');
    }

    public function destroy($contrato_id, $incidente_id)
    {
        $incidente = \App\Models\IncidenteContratual::where('contrato_id', $contrato_id)->findOrFail($incidente_id);
        
        $processo_id = $incidente->contrato->processo_id;

        // Excluir os itens vinculados e documentos associados a esse incidente
        $incidente->itens()->delete();
        \App\Models\Documento::where('incidente_id', $incidente->id)->delete();
        
        // Excluir o próprio incidente
        $incidente->delete();

        return redirect()->route('admin.processos.show', $processo_id)
            ->with('success', 'Aditivo revertido (excluído) com sucesso.');
    }
}
