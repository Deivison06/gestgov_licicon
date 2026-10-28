<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FatorAtrasoEnum;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\ContratoManual;
use App\Models\Documento;
use App\Models\DocumentoSelecaoAssinantes;
use App\Models\IncidenteContratual;
use App\Models\LoteContratado;
use App\Models\Unidade;
use App\Services\Aditivos\AditivoCalculoFinanceiroService;
use App\Services\IncidenteContratualService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use setasign\Fpdi\Tcpdf\Fpdi;

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

    protected $calculoFinanceiro;

    public function __construct(IncidenteContratualService $incidenteService, AditivoCalculoFinanceiroService $calculoFinanceiro)
    {
        $this->incidenteService = $incidenteService;
        $this->calculoFinanceiro = $calculoFinanceiro;
    }

    /**
     * Resolve o contratável (Contrato ou ContratoManual) a partir do id da rota,
     * usando o nome da rota atual para decidir o tipo.
     */
    private function resolverContrato($contrato_id): Contrato|ContratoManual
    {
        if (request()->routeIs('admin.incidentes-manual.*')) {
            $contrato = ContratoManual::findOrFail($contrato_id);
        } else {
            $contrato = Contrato::with('processo')->findOrFail($contrato_id);
        }

        $this->autorizarAcessoPrefeitura($contrato);

        return $contrato;
    }

    /**
     * Equipe Licicon (acesso irrestrito a qualquer prefeitura). Todo o resto — role
     * prefeitura/Prefeitura - Personalizado, ou qualquer usuário liberado só pela
     * permission "contratos" — só pode mexer nos contratos da própria prefeitura.
     */
    private function ehEquipeLicicon(): bool
    {
        return auth()->user()->hasAnyRole(['diretor_licicon', 'gerente_licicon', 'colaborador_licicon']);
    }

    /**
     * Fora da equipe Licicon, o fluxo de incidentes só é acessível via o próprio
     * fluxo de aditivo externo (liberado no middleware de rota); aqui garantimos que,
     * além disso, só mexam em contratos da própria prefeitura. ContratoManual já é
     * filtrado por um global scope (PrefeituraScope), mas Contrato (sistema) não tem
     * proteção nenhuma hoje — a titularidade só existe indiretamente via Processo.
     */
    private function autorizarAcessoPrefeitura(Contrato|ContratoManual $contrato): void
    {
        if ($this->ehEquipeLicicon()) {
            return;
        }

        $prefeituraIdDoContrato = $contrato instanceof ContratoManual
            ? $contrato->prefeitura_id
            : $contrato->processo?->prefeitura_id;

        if ($prefeituraIdDoContrato != auth()->user()->prefeitura_id) {
            abort(403, 'Acesso não autorizado.');
        }
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

    /**
     * Sugestão de tipo_objeto_contrato (compra|servico) na abertura do aditivo,
     * usada como default editável quando o usuário não escolhe explicitamente.
     * Contrato (sistema) usa o cadastro padrão em Contrato::tipo_objeto;
     * ContratoManual deriva do seu próprio tipo_contrato ('Compras'/'Serviço'/
     * 'Obra' — 'Obra' não se aplica aqui, pois a categoria já seria 'obras').
     */
    private function tipoObjetoContratoPadrao(Contrato|ContratoManual $contrato): ?string
    {
        if ($contrato instanceof ContratoManual) {
            return match ($contrato->tipo_contrato) {
                'Compras' => 'compra',
                'Serviço' => 'servico',
                default => null,
            };
        }

        return $contrato->tipo_objeto;
    }

    /**
     * Fora da equipe Licicon, só se pode mexer na tela de aditivo externo de um
     * incidente que foi, de fato, criado como externo (evita acessar o fluxo de um
     * incidente interno por adivinhação de id).
     */
    private function autorizarAditivoExternoPrefeitura(IncidenteContratual $incidente): void
    {
        if (! $this->ehEquipeLicicon() && ! $incidente->ehExterno()) {
            abort(403, 'Acesso não autorizado.');
        }
    }

    public function store(Request $request, $contrato_id)
    {
        $contrato = $this->resolverContrato($contrato_id);

        $validated = $request->validate([
            'tipo' => 'required|in:prazo,valor,prazo_valor',
            'categoria' => 'required|in:compras_servicos,obras',
            'origem' => 'nullable|in:interno,externo',
            // Decisões de abertura exigidas pela atualização TCU — ver
            // IncidenteContratual::camposTceFaltantes() para a mesma regra.
            'natureza_alteracao' => 'required_if:tipo,valor,prazo_valor|nullable|in:acrescimo,supressao,acrescimo_e_supressao',
            'tipo_objeto_contrato' => 'required_if:categoria,compras_servicos|nullable|in:compra,servico',
            'contrato_continuo' => 'required_if:tipo,prazo,prazo_valor|nullable|boolean',
        ]);

        // Fora da equipe Licicon (prefeitura, ou qualquer usuário liberado só pela
        // permission "contratos"), só pode criar aditivo externo — nunca confiar no
        // campo vindo do request para esse perfil, mesmo que a UI já force isso.
        if (! $this->ehEquipeLicicon()) {
            $validated['origem'] = 'externo';
        }

        $incidente = IncidenteContratual::create([
            'contratavel_id' => $contrato->id,
            'contratavel_type' => get_class($contrato),
            'tipo' => $validated['tipo'],
            'categoria' => $validated['categoria'],
            'origem' => $validated['origem'] ?? 'interno',
            'natureza_alteracao' => $validated['natureza_alteracao'] ?? null,
            'tipo_objeto_contrato' => $validated['tipo_objeto_contrato'] ?? $this->tipoObjetoContratoPadrao($contrato),
            'contrato_continuo' => $validated['contrato_continuo'] ?? $contrato->continuo,
            // Snapshot do estado do contrato ANTES do efeito deste aditivo — usado
            // depois para aplicar/reverter prazo e valor de forma absoluta (idempotente).
            'data_finalizacao_base' => $contrato->data_finalizacao,
            'valor_total_base' => $contrato instanceof ContratoManual ? $contrato->valor_total : null,
        ]);

        if (($validated['origem'] ?? 'interno') === 'externo') {
            return redirect()->route($this->routeName('externo'), [
                'contrato_id' => $contrato->id,
                'incidente_id' => $incidente->id,
            ])->with('success', 'Rascunho de aditivo externo criado. Preencha as informações e anexe o PDF.');
        }

        return redirect()->route($this->routeName('documentos'), [
            'contrato_id' => $contrato->id,
            'incidente_id' => $incidente->id,
        ])->with('success', 'Rascunho de aditivo criado. Preencha os campos para finalizar.');
    }

    /**
     * GET: formulário do aditivo externo (feito fora do sistema).
     */
    public function externo($contrato_id, $incidente_id)
    {
        $contrato = $this->resolverContrato($contrato_id);
        $incidente = $this->buscarIncidente($contrato, $incidente_id);
        $this->autorizarAditivoExternoPrefeitura($incidente);

        $lotesContratados = $contrato instanceof Contrato
            ? LoteContratado::where('contrato_id', $contrato->id)->with('lote')->get()
            : collect();

        $itensAtuais = $incidente->itens()->get()->keyBy('lote_contratado_id');

        $urlVoltar = $contrato instanceof ContratoManual
            ? route('admin.contratos.show.manual', $contrato->id)
            : route('admin.processos.show', $contrato->processo_id);

        return view('Admin.IncidentesContratuais.externo', [
            'contrato' => $contrato,
            'incidente' => $incidente,
            'ehManual' => $contrato instanceof ContratoManual,
            'lotesContratados' => $lotesContratados,
            'itensAtuais' => $itensAtuais,
            'urlVoltar' => $urlVoltar,
            'rotaSalvar' => $this->routeName('externo.atualizar'),
        ]);
    }

    /**
     * POST: salva o aditivo externo e aplica o efeito no contrato (prazo/valor/itens).
     */
    public function atualizarExterno(Request $request, $contrato_id, $incidente_id)
    {
        $contrato = $this->resolverContrato($contrato_id);
        $incidente = $this->buscarIncidente($contrato, $incidente_id);
        $this->autorizarAditivoExternoPrefeitura($incidente);

        $rules = [
            'data_aditivo' => 'required|date',
            'justificativa' => 'nullable|string',
            'arquivo_aditivo_externo' => $incidente->arquivo_aditivo_externo_path
                ? 'nullable|file|mimes:pdf|max:20480'
                : 'required|file|mimes:pdf|max:20480',
        ];

        if (in_array($incidente->tipo, ['prazo', 'prazo_valor'])) {
            $rules['meses_prorrogacao'] = 'required|integer|min:1';
        }

        if (in_array($incidente->tipo, ['valor', 'prazo_valor'])) {
            if ($contrato instanceof ContratoManual) {
                $rules['valor_acrescido'] = 'required|numeric|min:0.01';
            } else {
                $rules['itens'] = 'required|array|min:1';
                $rules['itens.*.lote_contratado_id'] = 'required|exists:lote_contratados,id';
                $rules['itens.*.quantidade_aditivada'] = 'required|numeric|min:0.0001';
            }
        }

        $validated = $request->validate($rules);

        // Sem vencimento cadastrado, não há data-base para calcular a prorrogação —
        // melhor recusar com uma mensagem clara do que aplicar silenciosamente nada.
        if (in_array($incidente->tipo, ['prazo', 'prazo_valor']) && ! $contrato->data_finalizacao) {
            return back()->withErrors([
                'meses_prorrogacao' => 'Este contrato não tem uma data de vencimento cadastrada — cadastre-a antes de registrar um aditivo de prazo.',
            ])->withInput();
        }

        if ($request->hasFile('arquivo_aditivo_externo')) {
            $validated['arquivo_aditivo_externo_path'] = $request->file('arquivo_aditivo_externo')
                ->store('incidentes/externos', 'public');
        }

        $this->incidenteService->atualizarAditivoExterno($incidente, $contrato, $validated);

        $urlVoltar = $contrato instanceof ContratoManual
            ? route('admin.contratos.show.manual', $contrato->id)
            : route('admin.processos.show', $contrato->processo_id);

        return redirect($urlVoltar)->with('success', 'Aditivo externo registrado com sucesso.');
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
            'justificativa' => 'nullable|string',
            'arquivo_solicitacao' => 'nullable|file|mimes:pdf|max:10240',
            'nome_solicitante' => 'nullable|string',
            'cargo_solicitante' => 'nullable|string',
            'nome_parecerista' => 'nullable|string',
            'cargo_parecerista' => 'nullable|string',
            'oab_parecerista' => 'nullable|string',
        ];

        // meses_prorrogacao/percentual_valor só existem no <form> do card "Termo
        // Aditivo" — exigi-los incondicionalmente quebrava o salvamento dos demais
        // cards (ex: Solicitação do Aditivo), que não enviam esses campos.
        if ($request->has('meses_prorrogacao')) {
            $rules['meses_prorrogacao'] = 'required_if:tipo,prazo,prazo_valor|nullable|integer|min:1';
        }
        if ($request->has('percentual_valor')) {
            $rules['percentual_valor'] = 'required_if:tipo,valor,prazo_valor|nullable|numeric';
        }

        if ($incidente->categoria === 'obras' && in_array($incidente->tipo, ['valor', 'prazo_valor'])) {
            $rules['arquivo_orcamento_obra'] = 'nullable|file|mimes:pdf|max:10240';
        }

        // Ato "Aceite e Documentação de Regularidade da Contratada" — upload único.
        if ($request->hasFile('arquivo_aceite_documentacao') || $incidente->arquivo_aceite_documentacao_path) {
            $rules['arquivo_aceite_documentacao'] = 'nullable|file|mimes:pdf|max:20480';
        }

        // Percentuais de acréscimo/supressão (natureza explícita do aditivo).
        if ($request->has('percentual_acrescimo') || $request->has('percentual_supressao')) {
            $rules['natureza_alteracao'] = 'required|in:acrescimo,supressao,acrescimo_e_supressao';
            $rules['percentual_acrescimo'] = 'required_if:natureza_alteracao,acrescimo,acrescimo_e_supressao|nullable|numeric|min:0';
            $rules['percentual_supressao'] = 'required_if:natureza_alteracao,supressao,acrescimo_e_supressao|nullable|numeric|min:0';
        }

        if ($request->has('tem_valor_periodo_prorrogado')) {
            $rules['tem_valor_periodo_prorrogado'] = 'nullable|boolean';
            $rules['valor_periodo_prorrogado'] = 'required_if:tem_valor_periodo_prorrogado,1|nullable|numeric|min:0';
        }

        // Ato "Parecer Técnico" (só Obras).
        if ($incidente->categoria === 'obras' && $request->has('situacao_execucao_contratual')) {
            $rules['nome_engenheiro_parecer_tecnico'] = 'nullable|string|max:255';
            $rules['cargo_engenheiro_parecer_tecnico'] = 'nullable|string|max:255';
            $rules['registro_profissional_parecer_tecnico'] = 'nullable|string|max:255';
            $rules['percentual_execucao_fisica'] = 'nullable|numeric|min:0|max:100';
            $rules['situacao_execucao_contratual'] = 'required|in:andamento_regular,com_atraso,paralisada';
            $rules['fatores_atraso'] = 'nullable|array';
            $rules['fatores_atraso.*'] = Rule::in(FatorAtrasoEnum::values());
            $rules['descricao_fatores_atraso'] = 'nullable|string';
            $rules['responsabilidade_atraso'] = 'required_if:tipo,prazo,prazo_valor|nullable|in:imputavel_contratada,nao_imputavel_contratada';
            $rules['texto_vantajosidade_tecnica'] = 'nullable|string';
            $rules['conclusao_tecnica'] = 'nullable|string';

            if (in_array($incidente->tipo, ['valor', 'prazo_valor'])) {
                $rules['limite_legal_aditivo_obras'] = 'required|in:25,50';
            }
        }

        $validated = $request->validate($rules);
        $dados = $validated;

        $dados['arquivo_solicitacao_path'] = $this->resolverPathArquivo(
            $request, 'arquivo_solicitacao', 'incidentes/solicitacoes', $incidente->arquivo_solicitacao_path
        );
        $dados['arquivo_orcamento_obra_path'] = $this->resolverPathArquivo(
            $request, 'arquivo_orcamento_obra', 'incidentes/orcamentos', $incidente->arquivo_orcamento_obra_path
        );
        $dados['arquivo_aceite_documentacao_path'] = $this->resolverPathArquivo(
            $request, 'arquivo_aceite_documentacao', 'incidentes/aceites', $incidente->arquivo_aceite_documentacao_path
        );

        $dados['tipo'] = $incidente->tipo;
        $dados['categoria'] = $incidente->categoria;

        $this->incidenteService->atualizarAditivo($incidente, $contrato, $dados);

        return redirect()->back()->with('success', 'Configurações do aditivo salvas com sucesso.');
    }

    /**
     * Resolve o path salvo de um campo de upload único: novo arquivo enviado
     * substitui; "remover_{campo}=1" sem novo arquivo apaga o atual e some o
     * path; caso contrário mantém o que já estava salvo. Usado pelos 3 uploads
     * de atualizarCampos() (solicitação, orçamento de obra, aceite/regularidade),
     * que antes só sabiam substituir — nunca remover sem trocar por outro.
     */
    /**
     * PDF anexado pelo usuário que deve sair logo abaixo do documento gerado.
     * Usado tanto no "Gerar PDF" individual quanto no "Baixar Todos", para os
     * dois nunca divergirem. Retorna o caminho absoluto, ou null se não houver.
     */
    private function anexoDoDocumento(IncidenteContratual $incidente, string $tipo): ?string
    {
        $path = match ($tipo) {
            'solicitacao_aditivo' => $incidente->arquivo_solicitacao_path,
            'aceite_documentacao_aditivo' => $incidente->arquivo_aceite_documentacao_path,
            'termo_aditivo' => $incidente->categoria === 'obras' ? $incidente->arquivo_orcamento_obra_path : null,
            default => null,
        };

        if (! $path) {
            return null;
        }

        $absoluto = Storage::disk('public')->path($path);

        return file_exists($absoluto) ? $absoluto : null;
    }

    private function resolverPathArquivo(Request $request, string $campo, string $diretorio, ?string $pathAtual): ?string
    {
        if ($request->hasFile($campo)) {
            return $request->file($campo)->store($diretorio, 'public');
        }

        if ($request->boolean('remover_'.$campo) && $pathAtual) {
            Storage::disk('public')->delete($pathAtual);
            return null;
        }

        return $pathAtual;
    }

    /**
     * Ato "Informação de Disponibilidade Orçamentária" — relação hasMany (permite
     * rateio entre fontes de recurso), por isso fora do form genérico de
     * atualizarCampos(). Pré-preenchido na tela a partir do cadastro padrão do
     * Contrato/ContratoManual, mas a gravação aqui é sempre o que o usuário confirmou.
     */
    public function atualizarDotacoes(Request $request, $contrato_id, $incidente_id)
    {
        $contrato = $this->resolverContrato($contrato_id);
        $incidente = $this->buscarIncidente($contrato, $incidente_id);

        // Cada campo é opcional (antes exigia os 4 preenchidos juntos por linha —
        // quem preenchia só 1 ou 2 não conseguia salvar nada).
        $validated = $request->validate([
            'dotacoes' => 'required|array|min:1',
            'dotacoes.*.dotacao_orcamentaria' => 'nullable|string|max:3000',
            'dotacoes.*.valor' => 'nullable|numeric|min:0',
        ]);

        $incidente->dotacoes()->delete();
        foreach ($validated['dotacoes'] as $linha) {
            $incidente->dotacoes()->create($linha);
        }

        return response()->json(['success' => true, 'message' => 'Dotação orçamentária salva com sucesso.']);
    }

    /**
     * Formulário de "dados pendentes" para aditivos legados (criados antes da
     * atualização TCU), que não têm os novos campos obrigatórios preenchidos.
     * gerarDocumentoPdf() bloqueia a geração até IncidenteContratual::
     * dadosTceCompletos() retornar true.
     */
    /**
     * Completa SOMENTE os campos de ABERTURA (ver
     * IncidenteContratual::camposAberturaTceFaltantes()) de um aditivo
     * legado. Percentuais e limite legal continuam sendo preenchidos pelo
     * fluxo normal (cards Solicitação/Parecer Técnico), não aqui.
     */
    public function completarDadosTce(Request $request, $contrato_id, $incidente_id)
    {
        $contrato = $this->resolverContrato($contrato_id);
        $incidente = $this->buscarIncidente($contrato, $incidente_id);

        $validated = $request->validate([
            'tipo_objeto_contrato' => 'required_if:categoria,compras_servicos|nullable|in:compra,servico',
            'contrato_continuo' => 'nullable|boolean',
            'natureza_alteracao' => 'nullable|in:acrescimo,supressao,acrescimo_e_supressao',
        ]);

        $incidente->update(array_filter($validated, fn ($v) => $v !== null));

        return redirect()->back()->with('success', 'Dados complementares do aditivo salvos com sucesso.');
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
        $prefeitura = $processo?->prefeitura ?? $contrato->prefeitura;
        $temAssinaturaEletronica = $processo !== null;
        // Manual ou sistema: sempre permitir selecionar assinante para impressão física.
        // $temAssinaturaEletronica controla apenas o botão de assinatura eletrônica.
        $permitirSelecaoAssinante = true;

        $documentos = [
            'capa_aditivo' => [
                'titulo' => 'Capa',
                'cor' => '#9333ea', // roxo
                'requer_assinatura' => $permitirSelecaoAssinante,
                'campos' => [],
            ],
            'solicitacao_aditivo' => [
                'titulo' => 'Solicitação do Aditivo',
                'cor' => '#009496',
                'requer_assinatura' => $permitirSelecaoAssinante,
                'campos' => array_merge(
                    $this->camposValorSolicitacao($incidente),
                    [
                        [
                            'name' => 'justificativa',
                            'label' => 'Justificativa para a solicitação de Aditivo',
                            'tipo' => 'textarea',
                            'ia' => true,
                            'ia_campo' => 'justificativa_aditivo',
                            'value' => $incidente->justificativa,
                        ],

                        [
                            'name' => 'arquivo_solicitacao',
                            'label' => 'Anexar PDF da solicitação de aditivo',
                            'tipo' => 'file',
                            'value' => $incidente->arquivo_solicitacao_path,
                        ],
                    ]
                ),
            ],
            'aceite_documentacao_aditivo' => [
                'titulo' => 'Aceite e Documentação de Regularidade da Contratada',
                'cor' => '#16a34a', // verde
                'requer_assinatura' => $permitirSelecaoAssinante,
                'campos' => [
                    [
                        'name' => 'arquivo_aceite_documentacao',
                        'label' => 'Anexar PDF com aceite e documentação de regularidade/habilitação da empresa',
                        'tipo' => 'file',
                        'value' => $incidente->arquivo_aceite_documentacao_path,
                    ],
                ],
            ],
        ];

        // Ato "Parecer Técnico" — exclusivo de Obras (Compras e Serviços não tem
        // engenharia envolvida no aditivo).
        if ($incidente->categoria === 'obras') {
            $documentos['parecer_tecnico_aditivo'] = [
                'titulo' => 'Parecer Técnico',
                'cor' => '#7c3aed', // roxo
                'requer_assinatura' => $permitirSelecaoAssinante,
                'campos' => $this->camposParecerTecnico($incidente),
            ];
        }

        // Ato "Informação de Disponibilidade Orçamentária" — condicional: só quando
        // há acréscimo de valor (ou valor estimado para o período prorrogado).
        if ($incidente->temAcrescimoDeValor()) {
            $documentos['dotacao_orcamentaria_aditivo'] = [
                'titulo' => 'Informação de Disponibilidade Orçamentária',
                'cor' => '#0891b2', // ciano
                // Quem assina varia por prefeitura (Finanças, Fazenda, Contabilidade...)
                'requer_assinatura' => $permitirSelecaoAssinante,
                'campos' => [], // tela própria (ver atualizarDotacoes()), não o form genérico
            ];
        }

        $documentos += [
            // Ofício do Prefeito à Procuradoria — vem depois do aceite e da dotação
            // (antes era a 2ª página da Solicitação do Aditivo, fora de ordem).
            'solicitacao_parecer_aditivo' => [
                'titulo' => 'Solicitação de Parecer Jurídico',
                'cor' => '#be185d', // rosa
                'requer_assinatura' => $permitirSelecaoAssinante,
                'campos' => [],
            ],
            'parecer_juridico_aditivo' => [
                'titulo' => 'Parecer Jurídico',
                'cor' => '#dc2626', // vermelho
                'requer_assinatura' => $permitirSelecaoAssinante,
                'campos' => [
                    [
                        'name' => 'nome_parecerista',
                        'label' => 'Nome do Parecerista',
                        'tipo' => 'string',
                        'value' => $incidente->nome_parecerista,
                    ],
                    [
                        'name' => 'cargo_parecerista',
                        'label' => 'Cargo do Parecerista',
                        'tipo' => 'string',
                        'value' => $incidente->cargo_parecerista,
                    ],
                    [
                        'name' => 'oab_parecerista',
                        'label' => 'OAB do Parecerista',
                        'tipo' => 'string',
                        'value' => $incidente->oab_parecerista,
                    ],
                ],
            ],
            'autorizacao_prefeito_aditivo' => [
                'titulo' => 'Autorização do Prefeito',
                'cor' => '#ea580c', // laranja
                'requer_assinatura' => $permitirSelecaoAssinante,
                'campos' => [],
            ],
            'termo_aditivo' => [
                'titulo' => 'Termo Aditivo ao Contrato',
                'cor' => '#0284c7', // azul
                'requer_assinatura' => $permitirSelecaoAssinante,
                'campos' => [],
            ],
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
            // percentual_valor (legado) não é mais editável aqui — a natureza da
            // alteração e os percentuais de acréscimo/supressão são decididos na
            // abertura do aditivo e editáveis no card "Solicitação do Aditivo"
            // (ver camposValorSolicitacao()).
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

        $resumoFinanceiro = $this->calculoFinanceiro->resumoFinanceiro($incidente, $contrato);
        $numeroOrdinal = $this->calculoFinanceiro->numeroOrdinal($incidente, $contrato);
        $dotacoesAtuais = $incidente->dotacoes()->get();
        $dotacaoSugerida = [
            'dotacao_orcamentaria' => collect([
                $contrato->dotacao_unidade_orcamentaria ?? null,
                $contrato->dotacao_classificacao_funcional_programatica ?? null,
                $contrato->dotacao_elemento_despesa ?? null,
                $contrato->fonte_recurso ?? null,
            ])->filter()->implode(' / '),
        ];
        // O aviso/formulário de "dados pendentes" só aparece para aditivos
        // LEGADOS (criados antes desta atualização, sem os campos de
        // abertura) — nunca para um aditivo novo que ainda está no meio do
        // preenchimento normal do fluxo (ver IncidenteContratual::ehAditivoLegado()).
        $ehAditivoLegado = $incidente->ehAditivoLegado();
        $camposAberturaFaltantes = $incidente->camposAberturaTceFaltantes();

        $unidadesData = $prefeitura?->unidades->map(function ($u) {
            $dataPortaria = $u->data_portaria;
            if ($dataPortaria && ! ($dataPortaria instanceof \Carbon\Carbon)) {
                try { $dataPortaria = \Carbon\Carbon::parse($dataPortaria); } catch (\Exception $e) { $dataPortaria = null; }
            }
            return [
                'id'                  => $u->id,
                'nome'                => $u->nome,
                'servidor_responsavel'=> $u->servidor_responsavel,
                'cargo'               => $u->cargo,
                'numero_portaria'     => $u->numero_portaria,
                'data_portaria'       => $dataPortaria ? $dataPortaria->format('Y-m-d') : null,
            ];
        }) ?? collect();

        return view('Admin.IncidentesContratuais.documentos', compact(
            'contrato', 'incidente', 'processo', 'prefeitura', 'documentos', 'documentosGerados', 'temAssinaturaEletronica',
            'resumoFinanceiro', 'numeroOrdinal', 'dotacoesAtuais', 'dotacaoSugerida', 'ehAditivoLegado', 'camposAberturaFaltantes',
            'unidadesData'
        ));
    }

    /**
     * Campos de natureza/percentual de valor exibidos no card "Solicitação do
     * Aditivo" — captados na abertura (store()), mas editáveis depois aqui,
     * seguindo o mesmo padrão genérico de atualizarCampos().
     */
    private function camposValorSolicitacao(IncidenteContratual $incidente): array
    {
        if (! in_array($incidente->tipo, ['valor', 'prazo_valor'])) {
            return [];
        }

        $campos = [
            [
                'name' => 'natureza_alteracao',
                'label' => 'Natureza da Alteração',
                'tipo' => 'select',
                'opcoes' => \App\Enums\NaturezaAlteracaoAditivoEnum::cases(),
                'value' => $incidente->natureza_alteracao,
            ],
            [
                'name' => 'percentual_acrescimo',
                'label' => 'Percentual de Acréscimo (%)',
                'tipo' => 'number',
                'step' => '0.01',
                'value' => $incidente->percentual_acrescimo,
            ],
            [
                'name' => 'percentual_supressao',
                'label' => 'Percentual de Supressão (%)',
                'tipo' => 'number',
                'step' => '0.01',
                'value' => $incidente->percentual_supressao,
            ],
        ];

        if ($incidente->categoria === 'compras_servicos' && $incidente->tipo === 'prazo_valor') {
            $campos[] = [
                'name' => 'tem_valor_periodo_prorrogado',
                'label' => 'Há valor estimado para o período prorrogado?',
                'tipo' => 'checkbox',
                'value' => $incidente->tem_valor_periodo_prorrogado,
            ];
            $campos[] = [
                'name' => 'valor_periodo_prorrogado',
                'label' => 'Valor estimado para o período prorrogado (preços unitários mantidos)',
                'tipo' => 'number',
                'step' => '0.01',
                'value' => $incidente->valor_periodo_prorrogado,
            ];
        }

        return $campos;
    }

    /**
     * Campos do ato "Parecer Técnico" (exclusivo Obras).
     */
    private function camposParecerTecnico(IncidenteContratual $incidente): array
    {
        $campos = [
            ['name' => 'nome_engenheiro_parecer_tecnico', 'label' => 'Nome do Engenheiro(a) Responsável', 'tipo' => 'string', 'value' => $incidente->nome_engenheiro_parecer_tecnico],
            ['name' => 'cargo_engenheiro_parecer_tecnico', 'label' => 'Cargo', 'tipo' => 'string', 'value' => $incidente->cargo_engenheiro_parecer_tecnico],
            ['name' => 'registro_profissional_parecer_tecnico', 'label' => 'CREA/CAU nº', 'tipo' => 'string', 'value' => $incidente->registro_profissional_parecer_tecnico],
            ['name' => 'percentual_execucao_fisica', 'label' => 'Percentual de Execução Física (%)', 'tipo' => 'number', 'step' => '0.01', 'value' => $incidente->percentual_execucao_fisica],
            ['name' => 'situacao_execucao_contratual', 'label' => 'Situação da Execução', 'tipo' => 'select', 'opcoes' => \App\Enums\SituacaoExecucaoContratualEnum::cases(), 'value' => $incidente->situacao_execucao_contratual],
        ];

        if (in_array($incidente->tipo, ['prazo', 'prazo_valor'])) {
            $campos[] = ['name' => 'fatores_atraso', 'label' => 'Fatores que Afetaram o Prazo', 'tipo' => 'checkboxes', 'opcoes' => \App\Enums\FatorAtrasoEnum::cases(), 'value' => $incidente->fatores_atraso ?? []];
            $campos[] = ['name' => 'descricao_fatores_atraso', 'label' => 'Descrição Técnica dos Fatores que Afetaram o Prazo', 'tipo' => 'textarea', 'ia' => true, 'ia_campo' => 'descricao_fatores_atraso_aditivo', 'value' => $incidente->descricao_fatores_atraso];
            $campos[] = ['name' => 'responsabilidade_atraso', 'label' => 'Responsabilidade pela Ocorrência do Atraso', 'tipo' => 'select', 'opcoes' => \App\Enums\ResponsabilidadeAtrasoEnum::cases(), 'value' => $incidente->responsabilidade_atraso];
        }

        if (in_array($incidente->tipo, ['valor', 'prazo_valor'])) {
            $campos[] = ['name' => 'limite_legal_aditivo_obras', 'label' => 'Limite Legal Aplicável', 'tipo' => 'select', 'opcoes' => \App\Enums\LimiteLegalObraEnum::cases(), 'value' => $incidente->limite_legal_aditivo_obras];
        }

        $campos[] = ['name' => 'texto_vantajosidade_tecnica', 'label' => 'Vantajosidade e Interesse Público', 'tipo' => 'textarea', 'value' => $incidente->texto_vantajosidade_tecnica];
        $campos[] = ['name' => 'conclusao_tecnica', 'label' => 'Conclusão Técnica', 'tipo' => 'textarea', 'value' => $incidente->conclusao_tecnica];

        return $campos;
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
                        'caminho' => 'gerado_dinamicamente',
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
        $incidente = IncidenteContratual::with(['itens.loteContratado', 'dotacoes'])
            ->where('contratavel_id', $contrato->id)
            ->where('contratavel_type', get_class($contrato))
            ->findOrFail($incidente_id);

        // Bloqueia a geração até todos os campos exigidos estarem preenchidos.
        // Mensagem diferente conforme o motivo: aditivo LEGADO (criado antes
        // desta atualização, precisa do formulário de "dados pendentes" — ver
        // completarDadosTce()) vs. aditivo novo ainda no meio do preenchimento
        // normal do fluxo (basta continuar preenchendo os cards acima).
        // Capa, aceite, dotação e solicitação de parecer não usam esses campos.
        $documentosSemDadosTce = ['capa_aditivo', 'aceite_documentacao_aditivo', 'dotacao_orcamentaria_aditivo', 'solicitacao_parecer_aditivo'];
        if (! in_array($tipo, $documentosSemDadosTce, true) && ! $incidente->dadosTceCompletos()) {
            $nomesCampos = [
                'natureza_alteracao'         => 'Natureza da Alteração',
                'percentual_acrescimo'       => 'Percentual de Acréscimo',
                'percentual_supressao'       => 'Percentual de Supressão',
                'limite_legal_aditivo_obras' => 'Limite Legal Aplicável',
                'contrato_continuo'          => 'Contrato Contínuo',
                'tipo_objeto_contrato'       => 'Tipo de Objeto do Contrato',
            ];
            $faltantes = collect($incidente->camposTceFaltantes())
                ->map(fn ($c) => $nomesCampos[$c] ?? $c)
                ->join(', ');

            $mensagem = $incidente->ehAditivoLegado()
                ? 'Complete os novos campos exigidos pelo Tribunal de Contas antes de gerar os documentos: '.$faltantes.'.'
                : 'Campos obrigatórios não preenchidos: '.$faltantes.'. Preencha-os no card "Solicitação do Aditivo" (ou Parecer Técnico para obras) e salve antes de gerar.';

            return redirect()
                ->route($this->routeName('documentos'), ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id])
                ->withErrors(['tce' => $mensagem]);
        }

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
                $tituloArquivo = 'Capa_Aditivo_'.$numContratoSafe;
                break;
            case 'solicitacao_aditivo':
                $viewName = 'Admin.Processos.pdf.aditivos.solicitacao';
                $tituloArquivo = 'Solicitacao_Aditivo_'.$numContratoSafe;
                break;
            case 'aceite_documentacao_aditivo':
                $viewName = 'Admin.Processos.pdf.aditivos.aceite_documentacao';
                $tituloArquivo = 'Aceite_Documentacao_Aditivo_'.$numContratoSafe;
                break;
            case 'parecer_tecnico_aditivo':
                $viewName = 'Admin.Processos.pdf.aditivos.parecer_tecnico';
                $tituloArquivo = 'Parecer_Tecnico_Aditivo_'.$numContratoSafe;
                break;
            case 'dotacao_orcamentaria_aditivo':
                $viewName = 'Admin.Processos.pdf.aditivos.dotacao_orcamentaria';
                $tituloArquivo = 'Dotacao_Orcamentaria_Aditivo_'.$numContratoSafe;
                break;
            case 'solicitacao_parecer_aditivo':
                $viewName = 'Admin.Processos.pdf.aditivos.solicitacao_parecer';
                $tituloArquivo = 'Solicitacao_Parecer_Aditivo_'.$numContratoSafe;
                break;
            case 'parecer_juridico_aditivo':
                $viewName = 'Admin.Processos.pdf.aditivos.parecer';
                $tituloArquivo = 'Parecer_Juridico_Aditivo_'.$numContratoSafe;
                break;
            case 'autorizacao_prefeito_aditivo':
                $viewName = 'Admin.Processos.pdf.aditivos.autorizacao';
                $tituloArquivo = 'Autorizacao_Aditivo_'.$numContratoSafe;
                break;
            case 'termo_aditivo':
                if ($incidente->categoria === 'obras') {
                    $viewName = 'Admin.Processos.pdf.aditivos.termo_obras';
                } else {
                    $viewName = 'Admin.Processos.pdf.aditivos.termo_compras';
                }
                $tituloArquivo = 'Termo_Aditivo_'.$numContratoSafe;
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

        // Recupera a seleção de assinantes, filtrada pelo incidente (processo_id pode ser nulo para manuais)
        $documentoSelecao = DocumentoSelecaoAssinantes::where('incidente_id', $incidente->id)
            ->where('tipo_documento', $tipo)
            ->when($processo, fn ($q) => $q->where('processo_id', $processo->id))
            ->first();

        // Fallback automático: se não há seleção manual, tenta popular pelo orgao_responsavel
        // da unidade cadastrada na prefeitura (evita mostrar "Secretário(a)" genérico no PDF)
        if (! $documentoSelecao && $prefeitura && in_array($tipo, ['solicitacao_aditivo', 'aceite_documentacao_aditivo'])) {
            $orgaoNome = $contrato->dados_contratante['orgao_responsavel'] ?? null;
            if ($orgaoNome) {
                $unidadeMatch = $prefeitura->unidades()
                    ->where(fn ($q) => $q->where('nome', $orgaoNome)->orWhere('nome', 'like', '%'.$orgaoNome.'%'))
                    ->first();
                if ($unidadeMatch && $unidadeMatch->servidor_responsavel) {
                    $documentoSelecao = (object) [
                        'assinantes' => [[
                            'responsavel'  => $unidadeMatch->servidor_responsavel,
                            'unidade_nome' => $unidadeMatch->nome,
                            'cargo'        => $unidadeMatch->cargo,
                            'portaria'     => $unidadeMatch->numero_portaria ?? '',
                            'data_portaria' => '',
                        ]],
                    ];
                }
            }
        }

        // "Baixar" (?download=1) reaproveita a geração, sem mexer em "Gerado em".
        $baixar = request()->boolean('download');
        $disposition = $baixar ? 'attachment' : 'inline';

        // Marca como gerado
        if ($documento) {
            if (! $baixar) {
                $documento->update(['gerado_em' => now()]);
            }
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

        $vm = app(\App\Services\Aditivos\AditivoViewModelFactory::class)->build($incidente, $contrato, $objetoContrato);
        $textoJuridico = app(\App\Services\Aditivos\AditivoTextoJuridicoService::class);

        $pdfOutput = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, compact(
            'contrato',
            'incidente',
            'processo',
            'prefeitura',
            'detalhe',
            'objetoContrato',
            'data_selecionada',
            'documentoSelecao',
            'vm',
            'textoJuridico'
        ))->setPaper('a4', 'portrait')->output();

        // Mesclar arquivo PDF anexado nos documentos que têm essa opção
        $pathAnexoMesclavel = $this->anexoDoDocumento($incidente, $tipo);

        if ($pathAnexoMesclavel) {
            $tmpDeclaracao = tempnam(sys_get_temp_dir(), 'adit_decl_') . '.pdf';
            file_put_contents($tmpDeclaracao, $pdfOutput);

            $tmpMesclado = tempnam(sys_get_temp_dir(), 'adit_merged_') . '.pdf';
            exec('gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile=' . escapeshellarg($tmpMesclado)
                . ' ' . escapeshellarg($tmpDeclaracao) . ' ' . escapeshellarg($pathAnexoMesclavel), $out, $exitCode);

            @unlink($tmpDeclaracao);

            if ($exitCode === 0 && file_exists($tmpMesclado) && filesize($tmpMesclado) > 0) {
                $conteudoMesclado = file_get_contents($tmpMesclado);
                @unlink($tmpMesclado);
                return response($conteudoMesclado, 200, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => $disposition.'; filename="' . $tituloArquivo . '.pdf"',
                ]);
            }
            @unlink($tmpMesclado);
        }

        return response($pdfOutput, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="' . $tituloArquivo . '.pdf"',
        ]);
    }

    /**
     * GET: retorna a seleção de assinantes salva para um documento do aditivo
     * de um ContratoManual (sem processo_id). Retorna JSON no mesmo formato
     * que SelecaoAssinanteController::obter(), para reutilizar o bridge script.
     */
    public function assinanteObter(Request $request, $contrato_id, $incidente_id)
    {
        $contrato = $this->resolverContrato($contrato_id);
        $incidente = $this->buscarIncidente($contrato, $incidente_id);

        $tipo = $request->query('tipo_documento');

        $selecao = DocumentoSelecaoAssinantes::where('incidente_id', $incidente->id)
            ->where('tipo_documento', $tipo)
            ->whereNull('processo_id')
            ->first();

        return response()->json([
            'success' => true,
            'data'    => [
                'assinantes' => $selecao?->assinantes ?? [],
            ],
        ]);
    }

    /**
     * POST: salva a seleção de assinantes para um documento do aditivo
     * de um ContratoManual (sem processo_id).
     */
    public function assinanteSalvar(Request $request, $contrato_id, $incidente_id)
    {
        $contrato = $this->resolverContrato($contrato_id);
        $incidente = $this->buscarIncidente($contrato, $incidente_id);

        $dados = $request->validate([
            'tipo_documento' => 'required|string',
            'assinantes'     => 'required|array',
            'assinantes.*.unidade_id'     => 'nullable',
            'assinantes.*.responsavel'    => 'nullable|string',
            'assinantes.*.unidade_nome'   => 'nullable|string',
            'assinantes.*.portaria'       => 'nullable|string',
            'assinantes.*.numero_portaria' => 'nullable|string',
            'assinantes.*.data_portaria'  => 'nullable|string',
        ]);

        // Normaliza: bridge script envia `numero_portaria`; view JS envia `portaria` — aceita ambos
        $dados['assinantes'] = array_map(function ($a) {
            if (empty($a['portaria']) && ! empty($a['numero_portaria'])) {
                $a['portaria'] = $a['numero_portaria'];
            }
            unset($a['numero_portaria']);
            return $a;
        }, $dados['assinantes']);

        // Enriquecer com o nome da unidade a partir do id, se vier apenas o id
        $prefeitura = $contrato->prefeitura;
        $assinantesEnriquecidos = collect($dados['assinantes'])->map(function ($a) use ($prefeitura) {
            if (empty($a['unidade_nome']) && ! empty($a['unidade_id'])) {
                $unidade = $prefeitura?->unidades()->find($a['unidade_id']);
                $a['unidade_nome'] = $unidade?->nome ?? '';
                $responsavel = $unidade?->responsavel;
                if ($responsavel) {
                    $a['responsavel']   = $a['responsavel']   ?? $responsavel->nome ?? '';
                    $a['portaria']      = $a['portaria']      ?? $responsavel->portaria ?? '';
                    $a['data_portaria'] = $a['data_portaria'] ?? ($responsavel->data_portaria?->format('d/m/Y') ?? '');
                }
            }
            return $a;
        })->values()->toArray();

        DocumentoSelecaoAssinantes::updateOrCreate(
            [
                'processo_id'    => null,
                'incidente_id'   => $incidente->id,
                'tipo_documento' => $dados['tipo_documento'],
                'homologacao_id' => null,
                'vencedor_id'    => null,
            ],
            ['assinantes' => $assinantesEnriquecidos]
        );

        return response()->json(['success' => true]);
    }

    /**
     * GET: gera e baixa todos os documentos do aditivo em um único PDF mesclado.
     * Usa a mesma lógica de gerarDocumentoPdf() para cada tipo, salva temporariamente
     * e mescla com Ghostscript.
     */
    public function baixarTodosDocumentos($contrato_id, $incidente_id)
    {
        $ehManual = request()->routeIs('admin.incidentes-manual.*');
        $contrato = $ehManual
            ? ContratoManual::with(['prefeitura', 'empresa'])->findOrFail($contrato_id)
            : Contrato::with(['processo.prefeitura', 'processo.detalhe', 'processo.documentos'])->findOrFail($contrato_id);
        $incidente = IncidenteContratual::with(['itens.loteContratado', 'dotacoes'])
            ->where('contratavel_id', $contrato->id)
            ->where('contratavel_type', get_class($contrato))
            ->findOrFail($incidente_id);

        $processo = $contrato instanceof Contrato ? $contrato->processo : null;
        $prefeitura = $processo?->prefeitura ?? $contrato->prefeitura;
        $detalhe = $processo?->detalhe;
        $objetoContrato = $processo?->objeto ?? $contrato->objeto ?? '';

        if ($contrato instanceof ContratoManual) {
            $empresa = $contrato->empresa;
            $contrato->dados_contratante = [
                'razao_social'    => $empresa?->razao_social ?? 'CONTRATADA',
                'cnpj'            => $empresa?->cnpj_formatado ?? $empresa?->cnpj ?? 'CNPJ',
                'endereco'        => $empresa?->endereco ?? 'Endereço da Empresa',
                'representante'   => $empresa?->representante ?? 'Representante não informado',
                'cpf_representante' => 'CPF',
                'orgao_responsavel' => $contrato->secretaria?->nome ?? 'Secretaria Municipal',
            ];
        }

        $vm = app(\App\Services\Aditivos\AditivoViewModelFactory::class)->build($incidente, $contrato, $objetoContrato);
        $textoJuridico = app(\App\Services\Aditivos\AditivoTextoJuridicoService::class);

        // Mesma ordem cronológica da tela (documentos()): solicitação → aceite →
        // [parecer técnico] → dotação → solicitação de parecer → parecer → autorização → termo.
        $tiposView = array_filter([
            'capa_aditivo'                 => 'Admin.Processos.pdf.aditivos.capa',
            'solicitacao_aditivo'          => 'Admin.Processos.pdf.aditivos.solicitacao',
            'aceite_documentacao_aditivo'  => 'Admin.Processos.pdf.aditivos.aceite_documentacao',
            'parecer_tecnico_aditivo'      => $incidente->categoria === 'obras' ? 'Admin.Processos.pdf.aditivos.parecer_tecnico' : null,
            'dotacao_orcamentaria_aditivo' => $incidente->temAcrescimoDeValor() ? 'Admin.Processos.pdf.aditivos.dotacao_orcamentaria' : null,
            'solicitacao_parecer_aditivo'  => 'Admin.Processos.pdf.aditivos.solicitacao_parecer',
            'parecer_juridico_aditivo'     => 'Admin.Processos.pdf.aditivos.parecer',
            'autorizacao_prefeito_aditivo' => 'Admin.Processos.pdf.aditivos.autorizacao',
            'termo_aditivo'                => $incidente->categoria === 'obras'
                ? 'Admin.Processos.pdf.aditivos.termo_obras'
                : 'Admin.Processos.pdf.aditivos.termo_compras',
        ]);

        $arquivosTemp = [];

        foreach ($tiposView as $tipo => $viewName) {
            $documento = Documento::where('incidente_id', $incidente->id)
                ->where('tipo_documento', $tipo)->first();
            $data_selecionada = $documento?->data_selecionada ?? now()->toDateString();

            $documentoSelecao = DocumentoSelecaoAssinantes::where('incidente_id', $incidente->id)
                ->where('tipo_documento', $tipo)
                ->when($processo, fn ($q) => $q->where('processo_id', $processo->id))
                ->first();

            if (! $documentoSelecao && $prefeitura && in_array($tipo, ['solicitacao_aditivo', 'aceite_documentacao_aditivo'])) {
                $orgaoNome = $contrato->dados_contratante['orgao_responsavel'] ?? null;
                if ($orgaoNome) {
                    $unidadeMatch = $prefeitura->unidades()
                        ->where(fn ($q) => $q->where('nome', $orgaoNome)->orWhere('nome', 'like', '%'.$orgaoNome.'%'))
                        ->first();
                    if ($unidadeMatch && $unidadeMatch->servidor_responsavel) {
                        $documentoSelecao = (object) [
                            'assinantes' => [[
                                'responsavel'  => $unidadeMatch->servidor_responsavel,
                                'unidade_nome' => $unidadeMatch->nome,
                                'cargo'        => $unidadeMatch->cargo,
                                'portaria'     => $unidadeMatch->numero_portaria ?? '',
                                'data_portaria' => '',
                            ]],
                        ];
                    }
                }
            }

            $pdfOutput = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, compact(
                'contrato', 'incidente', 'processo', 'prefeitura', 'detalhe',
                'objetoContrato', 'data_selecionada', 'documentoSelecao', 'vm', 'textoJuridico'
            ))->setPaper('a4', 'portrait')->output();

            $tmpPath = tempnam(sys_get_temp_dir(), 'aditivo_') . '.pdf';
            file_put_contents($tmpPath, $pdfOutput);
            $arquivosTemp[] = $tmpPath;

            // PDF anexado ao documento entra logo abaixo dele, como no "Gerar PDF" individual
            if ($pathAnexo = $this->anexoDoDocumento($incidente, $tipo)) {
                $arquivosTemp[] = $pathAnexo;
            }
        }

        if (empty($arquivosTemp)) {
            return back()->withErrors(['pdf' => 'Nenhum documento gerado para baixar.']);
        }

        $mergedPath = tempnam(sys_get_temp_dir(), 'aditivo_merged_') . '.pdf';
        $listaArquivos = implode(' ', array_map('escapeshellarg', $arquivosTemp));
        exec("gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile=" . escapeshellarg($mergedPath) . " {$listaArquivos}", $saida, $status);

        foreach ($arquivosTemp as $tmp) {
            if (str_starts_with($tmp, sys_get_temp_dir()) && file_exists($tmp)) {
                @unlink($tmp);
            }
        }

        $numContratoSafe = str_replace(['/', '\\'], '-', $contrato->numero_contrato ?? 'Sem_Numero');
        $nomeArquivo = "Aditivo_{$numContratoSafe}_Completo.pdf";

        // Aplicar carimbo lateral com numeração de páginas
        $outputPath = tempnam(sys_get_temp_dir(), 'aditivo_carimbado_') . '.pdf';
        $carimbado = $this->aplicarCarimboNumeracao($mergedPath, $outputPath, $prefeitura);

        if ($carimbado) {
            @unlink($mergedPath);
        } else {
            // Fallback: retorna sem carimbo se o FPDI falhar (antes o merged era
            // apagado antes deste fallback e a resposta saía vazia)
            @unlink($outputPath);
            $outputPath = $mergedPath;
        }

        $conteudo = file_get_contents($outputPath);
        @unlink($outputPath);

        return response($conteudo, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $nomeArquivo . '"',
        ]);
    }

    /**
     * Abre o PDF mesclado com FPDI e adiciona o carimbo lateral de numeração de páginas
     * em todas as páginas, igual ao ProcessoPdfService::adicionarCarimbo() para processos,
     * mas sem depender de um objeto Processo.
     * Retorna true se o arquivo de saída foi gerado com sucesso.
     */
    private function aplicarCarimboNumeracao(string $inputPath, string $outputPath, $prefeitura): bool
    {
        try {
            $fpdi = new Fpdi;

            $fontPath = public_path('storage/app/public/fonts/Aptos.ttf');
            if (file_exists($fontPath)) {
                $fpdi->AddFont('Aptos', '', 'Aptos.ttf', true);
                $fpdi->SetFont('Aptos', '', 8);
            } else {
                $fpdi->SetFont('helvetica', '', 6);
            }

            $totalPages = $fpdi->setSourceFile($inputPath);

            for ($pagina = 1; $pagina <= $totalPages; $pagina++) {
                $tplId = $fpdi->importPage($pagina);
                $size  = $fpdi->getTemplateSize($tplId);
                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                $fpdi->AddPage($orientation, [$size['width'], $size['height']]);
                $fpdi->useTemplate($tplId);

                // Carimbo lateral direito
                $pageWidth  = $fpdi->GetPageWidth();
                $pageHeight = $fpdi->GetPageHeight();
                $boxWidth   = 8;
                $boxHeight  = 150;
                $x = $pageWidth  - $boxWidth - 1;
                $y = ($pageHeight - $boxHeight) / 2;

                $fpdi->SetDrawColor(0, 0, 0);
                $fpdi->Rect($x, $y, $boxWidth, $boxHeight, 'D');
                $fpdi->SetTextColor(0, 0, 0);

                $codigoAut  = ($prefeitura->id ?? '0') . now()->format('HisdmY');
                $textoCarimbo = "Documento numerado | Pág. {$pagina}/{$totalPages} | " .
                    "Plataforma GestGov - Licenciado para Prefeitura de " . ($prefeitura->cidade ?? 'Município') .
                    ". Cod. de Autenticação: {$codigoAut} - Para autenticar acesse gestgov.com.br/autenticacao";

                $fpdi->StartTransform();
                $rotateX = $x + ($boxWidth / 2);
                $rotateY = $y + ($boxHeight / 2);
                $fpdi->Rotate(90, $rotateX, $rotateY);
                $textX = $rotateX - ($boxHeight / 2);
                $textY = $rotateY - ($boxWidth / 2);
                $fpdi->SetXY($textX, $textY);
                $fpdi->MultiCell($boxHeight, $boxWidth, $textoCarimbo, 0, 'C', false, 1, '', '', true, 0, false, true, 0, 'T', false);
                $fpdi->StopTransform();
            }

            $fpdi->Output($outputPath, 'F');

            return file_exists($outputPath) && filesize($outputPath) > 0;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao aplicar carimbo no aditivo', ['erro' => $e->getMessage()]);
            return false;
        }
    }

    public function destroy($contrato_id, $incidente_id)
    {
        $contrato = $this->resolverContrato($contrato_id);
        $incidente = $this->buscarIncidente($contrato, $incidente_id);

        // Desfaz o efeito deste aditivo no contrato (prazo/valor) antes de excluir.
        $this->incidenteService->reverterEfeitosNoContrato($incidente, $contrato);

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
