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
        'dispensa'        => 'bg-purple-100 text-purple-800 border border-purple-200',
        'inexigibilidade' => 'bg-pink-100 text-pink-800 border border-pink-200',
        'pregão'          => 'bg-blue-100 text-blue-800 border border-blue-200',
        'concorrência'    => 'bg-green-100 text-green-800 border border-green-200',
        default           => 'bg-gray-100 text-gray-800 border border-gray-200',
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

<div class="mb-6 overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
    {{-- Barra superior: navegação + identidade + status + ações --}}
    <div class="flex flex-col gap-4 px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-start gap-3 min-w-0">
            <a href="{{ route('admin.processos.index') }}"
               class="inline-flex items-center justify-center flex-shrink-0 w-9 h-9 text-gray-500 transition-colors bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#009496]"
               title="Voltar para a lista de processos">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="min-w-0">
                <div class="flex items-center flex-wrap gap-2">
                    <h2 class="font-mono text-lg font-bold text-gray-900 truncate">{{ $processo->numero_processo }}</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $modalidadeBadgeClass }}">
                        <i class="mr-1 fas {{ $modalidadeIcon }}"></i>{{ $modalidade?->getDisplayName() ?? 'Não definida' }}
                    </span>
                </div>
                @if($processo->user)
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="mr-1 fas fa-user"></i>Responsável: <strong class="font-medium text-gray-700">{{ $processo->user->name }}</strong>
                    </p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 flex-shrink-0">
            {{-- Status atual (badge) --}}
            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full
                @if($statusValue === 'EM_ANDAMENTO') bg-blue-100 text-blue-800
                @elseif($statusValue === 'FINALIZADO') bg-green-100 text-green-800
                @elseif($statusValue === 'CANCELADO') bg-red-100 text-red-800
                @elseif($statusValue === 'REPUBLICADO') bg-purple-100 text-purple-800
                @elseif($statusValue === 'ADIADO') bg-orange-100 text-orange-800
                @else bg-gray-100 text-gray-800 @endif">
                <i class="mr-1.5 fas
                    @if($statusValue === 'EM_ANDAMENTO') fa-spinner
                    @elseif($statusValue === 'FINALIZADO') fa-check-circle
                    @elseif($statusValue === 'CANCELADO') fa-times-circle
                    @elseif($statusValue === 'REPUBLICADO') fa-redo
                    @elseif($statusValue === 'ADIADO') fa-clock
                    @else fa-circle @endif"></i>
                {{ $status->label() }}
            </span>

            {{-- Menu de ações raras (kebab) — só na Inicialização --}}
            @if($temKebab)
                <div class="relative inline-block text-left" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" @click="open = !open"
                            class="inline-flex items-center justify-center w-9 h-9 text-gray-500 transition-colors bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300"
                            title="Mais ações">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div x-show="open" x-cloak x-transition
                         class="absolute right-0 z-30 mt-1 origin-top-right bg-white border border-gray-200 rounded-lg shadow-lg w-60">
                        <button type="button" @click="open = false; abrirModalRepublicarEdital({{ $processo->id }})"
                                class="flex items-center w-full px-4 py-2.5 text-sm text-left text-gray-700 hover:bg-gray-50">
                            <i class="w-4 mr-2 text-purple-500 fas fa-redo"></i> Republicação de Edital
                        </button>
                        @if (!$isCancelado && !$temCancelamentoGerado)
                            <button type="button" @click="open = false; gerarMinutaCancelamento({{ $processo->id }})"
                                    class="flex items-center w-full px-4 py-2.5 text-sm text-left text-gray-700 hover:bg-gray-50">
                                <i class="w-4 mr-2 text-orange-500 fas fa-triangle-exclamation"></i> Gerar Minuta de Cancelamento
                            </button>
                        @endif
                        @if ($temCancelamentoGerado)
                            <div class="my-1 border-t border-gray-100"></div>
                            <a href="{{ route('admin.processo.documento.dowload', ['processo' => $processo->id, 'tipo' => 'minuta_cancelamento']) }}"
                               download
                               class="flex items-center w-full px-4 py-2.5 text-sm text-left text-red-600 hover:bg-red-50">
                                <i class="w-4 mr-2 fas fa-download"></i> Baixar Minuta de Cancelamento
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Objeto em destaque + metadados --}}
    <div class="px-6 py-4">
        <p class="text-xs font-medium tracking-wide text-gray-400 uppercase">Objeto</p>
        @php
            $objeto = html_entity_decode(
                strip_tags($processo->objeto),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );
        @endphp

        <p class="mt-1 text-sm leading-relaxed text-gray-800">
            {{ $objeto ?: '—' }}
        </p>

        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 mt-5 sm:grid-cols-4">
            <div>
                <dt class="text-xs font-medium tracking-wide text-gray-400 uppercase">Prefeitura</dt>
                <dd class="mt-0.5 text-sm text-gray-800">{{ $processo->prefeitura->nome }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium tracking-wide text-gray-400 uppercase">Nº Procedimento</dt>
                <dd class="mt-0.5 font-mono text-sm text-gray-800">{{ $processo->numero_procedimento ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium tracking-wide text-gray-400 uppercase">Tipo Contratação</dt>
                <dd class="mt-0.5 text-sm text-gray-800">{{ $processo->tipo_contratacao_nome }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium tracking-wide text-gray-400 uppercase">Tipo Procedimento</dt>
                <dd class="mt-0.5 text-sm text-gray-800">{{ $processo->tipo_procedimento_nome }}</dd>
            </div>
        </dl>
    </div>

    {{-- Stepper de etapas --}}
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60">
        <div class="flex items-center">
            @foreach($etapas as $i => $etapa)
                @php $estado = $estadoEtapa($etapa['key']); @endphp
                @if($i > 0)
                    <div class="flex-1 h-px mx-2 {{ $estado === 'pendente' ? 'bg-gray-200' : 'bg-green-300' }}"></div>
                @endif
                <a href="{{ $etapa['rota'] }}" title="{{ $etapa['label'] }}" class="inline-flex items-center gap-2 group">
                    <span class="flex items-center justify-center rounded-full w-8 h-8 text-xs transition
                        @if($estado === 'concluida') bg-green-100 text-green-700
                        @elseif($estado === 'atual') bg-[#062F43] text-white ring-2 ring-[#062F43]/20
                        @else bg-gray-100 text-gray-400 group-hover:bg-gray-200 @endif">
                        <i class="fas {{ $estado === 'concluida' ? 'fa-check' : $etapa['icon'] }}"></i>
                    </span>
                    <span class="text-xs font-medium leading-tight hidden sm:inline
                        @if($estado === 'atual') text-[#062F43]
                        @elseif($estado === 'concluida') text-green-700
                        @else text-gray-400 @endif">{{ $etapa['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>
