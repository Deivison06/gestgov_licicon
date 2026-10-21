{{--
    Cabeçalho unificado do Processo (identidade + barra de ações + stepper de etapas).
    Reutilizado nas telas de Inicialização e Finalização para manter consistência com a
    listagem de Processos. Não altera nenhuma regra de negócio — apenas apresentação.

    Variáveis esperadas:
      - $processo    : instância do Processo
      - $etapaAtual  : 'iniciar' | 'finalizar' (define o passo destacado no stepper)
--}}
@php
    $status = $processo->status instanceof \App\Enums\ProcessoStatusEnum
        ? $processo->status
        : (\App\Enums\ProcessoStatusEnum::tryFrom($processo->status) ?? \App\Enums\ProcessoStatusEnum::EM_ANDAMENTO);
    $statusValue = $status->value;

    $modalidade = $processo->modalidade instanceof \App\Enums\ModalidadeEnum
        ? $processo->modalidade
        : \App\Enums\ModalidadeEnum::tryFrom($processo->modalidade);
    $modalidadeValue = $modalidade?->value;

    $modalidadeBadgeClass = match ($modalidadeValue) {
        'dispensa'        => 'bg-purple-500/15 text-purple-300 border border-purple-500/30',
        'inexigibilidade' => 'bg-pink-500/15 text-pink-300 border border-pink-500/30',
        'pregão'          => 'bg-blue-500/15 text-blue-300 border border-blue-500/30',
        'concorrência'    => 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30',
        default           => 'bg-[var(--hover-bg)] text-[var(--ink-2)] border border-[var(--line)]',
    };
    $modalidadeIcon = match ($modalidadeValue) {
        'dispensa'        => 'fa-file-signature',
        'inexigibilidade' => 'fa-ban',
        'pregão'          => 'fa-gavel',
        'concorrência'    => 'fa-balance-scale',
        default           => 'fa-question-circle',
    };

    // ---- Etapas (mesma lógica derivada da listagem — sem alterar regra de negócio) ----
    $ehInexigibilidade = $modalidadeValue === \App\Enums\ModalidadeEnum::INEXIGIBILIDADE->value;
    $temContrato       = $processo->contrato !== null;
    $temFinalizacao    = $processo->finalizacao !== null || $statusValue === 'FINALIZADO';
    $mostraContrato    = !$ehInexigibilidade
        && !($processo->modalidade == 4 && optional($processo->detalhe)->tipo_srp === 'nao');

    $etapas = [[
        'key' => 'iniciar', 'label' => 'Inicialização', 'icon' => 'fa-play',
        'rota' => route('admin.processos.iniciar', $processo->id),
    ]];
    if (!$ehInexigibilidade) {
        $etapas[] = [
            'key' => 'finalizar', 'label' => 'Finalização', 'icon' => 'fa-check',
            'rota' => route('admin.processos.finalizacao.finalizar', $processo->id),
        ];
    }
    if ($mostraContrato) {
        $etapas[] = [
            'key' => 'contrato', 'label' => 'Contrato', 'icon' => 'fa-file-contract',
            'rota' => route('admin.processos.contrato.index', $processo->id),
        ];
    }

    $estadoEtapa = function (string $key) use ($etapaAtual, $temFinalizacao, $temContrato) {
        if ($key === $etapaAtual) return 'atual';
        return match ($key) {
            'iniciar'   => ($temFinalizacao || $temContrato) ? 'concluida' : 'pendente',
            'finalizar' => ($temFinalizacao || $temContrato) ? 'concluida' : 'pendente',
            'contrato'  => $temContrato ? 'concluida' : 'pendente',
            default     => 'pendente',
        };
    };

    // ---- Ações raras (somente na Inicialização, onde os handlers JS existem) ----
    $temCancelamentoGerado = false;
    $cancelamentoDocumento = null;
    if ($etapaAtual === 'iniciar' && method_exists($processo, 'cancelamentos')) {
        $cancelamentoDocumento = $processo->cancelamentos()->orderByDesc('gerado_em')->first();
        $temCancelamentoGerado = $cancelamentoDocumento !== null;
    }
    $isCancelado = $statusValue === 'CANCELADO';
    $temKebab = $etapaAtual === 'iniciar';
@endphp

<div class="mb-6 overflow-hidden card !rounded-2xl">
    {{-- Barra superior: navegação + identidade + status + ações --}}
    <div class="card-h w-full flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-start gap-3 min-w-0">
            <a href="{{ route('admin.processos.index') }}"
               class="inline-flex items-center justify-center w-9 h-9 text-[var(--ink)] bg-[var(--card)] border border-[var(--line)] rounded-xl hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] transition-colors shadow-sm shrink-0"
               title="Voltar para a lista de processos">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="min-w-0">
                <div class="flex items-center flex-wrap gap-2">
                    <h2 class="font-mono text-lg font-bold text-[var(--ink)] truncate">{{ $processo->numero_processo }}</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $modalidadeBadgeClass }}">
                        <i class="mr-1 fas {{ $modalidadeIcon }}"></i>{{ $modalidade?->getDisplayName() ?? 'Não definida' }}
                    </span>
                </div>
                @if($processo->user)
                    <p class="mt-1 text-xs text-[var(--ink-2)]">
                        <i class="mr-1 fas fa-user text-[var(--mint)]"></i>Responsável: <strong class="font-medium text-[var(--ink)]">{{ $processo->user->name }}</strong>
                    </p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 flex-shrink-0">
            {{-- Status atual (badge) --}}
            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full
                @if($statusValue === 'EM_ANDAMENTO') bg-blue-500/15 text-blue-300 border border-blue-500/30
                @elseif($statusValue === 'FINALIZADO') bg-emerald-500/15 text-emerald-300 border border-emerald-500/30
                @elseif($statusValue === 'CANCELADO') bg-red-500/15 text-red-300 border border-red-500/30
                @elseif($statusValue === 'REPUBLICADO') bg-purple-500/15 text-purple-300 border border-purple-500/30
                @elseif($statusValue === 'ADIADO') bg-amber-500/15 text-amber-300 border border-amber-500/30
                @else bg-[var(--hover-bg)] text-[var(--ink-2)] border border-[var(--line)] @endif">
                <i class="mr-1.5 fas
                    @if($statusValue === 'EM_ANDAMENTO') fa-spinner fa-spin
                    @elseif($statusValue === 'FINALIZADO') fa-check-circle
                    @elseif($statusValue === 'CANCELADO') fa-times-circle
                    @elseif($statusValue === 'REPUBLICADO') fa-redo
                    @elseif($statusValue === 'ADIADO') fa-clock
                    @else fa-circle @endif"></i>
                {{ $status->label() }}
            </span>

            @if($processo->foi_republicado)
                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-purple-300 bg-purple-500/15 border border-purple-500/30 rounded-full"
                      title="Este processo é fruto de uma republicação">
                    <i class="mr-1.5 fas fa-redo"></i>Republicado
                </span>
            @endif

            {{-- Menu de ações raras (kebab) — só na Inicialização --}}
            @if($temKebab)
                <div class="relative inline-block text-left" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" @click="open = !open"
                            class="inline-flex items-center justify-center w-9 h-9 text-[var(--ink-2)] transition-colors bg-[var(--card)] border border-[var(--line)] rounded-xl hover:bg-[var(--hover-bg)] hover:text-[var(--ink)] focus:outline-none focus:ring-2 focus:ring-[var(--mint)]"
                            title="Mais ações">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div x-show="open" x-cloak x-transition
                         class="absolute right-0 z-30 mt-1 origin-top-right bg-[var(--card)] border border-[var(--line)] rounded-xl shadow-2xl w-60 py-1">
                        <button type="button" @click="open = false; abrirModalRepublicarEdital({{ $processo->id }})"
                                class="flex items-center w-full px-4 py-2.5 text-sm text-left text-[var(--ink)] hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] transition-colors">
                            <i class="w-4 mr-2 text-purple-400 fas fa-redo"></i> Republicação de Edital
                        </button>
                        @if (!$isCancelado && !$temCancelamentoGerado)
                            <button type="button" @click="open = false; gerarMinutaCancelamento({{ $processo->id }})"
                                    class="flex items-center w-full px-4 py-2.5 text-sm text-left text-[var(--ink)] hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] transition-colors">
                                <i class="w-4 mr-2 text-amber-400 fas fa-triangle-exclamation"></i> Gerar Minuta de Cancelamento
                            </button>
                        @endif
                        @if ($temCancelamentoGerado)
                            <div class="my-1 border-t border-[var(--line)]"></div>
                            <a href="{{ route('admin.processo.documento.dowload', ['processo' => $processo->id, 'tipo' => 'minuta_cancelamento']) }}"
                               download
                               class="flex items-center w-full px-4 py-2.5 text-sm text-left text-red-400 hover:bg-red-950/40 transition-colors">
                                <i class="w-4 mr-2 fas fa-download"></i> Baixar Minuta de Cancelamento
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Objeto em destaque + metadados --}}
    <div class="px-6 py-5">
        <p class="text-xs font-semibold tracking-wider text-[var(--ink-2)] uppercase">Objeto</p>
        @php
            $objeto = html_entity_decode(
                strip_tags($processo->objeto),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );
        @endphp

        <p class="mt-1 text-sm leading-relaxed text-[var(--ink)]">
            {{ $objeto ?: '—' }}
        </p>

        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 mt-5 sm:grid-cols-4">
            <div>
                <dt class="text-xs font-semibold tracking-wider text-[var(--ink-2)] uppercase">Prefeitura</dt>
                <dd class="mt-0.5 text-sm font-medium text-[var(--ink)]">{{ $processo->prefeitura->nome }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold tracking-wider text-[var(--ink-2)] uppercase">Nº Procedimento</dt>
                <dd class="mt-0.5 font-mono text-sm font-medium text-[var(--ink)]">{{ $processo->numero_procedimento ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold tracking-wider text-[var(--ink-2)] uppercase">Tipo Contratação</dt>
                <dd class="mt-0.5 text-sm font-medium text-[var(--ink)]">{{ $processo->tipo_contratacao_nome }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold tracking-wider text-[var(--ink-2)] uppercase">Tipo Procedimento</dt>
                <dd class="mt-0.5 text-sm font-medium text-[var(--ink)]">{{ $processo->tipo_procedimento_nome }}</dd>
            </div>
        </dl>
    </div>

    {{-- Stepper de etapas --}}
    <div class="px-6 py-4 border-t border-[var(--line)] bg-[var(--card-2)]">
        <div class="flex items-center">
            @foreach($etapas as $i => $etapa)
                @php $estado = $estadoEtapa($etapa['key']); @endphp
                @if($i > 0)
                    <div class="flex-1 h-0.5 mx-3 {{ $estado === 'pendente' ? 'bg-[var(--line)]' : 'bg-[var(--mint)]/50' }}"></div>
                @endif
                <a href="{{ $etapa['rota'] }}" title="{{ $etapa['label'] }}" class="inline-flex items-center gap-2 group">
                    <span class="flex items-center justify-center rounded-xl w-8 h-8 text-xs transition
                        @if($estado === 'concluida') bg-[var(--card-3)] text-[var(--mint)] border border-[var(--line)]
                        @elseif($estado === 'atual') bg-[var(--mint)] text-[var(--on-mint)] font-bold shadow-sm
                        @else bg-[var(--bg)] text-[var(--ink-2)] border border-[var(--line)] group-hover:border-[var(--mint)]/50 @endif">
                        <i class="fas {{ $estado === 'concluida' ? 'fa-check' : $etapa['icon'] }}"></i>
                    </span>
                    <span class="text-xs leading-tight hidden sm:inline
                        @if($estado === 'atual') text-[var(--mint)] font-bold
                        @elseif($estado === 'concluida') text-[var(--mint)] font-medium
                        @else text-[var(--ink-2)] group-hover:text-[var(--ink)] @endif">{{ $etapa['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>
