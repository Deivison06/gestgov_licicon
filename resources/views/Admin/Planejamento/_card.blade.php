{{-- Partial de card. Recebe: $processo, $statusConfig, $exibirStatus (opcional) --}}
@php
    $cfg                = $statusConfig[$processo->planejamento_status];
    $aguardandoResposta = $processo->aguardandoRespostaRecurso();
    $cidade             = $processo->prefeitura->cidade ?? '—';
    $corPref            = $processo->prefeitura->cor ?? '#0f766e';
@endphp

<div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-150 overflow-hidden flex flex-col">

    <div class="h-1.5 "></div>

    <div class="p-3 flex flex-col gap-2.5 flex-1">

        {{-- Prefeitura: destaque com cor da cidade --}}
        <div class="flex items-center gap-2 rounded-lg px-2.5 py-1.5"
            style="background-color: {{ $corPref }};">
            <svg class="w-3.5 h-3.5 shrink-0" style="color: rgba(255,255,255,0.75);"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span class="text-xs font-bold leading-tight truncate" style="color: #fff;" title="{{ $cidade }}">
                {{ $cidade }}
            </span>
        </div>

        {{-- Número + badge de status (mobile) --}}
        <div class="flex items-center justify-between gap-2">
            <a href="{{ route('admin.planejamento.show', $processo) }}"
                class="font-mono text-xs font-semibold text-gray-400 hover:text-teal-600 transition-colors shrink-0">
                #{{ $processo->numero_processo ?? $processo->numero_procedimento ?? '#'.$processo->id }}
            </a>
            @isset($exibirStatus)
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $cfg['cor_badge'] }}">
                    {{ $cfg['label'] }}
                </span>
            @endisset
        </div>

        {{-- Objeto --}}
        @if($processo->objeto)
            <a href="{{ route('admin.planejamento.show', $processo) }}"
                class="text-sm text-gray-700 leading-snug hover:text-indigo-700 transition-colors -mt-0.5"
                title="{{ html_entity_decode(strip_tags($processo->objeto)) }}">
                {{ Str::limit(html_entity_decode(strip_tags($processo->objeto)), 75) }}
            </a>
        @endif

        {{-- Data de criação (somente em elaboração, antes de ter data de sessão) --}}
        @if($processo->planejamento_status === 'em_elaboracao')
            <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5">
                <svg class="w-3.5 h-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-xs text-gray-500">Criado em {{ $processo->created_at->format('d/m/Y') }}</span>
            </div>
        @endif

        {{-- Alerta: recurso aguardando resposta --}}
        @if($aguardandoResposta)
            <div class="flex items-center gap-1.5 bg-red-50 border border-red-200 rounded-lg px-2.5 py-1.5">
                <svg class="w-3.5 h-3.5 shrink-0 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                </svg>
                <span class="text-xs font-semibold text-red-700">Aguardando resposta ao recurso</span>
            </div>
        @endif

        {{-- Prazo do recurso --}}
        @if($processo->planejamento_fim_recurso && $processo->planejamento_status === 'em_recurso' && !$aguardandoResposta)
            <div class="flex items-center gap-1.5 bg-orange-50 border border-orange-200 rounded-lg px-2.5 py-1.5">
                <svg class="w-3.5 h-3.5 shrink-0 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-xs font-semibold text-orange-700">
                    Prazo recurso: {{ $processo->planejamento_fim_recurso->format('d/m/Y') }}
                </span>
            </div>
        @endif

        {{-- Data de abertura da sessão --}}
        @if($processo->planejamento_data_abertura && in_array($processo->planejamento_status, ['aguardando_sessao', 'em_andamento']))
            <div class="flex items-center gap-1.5 bg-blue-50 border border-blue-100 rounded-lg px-2.5 py-1.5">
                <svg class="w-3.5 h-3.5 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-xs font-semibold text-blue-700">
                    Sessão: {{ $processo->planejamento_data_abertura->format('d/m/Y') }}
                </span>
            </div>
        @endif

        {{-- Botões de ação --}}
        <div class="flex flex-col gap-1.5 mt-auto pt-1"
            x-data="{ processoId: {{ $processo->id }} }">

            @if($processo->planejamento_status === 'em_elaboracao')
                <button @click="$dispatch('abrir-modal-agendar', { id: {{ $processo->id }} })"
                    class="w-full inline-flex items-center justify-center gap-1.5 text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-lg px-3 py-2 transition-colors shadow-sm">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Agendar Sessão
                </button>
            @endif

            @if($processo->planejamento_status === 'em_andamento')
                <form method="POST" action="{{ route('admin.planejamento.status.update', $processo) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="planejamento_status" value="concluida">
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-1.5 text-xs font-semibold bg-green-600 hover:bg-green-700 text-white rounded-lg px-3 py-2 transition-colors shadow-sm">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Concluir Sessão
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.planejamento.status.update', $processo) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="planejamento_status" value="em_recurso">
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-1.5 text-xs font-semibold bg-orange-500 hover:bg-orange-600 text-white rounded-lg px-3 py-2 transition-colors shadow-sm">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Iniciar Recurso
                    </button>
                </form>
            @endif

            @if($processo->planejamento_status === 'em_recurso' && $aguardandoResposta)
                <form method="POST" action="{{ route('admin.planejamento.status.update', $processo) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="planejamento_status" value="concluida">
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-1.5 text-xs font-semibold bg-slate-600 hover:bg-slate-700 text-white rounded-lg px-3 py-2 transition-colors shadow-sm">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        Encaminhar para Conclusão
                    </button>
                </form>
            @endif

        </div>

    </div>

    {{-- Rodapé --}}
    <div class="flex items-center justify-between px-3 py-2 bg-gray-50 border-t border-gray-100">
        <a href="{{ route('admin.planejamento.show', $processo) }}#notas"
            class="flex items-center gap-1.5 text-xs text-gray-400 hover:text-teal-600 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
            </svg>
            @if($processo->notas->count() > 0)
                {{ $processo->notas->count() }} {{ $processo->notas->count() === 1 ? 'nota' : 'notas' }}
            @else
                Adicionar nota
            @endif
        </a>
        <a href="{{ route('admin.processos.show', $processo) }}"
            class="text-xs text-gray-400 hover:text-teal-600 font-semibold transition-colors flex items-center gap-1"
            title="Ver processo completo">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            Processo
        </a>
    </div>

</div>
