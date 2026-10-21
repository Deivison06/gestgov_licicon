@extends('layouts.app')
@section('page-title', 'Detalhes da Fiscalização')
@section('page-subtitle', 'Relatório de Fiscalização Nº ' . ($fiscalizacao->numero_fiscalizacao ?? $fiscalizacao->id))

@section('content')

@php
    $tipo = $fiscalizacao->tipo_contrato?->value;
    $info = $fiscalizacao->contrato_info;

    // Labels dinâmicas por tipo (mantidas da lógica anterior)
    $labelExecucao = match($tipo) {
        'compras' => 'Como o objeto do contrato foi executado neste período?',
        'servicos' => 'Como o objeto do contrato foi executado neste período?',
        'obras' => 'Execução do Objeto',
        default => 'Execução do Objeto',
    };
    $labelQualidade = match($tipo) {
        'compras' => 'Os produtos entregues ou os serviços executados estão de acordo com a qualidade exigida no contrato?',
        'servicos' => 'Os produtos entregues ou os serviços executados estão de acordo com a qualidade exigida no contrato?',
        'obras' => 'Qualidade dos serviços Executados',
        default => 'Qualidade das Entregas',
    };
    $ocorrenciaEstruturada = in_array($tipo, ['compras', 'servicos'], true);
@endphp

<div class="py-8">
    {{-- BARRA DE AÇÕES --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-[var(--ink)]">Fiscalização {{ $fiscalizacao->numero_fiscalizacao }}</h2>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            
            <a href="{{ route('admin.fiscalizacoes.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-[var(--ink)] transition-colors bg-[var(--card)] border border-[var(--line)] rounded-xl hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] shadow-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>

            <a href="{{ route('admin.fiscalizacoes.edit', $fiscalizacao->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-[var(--ink)] transition-colors bg-[var(--hover-bg)] border border-[var(--line)] rounded-xl hover:bg-[var(--card-3)] hover:text-[var(--mint)] shadow-sm">
                <i class="fas fa-edit text-[var(--warn-2)]"></i> Editar Dados
            </a>

            <div class="hidden md:block w-px h-8 bg-[var(--line)] mx-1"></div>

            <a href="{{ route('admin.fiscalizacoes.relatorio-tecnico', $fiscalizacao->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-[var(--on-mint)] transition-all bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] hover:scale-105 shadow-sm" target="_blank">
                <i class="fas fa-file-contract"></i> Relatório Técnico
            </a>

            <a href="{{ route('admin.fiscalizacoes.notificacoes', $fiscalizacao->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-[var(--bad-strong)] transition-colors bg-[var(--bad-bg)] border border-[var(--bad-border)] rounded-xl hover:bg-[var(--bad-bg)] shadow-sm" target="_blank">
                <i class="fas fa-exclamation-triangle"></i> Notificações e Recomendações
            </a>
            
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-6">

            <div class="card !rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-[var(--line)] bg-[var(--card-2)]">
                    <h3 class="text-lg font-bold text-[var(--ink)]">Dados do Relatório</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-semibold text-[var(--ink-2)] col-span-1">Resultado</dt>
                            <dd class="text-sm text-[var(--ink)] col-span-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $fiscalizacao->conclusao_badge_class }}">
                                    {{ $fiscalizacao->conclusao_texto }}
                                </span>
                            </dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-[var(--line)] pt-3">
                            <dt class="text-sm font-semibold text-[var(--ink-2)] col-span-1">Data</dt>
                            <dd class="text-sm text-[var(--ink)] col-span-2">{{ $fiscalizacao->data_fiscalizacao?->format('d/m/Y') }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-[var(--line)] pt-3">
                            <dt class="text-sm font-semibold text-[var(--ink-2)] col-span-1">Tipo</dt>
                            <dd class="text-sm text-[var(--ink)] col-span-2">{{ $fiscalizacao->tipo_contrato?->getDisplayName() ?? 'N/I' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-[var(--line)] pt-3">
                            <dt class="text-sm font-semibold text-[var(--ink-2)] col-span-1">Fiscal</dt>
                            <dd class="text-sm text-[var(--ink)] col-span-2 font-medium">{{ $fiscalizacao->user->name ?? 'Sistema' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-[var(--line)] pt-3">
                            <dt class="text-sm font-semibold text-[var(--ink-2)] col-span-1">Criado em</dt>
                            <dd class="text-sm text-[var(--ink)] col-span-2">{{ $fiscalizacao->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="card !rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-[var(--line)] bg-[var(--card-2)]">
                    <h3 class="text-lg font-bold text-[var(--ink)]">Contrato Vinculado</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs font-semibold text-[var(--ink-2)] uppercase tracking-wider">Empresa Contratada</dt>
                            <dd class="text-sm font-bold text-[var(--mint)] mt-1">{{ $info['razao_social'] }}</dd>
                            <dd class="text-xs text-[var(--ink-2)]">{{ $info['cnpj'] }}</dd>
                        </div>
                        <div class="pt-3 border-t border-[var(--line)]">
                            <dt class="text-xs font-semibold text-[var(--ink-2)] uppercase">Nº Contrato / Processo</dt>
                            <dd class="text-sm text-[var(--ink)] mt-1">{{ $info['numero_contrato'] }} ({{ $info['numero_processo'] }})</dd>
                        </div>
                        <div class="pt-3 border-t border-[var(--line)]">
                            <dt class="text-xs font-semibold text-[var(--ink-2)] uppercase">Secretaria</dt>
                            <dd class="text-sm text-[var(--ink)] mt-1">{{ $info['secretaria'] }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if($fiscalizacao->relatorio_fotografico)
                <div class="card !rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-[var(--line)] bg-[var(--card-2)]">
                        <h3 class="text-lg font-bold text-[var(--ink)]">Relatório Fotográfico</h3>
                    </div>
                    <div class="p-6">
                        <a href="{{ asset($fiscalizacao->relatorio_fotografico) }}" target="_blank">
                            <img src="{{ asset($fiscalizacao->relatorio_fotografico) }}" alt="Relatório Fotográfico"
                                 class="w-full rounded-xl border border-[var(--line)] hover:opacity-90 transition-opacity">
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <div class="lg:col-span-2">
            <div class="card !rounded-2xl overflow-hidden min-h-full">
                <div class="px-6 py-4 border-b border-[var(--line)] bg-[var(--card-2)]">
                    <h3 class="text-lg font-bold text-[var(--ink)]">Avaliação do cumprimento do contrato</h3>
                </div>

                <div class="p-8 space-y-8">
                    {{-- CHECKLIST DE VERIFICAÇÃO (Compras e Serviços) --}}
                    @if($ocorrenciaEstruturada && !empty($fiscalizacao->checklist_fiscalizacao))
                        <div class="relative pl-8">
                            <div class="absolute left-0 top-1 text-[var(--mint)]">
                                <i class="fas fa-clipboard-check text-lg"></i>
                            </div>
                            <h4 class="text-xs font-bold text-[var(--ink-2)] uppercase tracking-widest">Checklist de Verificação</h4>
                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 bg-[var(--card-2)] p-4 rounded-xl border border-[var(--line)]">
                                @foreach(\App\Models\Fiscalizacao::CHECKLIST_ITENS as $chave => $rotulo)
                                    <div class="flex items-center gap-2 text-sm">
                                        @if(data_get($fiscalizacao->checklist_fiscalizacao, $chave))
                                            <i class="fas fa-check-circle text-[var(--mint)]"></i>
                                            <span class="text-[var(--ink)]">{{ $rotulo }}</span>
                                        @else
                                            <i class="fas fa-times-circle text-[var(--ink-2)]/30"></i>
                                            <span class="text-[var(--ink-2)]">{{ $rotulo }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @php
                        $campos = [
                            ['icon' => 'fa-microscope', 'label' => 'Metodologia Aplicada', 'value' => $fiscalizacao->metodologia_fiscalizacao, 'show' => $tipo === 'obras'],
                            ['icon' => 'fa-box', 'label' => $labelExecucao, 'value' => $fiscalizacao->execucao_objeto, 'show' => true],
                            ['icon' => 'fa-star', 'label' => $labelQualidade, 'value' => $fiscalizacao->qualidade_entregas, 'show' => true],
                            ['icon' => 'fa-clock', 'label' => 'A empresa cumpriu os prazos de entrega ou de execução previstos no contrato?', 'value' => $fiscalizacao->pontualidade_prazos, 'show' => true],
                            ['icon' => 'fa-file-invoice-dollar', 'label' => 'A empresa está com a regularidade fiscal e trabalhista em dia?', 'value' => $fiscalizacao->regularidade_fiscal_trabalhista, 'show' => ! $ocorrenciaEstruturada],
                            ['icon' => 'fa-comments', 'label' => 'Comunicação e Atendimento', 'value' => $fiscalizacao->comunicacao_atendimento, 'show' => $tipo === 'obras'],
                            ['icon' => 'fa-exclamation-triangle', 'label' => 'Irregularidades Observadas', 'value' => $fiscalizacao->irregularidade_observada, 'show' => ! $ocorrenciaEstruturada],
                            ['icon' => 'fa-lightbulb', 'label' => 'Recomendações ao Gestor', 'value' => $fiscalizacao->recomendacoes_gestor, 'show' => true],
                            ['icon' => 'fa-building', 'label' => 'Recomendações à Empresa', 'value' => $fiscalizacao->recomendacoes_empresa, 'show' => true],
                        ];
                    @endphp

                    {{-- OCORRÊNCIAS (Compras e Serviços) --}}
                    @if($ocorrenciaEstruturada)
                        <div class="relative pl-8">
                            <div class="absolute left-0 top-1 text-[var(--mint)]">
                                <i class="fas fa-exclamation-triangle text-lg"></i>
                            </div>
                            <h4 class="text-xs font-bold text-[var(--ink-2)]">Houve alguma ocorrência ou problema durante a execução do contrato neste período?</h4>
                            <div class="mt-2 space-y-3">
                                @if($fiscalizacao->houve_ocorrencia)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold text-[var(--warn-2)] bg-[var(--warn-bg)] border border-[var(--warn-border)] rounded-full">
                                        <i class="fas fa-exclamation-circle"></i> Houve ocorrências
                                    </span>
                                    <div class="text-sm text-[var(--ink)] leading-relaxed bg-[var(--card-2)] p-4 rounded-xl border border-[var(--line)]">
                                        {!! nl2br(e($fiscalizacao->irregularidade_observada ?: 'Não informado.')) !!}
                                    </div>
                                    @if($fiscalizacao->providencias_adotadas)
                                        <h4 class="text-xs font-bold text-[var(--ink-2)] uppercase tracking-widest">Providências adotadas</h4>
                                        <div class="text-sm text-[var(--ink)] leading-relaxed bg-[var(--card-2)] p-4 rounded-xl border border-[var(--line)]">
                                            {!! nl2br(e($fiscalizacao->providencias_adotadas)) !!}
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold text-[var(--mint)] bg-[var(--card-3)] border border-[var(--line-3)] rounded-full">
                                        <i class="fas fa-check-circle"></i> Não houve problemas ou ocorrências durante este período
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- REGULARIDADE FISCAL/TRABALHISTA — SIM/NÃO (Compras e Serviços) --}}
                        <div class="relative pl-8">
                            <div class="absolute left-0 top-1 text-[var(--mint)]">
                                <i class="fas fa-file-invoice-dollar text-lg"></i>
                            </div>
                            <h4 class="text-xs font-bold text-[var(--ink-2)]">A empresa está com a regularidade fiscal e trabalhista em dia?</h4>
                            <div class="mt-2">
                                @if($fiscalizacao->regularidade_fiscal_trabalhista === 'Sim')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold text-[var(--mint)] bg-[var(--card-3)] border border-[var(--line-3)] rounded-full">
                                        <i class="fas fa-check-circle"></i> Sim
                                    </span>
                                @elseif($fiscalizacao->regularidade_fiscal_trabalhista === 'Não')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold text-[var(--bad-strong)] bg-[var(--bad-bg)] border border-[var(--bad-border)] rounded-full">
                                        <i class="fas fa-times-circle"></i> Não
                                    </span>
                                @else
                                    <span class="text-sm text-[var(--ink-2)]">Não informado.</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    @foreach($campos as $campo)
                        @if($campo['show'] && $campo['value'])
                            <div class="relative pl-8">
                                <div class="absolute left-0 top-1 text-[var(--mint)]">
                                    <i class="fas {{ $campo['icon'] }} text-lg"></i>
                                </div>
                                <h4 class="text-xs font-bold text-[var(--ink-2)] uppercase tracking-widest">{{ $campo['label'] }}</h4>
                                <div class="mt-2 text-sm text-[var(--ink)] leading-relaxed bg-[var(--card-2)] p-4 rounded-xl border border-[var(--line)]">
                                    {!! nl2br(e($campo['value'])) !!}
                                </div>
                            </div>
                        @endif
                    @endforeach

                    {{-- CONCLUSÃO FINAL --}}
                    <div class="mt-12 p-6 rounded-2xl border-2 {{ $fiscalizacao->conclusao_badge_class }} border-current bg-[var(--bg)]">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full bg-[var(--card)] border border-[var(--line)] shadow-sm text-xl font-bold text-[var(--mint)]">
                                {{ $fiscalizacao->conclusao_fiscal?->value }}
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-[var(--ink)]">Qual é a sua conclusão sobre o cumprimento do contrato neste período?</h4>
                                <p class="mt-1 text-sm leading-relaxed font-medium">{{ $fiscalizacao->conclusao_texto }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- ASSINANTES DO RELATÓRIO (assinatura física — sem assinatura eletrônica) --}}
    {{-- ============================================================== --}}
    @php
        $unidadesAssinantes = $fiscalizacao->prefeitura?->unidades->map(fn($u) => [
            'id' => $u->id,
            'nome' => $u->nome,
            'servidor_responsavel' => $u->servidor_responsavel,
        ])->values() ?? collect();
    @endphp
    <div class="mt-6 card !rounded-2xl overflow-hidden"
         x-data="assinantesFiscalizacao({
            unidades: {{ $unidadesAssinantes->toJson() }},
            fiscais: {{ ($fiscais ?? collect())->toJson() }},
            iniciais: {{ json_encode($fiscalizacao->assinantes ?? []) }},
            saveUrl: '{{ route('admin.fiscalizacoes.assinantes', $fiscalizacao->id) }}'
         })">
        <div class="px-6 py-4 border-b border-[var(--line)] bg-[var(--card-2)] flex items-center justify-between">
            <h3 class="text-lg font-bold text-[var(--ink)]">
                <i class="fas fa-file-signature text-[var(--mint)] mr-2"></i>Quem deve assinar o relatório?
            </h3>
            <span class="text-xs font-semibold text-[var(--ink-2)]" x-show="assinantes.length > 0">
                <span x-text="assinantes.length"></span> assinante(s)
            </span>
        </div>

        <div class="p-6 space-y-5">
            <p class="text-sm text-[var(--ink-2)]">
                Informe abaixo os servidores que deverão assinar o relatório impresso. Os nomes aparecerão no rodapé do relatório e a assinatura será feita fisicamente.
            </p>

            {{-- Formulário de adição --}}
            <div class="flex flex-col gap-3 p-4 bg-[var(--card-2)] border border-[var(--line)] rounded-xl sm:flex-row sm:items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block mb-1 text-xs font-semibold text-[var(--ink-2)]">Selecione a secretaria ou o fiscal responsável</label>
                    <select x-model="selecionado" @change="aoSelecionar()"
                            class="block w-full px-3 py-2 text-sm bg-[var(--bg)] text-[var(--ink)] border border-[var(--line)] rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)]">
                        <option value="" class="bg-[var(--card)] text-[var(--ink-2)]">Selecione (ou preencha manualmente)</option>
                        <optgroup label="Secretarias" x-show="unidades.length" class="bg-[var(--card)] text-[var(--ink)]">
                            <template x-for="u in unidades" :key="'u'+u.id">
                                <option :value="'unidade:'+u.id" x-text="u.nome" class="bg-[var(--card)] text-[var(--ink)]"></option>
                            </template>
                        </optgroup>
                        <optgroup label="Fiscais / Usuários" x-show="fiscais.length" class="bg-[var(--card)] text-[var(--ink)]">
                            <template x-for="f in fiscais" :key="'f'+f.id">
                                <option :value="'fiscal:'+f.id" x-text="f.nome + (f.unidade ? ' — ' + f.unidade : '')" class="bg-[var(--card)] text-[var(--ink)]"></option>
                            </template>
                        </optgroup>
                    </select>
                </div>
                <div class="flex-1 min-w-[160px]">
                    <label class="block mb-1 text-xs font-semibold text-[var(--ink-2)]">Informe o nome completo do servidor <span class="text-red-400">*</span></label>
                    <input type="text" x-model="formNome" placeholder="Nome completo"
                           class="block w-full px-3 py-2 text-sm text-[var(--ink)] bg-[var(--bg)] border border-[var(--line)] rounded-xl shadow-sm focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] outline-none">
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="block mb-1 text-xs font-semibold text-[var(--ink-2)]">Informe o cargo ou a função do servidor</label>
                    <input type="text" x-model="formCargo" placeholder="Ex: Fiscal de Contrato"
                           class="block w-full px-3 py-2 text-sm text-[var(--ink)] bg-[var(--bg)] border border-[var(--line)] rounded-xl shadow-sm focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] outline-none">
                </div>
                <div>
                    <button type="button" @click="adicionar()" :disabled="!formNome.trim()"
                            class="flex items-center justify-center w-full px-4 py-2 text-sm font-bold text-[var(--on-mint)] transition-all bg-[var(--mint)] rounded-xl shadow disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[var(--mint-h)]">
                        + Adicionar
                    </button>
                </div>
            </div>

            {{-- Lista de selecionados --}}
            <div class="border border-[var(--line)] rounded-xl overflow-hidden bg-[var(--bg)]">
                <div class="px-4 py-2.5 bg-[var(--card-3)] border-b border-[var(--line-3)]">
                    <span class="text-xs font-bold text-[var(--mint)] uppercase tracking-wide">Assinantes Selecionados</span>
                </div>
                <template x-if="assinantes.length === 0">
                    <div class="p-6 text-sm text-center text-[var(--ink-2)]">Nenhum assinante adicionado.</div>
                </template>
                <ul class="divide-y divide-[var(--line)]">
                    <template x-for="(a, idx) in assinantes" :key="idx">
                        <li class="flex items-center justify-between px-4 py-3 hover:bg-[var(--hover-bg)] transition-colors">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[var(--ink)] truncate" x-text="a.nome"></p>
                                <p class="text-xs text-[var(--ink-2)] truncate"
                                   x-text="[a.cargo, a.unidade].filter(Boolean).join(' — ')"></p>
                            </div>
                            <button type="button" @click="remover(idx)"
                                    class="px-2.5 py-1.5 text-xs font-semibold text-[var(--bad-strong)] hover:bg-[var(--bad-bg)] rounded-lg transition-colors">
                                Remover
                            </button>
                        </li>
                    </template>
                </ul>
            </div>

            {{-- Form de salvamento (hidden inputs gerados a partir do estado) --}}
            <form method="POST" :action="saveUrl" @submit="salvando = true">
                @csrf
                <template x-for="(a, idx) in assinantes" :key="'h'+idx">
                    <span>
                        <input type="hidden" :name="`assinantes[${idx}][nome]`" :value="a.nome">
                        <input type="hidden" :name="`assinantes[${idx}][cargo]`" :value="a.cargo">
                        <input type="hidden" :name="`assinantes[${idx}][unidade]`" :value="a.unidade">
                    </span>
                </template>
                <div class="flex justify-end">
                    <button type="submit" :disabled="salvando"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] hover:scale-105 transition-all disabled:opacity-60 shadow-sm">
                        <i class="fas fa-save"></i> Salvar Assinantes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- RELATÓRIO FOTOGRÁFICO (múltiplas imagens) --}}
    {{-- ============================================================== --}}
    <div class="mt-6 card !rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-[var(--line)] bg-[var(--card-2)] flex items-center justify-between">
            <h3 class="text-lg font-bold text-[var(--ink)]">
                <i class="fas fa-camera text-[var(--mint)] mr-2"></i>Relatório Fotográfico
            </h3>
            <span class="text-xs font-semibold text-[var(--ink-2)]">{{ $fiscalizacao->fotos->count() }} imagem(ns)</span>
        </div>

        <div class="p-6 space-y-6">
            <p class="text-sm text-[var(--ink-2)]">
                Adicione imagens da fiscalização. Todas serão anexadas automaticamente ao final do relatório impresso, na seção “Relatório Fotográfico”.
            </p>

            {{-- Upload --}}
            <form method="POST" action="{{ route('admin.fiscalizacoes.fotos.upload', $fiscalizacao->id) }}"
                  enctype="multipart/form-data"
                  class="flex flex-col gap-3 p-4 bg-[var(--card-2)] border border-[var(--line)] rounded-xl sm:flex-row sm:items-center">
                @csrf
                <input type="file" name="fotos[]" multiple accept="image/jpeg,image/png,image/webp" required
                       class="flex-1 text-sm text-[var(--ink-2)] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-[var(--mint)] file:text-[var(--on-mint)] hover:file:bg-[var(--mint-h)] cursor-pointer">
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] hover:scale-105 transition-all shadow-sm">
                    <i class="fas fa-upload"></i> Enviar Fotos
                </button>
            </form>

            {{-- Galeria --}}
            @if($fiscalizacao->fotos->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($fiscalizacao->fotos as $foto)
                        <div class="relative group border border-[var(--line)] rounded-xl overflow-hidden">
                            <a href="{{ asset($foto->caminho) }}" target="_blank">
                                <img src="{{ asset($foto->caminho) }}" alt="{{ $foto->legenda ?? 'Foto da fiscalização' }}"
                                     class="w-full h-32 object-cover group-hover:opacity-90 transition-opacity">
                            </a>
                            <form method="POST" action="{{ route('admin.fiscalizacoes.fotos.delete', [$fiscalizacao->id, $foto->id]) }}"
                                  onsubmit="return confirm('Remover esta foto?');"
                                  class="absolute top-1.5 right-1.5">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-7 h-7 flex items-center justify-center rounded-full bg-red-600 text-white text-xs shadow hover:bg-red-700 opacity-0 group-hover:opacity-100 transition-opacity"
                                        title="Remover foto">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-sm text-center text-[var(--ink-2)] border border-dashed border-[var(--line)] rounded-xl bg-[var(--bg)]">
                    Nenhuma foto adicionada ainda.
                </div>
            @endif
        </div>
    </div>
</div>

@once
<script>
    function assinantesFiscalizacao({ unidades, fiscais, iniciais, saveUrl }) {
        return {
            unidades: unidades || [],
            fiscais: fiscais || [],
            assinantes: (iniciais || []).map(a => ({
                nome: a.nome ?? '',
                cargo: a.cargo ?? '',
                unidade: a.unidade ?? '',
            })),
            saveUrl,
            salvando: false,
            selecionado: '',   // "unidade:<id>" ou "fiscal:<id>"
            formNome: '',
            formCargo: '',
            formUnidade: '',

            aoSelecionar() {
                if (!this.selecionado) { this.formUnidade = ''; return; }
                const [tipo, id] = this.selecionado.split(':');

                if (tipo === 'unidade') {
                    const u = this.unidades.find(x => x.id == id);
                    if (u) {
                        this.formNome = u.servidor_responsavel || this.formNome;
                        this.formUnidade = u.nome || '';
                    }
                } else if (tipo === 'fiscal') {
                    const f = this.fiscais.find(x => x.id == id);
                    if (f) {
                        this.formNome = f.nome || this.formNome;
                        this.formUnidade = f.unidade || '';
                    }
                }
            },

            adicionar() {
                if (!this.formNome.trim()) return;
                this.assinantes.push({
                    nome: this.formNome.trim(),
                    cargo: this.formCargo.trim(),
                    unidade: this.formUnidade || '',
                });
                this.formNome = '';
                this.formCargo = '';
                this.formUnidade = '';
                this.selecionado = '';
            },

            remover(idx) {
                this.assinantes.splice(idx, 1);
            },
        };
    }
</script>
@endonce
@endsection