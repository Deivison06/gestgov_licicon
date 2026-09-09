<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contrato;
use App\Models\ContratoManual;
use App\Models\Documento;
use App\Models\DocumentoSelecaoAssinantes;
use App\Models\IncidenteContratual;
use App\Services\IncidenteContratualService;

/**
 * Gerencia o fluxo de Aditivo (IncidenteContratual), que pode pertencer tanto a um
 * Contrato do Sistema (App\Models\Contrato, vinculado a um Processo licitatório)
 * quanto a um Contrato Manual/Externo (App\Models\ContratoManual, sem Processo).
 *
 * As rotas de "sistema" (nome admin.incidentes.*) e "manual" (nome
 * admin.incidentes-manual.*) apontam para os MESMOS métodos abaixo — o tipo é
 * resolvido via resolverContrato(), a partir do nome da rota atual.
 *
 * Contratos Manuais não têm assinatura eletrônica disponível (não há Processo para
 * amarrar a rodada de assinatura) — os documentos saem para assinatura física, como
 * já ocorre no fluxo de Fiscalização.
 */
class IncidenteContratualController extends Controller
{
    protected $incidenteService;

    public function __construct(IncidenteContratualService $incidenteService)
    {
        $this->incidenteService = $incidenteService;
    }

    /**
     * Resolve o contratável (Contrato ou ContratoManual) a partir do id da rota,
     * usando o nome da rota atual para decidir o tipo.
     */
    private function resolverContrato($contrato_id): Contrato|ContratoManual
    {
        if (request()->routeIs('admin.incidentes-manual.*')) {
            return ContratoManual::findOrFail($contrato_id);
        }

        return Contrato::findOrFail($contrato_id);
    }

    /**
     * Nome de rota completo (admin.incidentes.{suffix} ou admin.incidentes-manual.{suffix}),
     * espelhando o prefixo da rota atual.
     */
    private function routeName(string $suffix): string
    {
        $base = request()->routeIs('admin.incidentes-manual.*') ? 'admin.incidentes-manual' : 'admin.incidentes';

        return "{$base}.{$suffix}";
    }

    private function buscarIncidente($contrato, $incidente_id): IncidenteContratual
    {
        return IncidenteContratual::where('contratavel_id', $contrato->id)
            ->where('contratavel_type', get_class($contrato))
            ->findOrFail($incidente_id);
    }

    public function store(Request $request, $contrato_id)
    {
        $contrato = $this->resolverContrato($contrato_id);

        $validated = $request->validate([
            'tipo' => 'required|in:prazo,valor,prazo_valor',
            'categoria' => 'required|in:compras_servicos,obras',
        ]);

        $incidente = IncidenteContratual::create([
            'contratavel_id' => $contrato->id,
            'contratavel_type' => get_class($contrato),
            'tipo' => $validated['tipo'],
            'categoria' => $validated['categoria'],
        ]);

        return redirect()->route($this->routeName('documentos'), [
            'contrato_id' => $contrato->id,
            'incidente_id' => $incidente->id
        ])->with('success', 'Rascunho de aditivo criado. Preencha os campos para finalizar.');
    }

    public function atualizarCampos(Request $request, $contrato_id, $incidente_id)
    {
        $contrato = $this->resolverContrato($contrato_id);
        $incidente = $this->buscarIncidente($contrato, $incidente_id);

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
            'nome_solicitante' => 'nullable|string',
            'cargo_solicitante' => 'nullable|string',
            'nome_parecerista' => 'nullable|string',
            'oab_parecerista' => 'nullable|string',
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
        $ehManual = request()->routeIs('admin.incidentes-manual.*');
        $contrato = $ehManual
            ? ContratoManual::with(['prefeitura', 'empresa'])->findOrFail($contrato_id)
            : Contrato::with(['processo.prefeitura', 'processo.detalhe'])->findOrFail($contrato_id);
        $incidente = $this->buscarIncidente($contrato, $incidente_id);

        // Contratos Manuais não têm Processo — logo, não há como amarrar uma rodada de
        // assinatura eletrônica (que depende de processo_id). Os documentos saem para
        // assinatura física, como já ocorre no fluxo de Fiscalização.
        $processo = $contrato instanceof Contrato ? $contrato->processo : null;
        $temAssinaturaEletronica = $processo !== null;

        $documentos = [
            'capa_aditivo' => [
                'titulo' => 'Capa',
                'cor' => '#9333ea', // roxo
                'campos' => []
            ],
            'solicitacao_aditivo' => [
                'titulo' => 'Solicitação do Aditivo',
                'cor' => '#009496',
                'requer_assinatura' => $temAssinaturaEletronica,
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
                'requer_assinatura' => $temAssinaturaEletronica,
                'campos' => [

                ]
            ],
            'autorizacao_prefeito_aditivo' => [
                'titulo' => 'Autorização do Prefeito',
                'cor' => '#ea580c', // laranja
                'requer_assinatura' => $temAssinaturaEletronica,
                'campos' => []
            ],
            'termo_aditivo' => [
                'titulo' => 'Termo Aditivo ao Contrato',
                'cor' => '#0284c7', // azul
                'requer_assinatura' => $temAssinaturaEletronica,
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

        // Datas já salvas para este incidente (Documento agora existe sem processo_id
        // para aditivos de Contrato Manual).
        $documentosGerados = Documento::where('incidente_id', $incidente->id)->get();

        return view('Admin.IncidentesContratuais.documentos', compact(
            'contrato', 'incidente', 'processo', 'documentos', 'documentosGerados', 'temAssinaturaEletronica'
        ));
    }

    public function salvarCampoDocumento(Request $request, $contrato_id, $incidente_id)
    {
        $contrato = $this->resolverContrato($contrato_id);
        $incidente = $this->buscarIncidente($contrato, $incidente_id);
        $processo = $contrato instanceof Contrato ? $contrato->processo : null;

        $dados = $request->except(['_token', '_method']);

        foreach ($dados as $campo => $valor) {
            if (strpos($campo, 'data_doc_') === 0) {
                $tipoDocumento = substr($campo, 9);
                Documento::updateOrCreate(
                    [
                        'processo_id' => $processo?->id,
                        'incidente_id' => $incidente->id,
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
        $ehManual = request()->routeIs('admin.incidentes-manual.*');
        $contrato = $ehManual
            ? ContratoManual::with(['prefeitura', 'empresa'])->findOrFail($contrato_id)
            : Contrato::with(['processo.prefeitura', 'processo.detalhe', 'processo.documentos'])->findOrFail($contrato_id);
        $incidente = IncidenteContratual::with(['itens.loteContratado'])
            ->where('contratavel_id', $contrato->id)
            ->where('contratavel_type', get_class($contrato))
            ->findOrFail($incidente_id);

        $processo = $contrato instanceof Contrato ? $contrato->processo : null;
        $prefeitura = $processo?->prefeitura ?? $contrato->prefeitura;
        $detalhe = $processo?->detalhe;

        // Objeto do contrato: Contrato (sistema) só tem via Processo; ContratoManual
        // tem o campo direto. Normalizado aqui para os templates usarem sempre
        // $objetoContrato, em vez de $processo->objeto (que não existe sem Processo).
        $objetoContrato = $processo?->objeto ?? $contrato->objeto ?? '';

        // Garantir dados da empresa
        if ($contrato instanceof ContratoManual) {
            $empresa = $contrato->empresa;
            $contrato->dados_contratante = [
                'razao_social' => $empresa?->razao_social ?? 'CONTRATADA',
                'cnpj' => $empresa?->cnpj_formatado ?? $empresa?->cnpj ?? 'CNPJ',
                'endereco' => $empresa?->endereco ?? 'Endereço da Empresa',
                'representante' => $empresa?->representante ?? 'Representante não informado',
                'cpf_representante' => 'CPF',
                'orgao_responsavel' => $contrato->secretaria?->nome ?? 'Secretaria Municipal',
            ];
        } elseif (empty($contrato->dados_contratante)) {
            $loteContratado = \App\Models\LoteContratado::where('contrato_id', $contrato->id)->first()
                ?? \App\Models\LoteContratado::where('processo_id', $processo->id)->first();

            if ($loteContratado && $loteContratado->vencedor) {
                $v = $loteContratado->vencedor;
                $contrato->dados_contratante = [
                    'razao_social' => $v->razao_social,
                    'cnpj' => $v->cnpj,
                    'endereco' => $v->endereco,
                    'representante' => $v->representante ?? 'Representante não informado',
                    'cpf_representante' => $v->cpf,
                    'orgao_responsavel' => 'Secretaria Municipal',
                ];
            } elseif ($processo->finalizacao) {
                $contrato->dados_contratante = [
                    'razao_social' => $processo->finalizacao->razao_social ?? 'CONTRATADA',
                    'cnpj' => $processo->finalizacao->cnpj_empresa_vencedora ?? 'CNPJ',
                    'endereco' => $processo->finalizacao->endereco_empresa_vencedora ?? 'Endereço',
                    'representante' => $processo->finalizacao->representante_legal_empresa ?? 'Representante não informado',
                    'cpf_representante' => $processo->finalizacao->cpf_representante ?? 'CPF',
                    'orgao_responsavel' => 'Secretaria Municipal',
                ];
            } else {
                $contrato->dados_contratante = [
                    'razao_social' => 'CONTRATADA',
                    'cnpj' => 'CNPJ',
                    'endereco' => 'Endereço da Empresa',
                    'representante' => 'Representante Legal',
                    'cpf_representante' => 'CPF',
                    'orgao_responsavel' => 'Secretaria Municipal',
                ];
            }
        }

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

        // Recupera o documento para pegar a data, se houver (identificado por
        // incidente_id + tipo_documento — processo_id pode ser nulo para manuais)
        $documento = Documento::where('incidente_id', $incidente->id)
                                          ->where('tipo_documento', $tipo)
                                          ->first();

        $data_selecionada = $documento ? $documento->data_selecionada : null;

        // Recupera a seleção de assinantes, se houver (só existe quando há Processo)
        $documentoSelecao = $processo
            ? DocumentoSelecaoAssinantes::where('processo_id', $processo->id)
                ->where('incidente_id', $incidente->id)
                ->where('tipo_documento', $tipo)
                ->first()
            : null;

        // Marca como gerado
        if ($documento) {
            $documento->update(['gerado_em' => now()]);
        } else {
            $data_selecionada = now()->toDateString();
            Documento::create([
                'processo_id' => $processo?->id,
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
            'objetoContrato',
            'data_selecionada',
            'documentoSelecao'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream($tituloArquivo . '.pdf');
    }

    public function destroy($contrato_id, $incidente_id)
    {
        $contrato = $this->resolverContrato($contrato_id);
        $incidente = $this->buscarIncidente($contrato, $incidente_id);

        // Excluir os itens vinculados e documentos associados a esse incidente
        $incidente->itens()->delete();
        Documento::where('incidente_id', $incidente->id)->delete();
        DocumentoSelecaoAssinantes::where('incidente_id', $incidente->id)->delete();

        $incidente->delete();

        if ($contrato instanceof ContratoManual) {
            return redirect()->route('admin.contratos.show.manual', $contrato->id)
                ->with('success', 'Aditivo revertido (excluído) com sucesso.');
        }

        return redirect()->route('admin.processos.show', $contrato->processo_id)
            ->with('success', 'Aditivo revertido (excluído) com sucesso.');
    }
}
