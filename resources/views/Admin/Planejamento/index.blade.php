@extends('layouts.app')
@section('page-title', 'Planejamento')
@section('page-subtitle', 'Acompanhe o andamento dos processos licitatórios em cada fase')

@section('content')
<div
    x-data="planejamentoPainel()"
    @abrir-modal-agendar.window="modalAgendar = true; processoId = $event.detail.id; dataSessao = $event.detail.dataSessao || ''"
    class="py-6">

    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

        {{-- Cabeçalho --}}
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Painel de Planejamento</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $processos->count() }} {{ $processos->count() === 1 ? 'processo' : 'processos' }}
                    @if(request()->hasAny(['prefeitura_id', 'status', 'data_de', 'data_ate'])) filtrados @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="modalCalendario = true; $nextTick(() => initCalendario())"
                    class="inline-flex items-center gap-2 text-sm font-semibold bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-xl px-4 py-2.5 transition-colors shadow-sm">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Ver Calendário
                </button>
                <a href="{{ route('admin.processos.create') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold bg-teal-700 hover:bg-teal-800 active:bg-teal-900 text-white rounded-xl px-4 py-2.5 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Novo Processo
                </a>
            </div>
        </div>

        @include('Admin.Planejamento._calendario_modal')

        {{-- Flash de sucesso --}}
        @if(session('sucesso'))
            <div x-data="{ show: true }" x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="flex items-center justify-between gap-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    {{ session('sucesso') }}
                </div>
                <button @click="show = false" class="text-green-500 hover:text-green-700 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        {{-- ====================================================
             TABS DE VISÃO
             ==================================================== --}}
        @php
            $paramsVisao = request()->only('prefeitura_id', 'data_de', 'data_ate');
        @endphp
        <div class="flex items-center gap-1 bg-white border border-gray-200 rounded-xl shadow-sm p-1 self-start">
            <a href="{{ route('admin.planejamento.index', array_merge($paramsVisao, ['visao' => 'padrao'])) }}"
                @class([
                    'inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-lg transition-all duration-150',
                    'bg-teal-700 text-white shadow-sm' => $visao === 'padrao',
                    'text-gray-500 hover:text-gray-700 hover:bg-gray-50' => $visao !== 'padrao',
                ])>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                Fluxo Padrão
            </a>
            <a href="{{ route('admin.planejamento.index', array_merge($paramsVisao, ['visao' => 'inexigibilidade'])) }}"
                @class([
                    'inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-lg transition-all duration-150',
                    'bg-violet-700 text-white shadow-sm' => $visao === 'inexigibilidade',
                    'text-gray-500 hover:text-gray-700 hover:bg-gray-50' => $visao !== 'inexigibilidade',
                ])>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Inexigibilidade / Dispensa
            </a>
        </div>

        {{-- ====================================================
             FILTROS
             ==================================================== --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

            {{-- Pills de status --}}
            @php
                $statusAtivo    = request('status');
                $modalidadeAtiva = request('modalidade');
                $paramsBase     = request()->only('prefeitura_id', 'data_de', 'data_ate', 'visao');
                $totalGeral     = collect($colunas)->sum(fn($col) => $col->count());
            @endphp
            @if($visao !== 'inexigibilidade')
            <div class="flex flex-wrap items-center gap-2 px-4 py-3 border-b border-gray-100 bg-gray-50/60">

                <span class="text-xs font-semibold text-gray-400 shrink-0 mr-0.5">Status</span>
                <span class="w-px h-4 bg-gray-200 shrink-0"></span>

                <a href="{{ route('admin.planejamento.index', array_merge($paramsBase, $modalidadeAtiva ? ['modalidade' => $modalidadeAtiva] : [])) }}"
                    @class([
                        'inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full border transition-all duration-150',
                        'bg-gray-800 text-white border-gray-800 shadow-sm' => ! $statusAtivo,
                        'bg-white text-gray-500 border-gray-200 hover:border-gray-300 hover:text-gray-700 hover:bg-white' => $statusAtivo,
                    ])>
                    Todos
                    <span @class([
                        'text-xs px-1.5 py-0.5 rounded-full font-bold',
                        'bg-white/20 text-white' => ! $statusAtivo,
                        'bg-gray-100 text-gray-600' => $statusAtivo,
                    ])>{{ $totalGeral }}</span>
                </a>

                @foreach($statusConfig as $status => $cfg)
                    @php $count = $colunas[$status]->count(); @endphp
                    <a href="{{ route('admin.planejamento.index', array_merge($paramsBase, ['status' => $status], $modalidadeAtiva ? ['modalidade' => $modalidadeAtiva] : [])) }}"
                        @class([
                            'inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full border transition-all duration-150',
                            $cfg['cor_badge'] . ' border-current shadow-sm' => $statusAtivo === $status,
                            'bg-white text-gray-500 border-gray-200 hover:border-gray-300 hover:text-gray-700 hover:bg-white' => $statusAtivo !== $status,
                        ])>
                        {{ $cfg['label'] }}
                        <span @class([
                            'text-xs px-1.5 py-0.5 rounded-full font-bold',
                            'bg-black/10' => $statusAtivo === $status,
                            'bg-gray-100 text-gray-500' => $statusAtivo !== $status,
                        ])>{{ $count }}</span>
                    </a>
                @endforeach
            </div>
            @endif

            {{-- Pills de modalidade --}}
            <div class="flex flex-wrap items-center gap-2 px-4 py-2.5 border-b border-gray-100">
                <span class="text-xs font-semibold text-gray-400 shrink-0 mr-0.5">Modalidade</span>
                <span class="w-px h-4 bg-gray-200 shrink-0"></span>

                @if($visao === 'inexigibilidade')
                    {{-- Visão especial: filtro entre Dispensa e Inexigibilidade --}}
                    @php
                        $modalidadesEspeciais = collect($modalidades)->filter(fn($m) => in_array($m->value, [
                            \App\Enums\ModalidadeEnum::DISPENSA->value,
                            \App\Enums\ModalidadeEnum::INEXIGIBILIDADE->value,
                        ]));
                    @endphp
                    <a href="{{ route('admin.planejamento.index', array_merge($paramsBase, $statusAtivo ? ['status' => $statusAtivo] : [])) }}"
                        @class([
                            'text-xs font-semibold px-3 py-1 rounded-full border transition-all duration-150',
                            'bg-gray-800 text-white border-gray-800 shadow-sm' => ! $modalidadeAtiva,
                            'bg-white text-gray-500 border-gray-200 hover:border-gray-300 hover:text-gray-700' => $modalidadeAtiva,
                        ])>
                        Ambas
                    </a>
                    @foreach($modalidadesEspeciais as $mod)
                        <a href="{{ route('admin.planejamento.index', array_merge($paramsBase, ['modalidade' => $mod->value], $statusAtivo ? ['status' => $statusAtivo] : [])) }}"
                            @class([
                                'text-xs font-semibold px-3 py-1 rounded-full border transition-all duration-150',
                                'bg-violet-700 text-white border-violet-700 shadow-sm' => $modalidadeAtiva == $mod->value,
                                'bg-white text-gray-500 border-gray-200 hover:border-violet-300 hover:text-violet-700' => $modalidadeAtiva != $mod->value,
                            ])>
                            {{ $mod->getDisplayName() }}
                        </a>
                    @endforeach
                @else
                    {{-- Fluxo padrão: apenas CONCORRÊNCIA e PREGÃO ELETRÔNICO --}}
                    @php
                        $modalidadesPadrao = collect($modalidades)->filter(fn($m) => ! in_array($m->value, [
                            \App\Enums\ModalidadeEnum::DISPENSA->value,
                            \App\Enums\ModalidadeEnum::INEXIGIBILIDADE->value,
                        ]));
                    @endphp
                    <a href="{{ route('admin.planejamento.index', array_merge($paramsBase, $statusAtivo ? ['status' => $statusAtivo] : [])) }}"
                        @class([
                            'text-xs font-semibold px-3 py-1 rounded-full border transition-all duration-150',
                            'bg-gray-800 text-white border-gray-800 shadow-sm' => ! $modalidadeAtiva,
                            'bg-white text-gray-500 border-gray-200 hover:border-gray-300 hover:text-gray-700' => $modalidadeAtiva,
                        ])>
                        Ambas
                    </a>
                    @foreach($modalidadesPadrao as $mod)
                        <a href="{{ route('admin.planejamento.index', array_merge($paramsBase, ['modalidade' => $mod->value], $statusAtivo ? ['status' => $statusAtivo] : [])) }}"
                            @class([
                                'text-xs font-semibold px-3 py-1 rounded-full border transition-all duration-150',
                                'bg-teal-700 text-white border-teal-700 shadow-sm' => $modalidadeAtiva == $mod->value,
                                'bg-white text-gray-500 border-gray-200 hover:border-teal-300 hover:text-teal-700' => $modalidadeAtiva != $mod->value,
                            ])>
                            {{ $mod->getDisplayName() }}
                        </a>
                    @endforeach
                @endif
            </div>

            {{-- Formulário de filtros --}}
            <form method="GET" action="{{ route('admin.planejamento.index') }}"
                x-data="{ showPeriodo: {{ request()->hasAny(['data_de', 'data_ate']) ? 'true' : 'false' }} }"
                class="px-4 py-3.5 space-y-3">

                <input type="hidden" name="visao" value="{{ $visao }}">

                <div class="flex flex-wrap items-center gap-2.5">

                    {{-- Prefeitura --}}
                    @if(auth()->user()->prefeitura_id)
                        <input type="hidden" name="prefeitura_id" value="{{ auth()->user()->prefeitura_id }}">
                    @else
                        <div class="flex-1 min-w-[200px]">
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <select id="prefeitura_id" name="prefeitura_id"
                                    class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-200 bg-gray-50 text-sm text-gray-700 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 focus:bg-white transition-colors">
                                    <option value="">Todas as prefeituras</option>
                                    @foreach($prefeituras as $prefeitura)
                                        <option value="{{ $prefeitura->id }}" @selected(request('prefeitura_id') == $prefeitura->id)>
                                            {{ $prefeitura->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    {{-- Botão período --}}
                    <button type="button" @click="showPeriodo = !showPeriodo"
                        :class="showPeriodo ? 'bg-teal-600 border-teal-600 text-white shadow-sm' : 'bg-gray-50 border-gray-200 text-gray-600 hover:border-teal-400 hover:text-teal-700'"
                        class="relative inline-flex items-center gap-2 text-sm font-semibold border rounded-lg px-3 py-2 transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span x-text="showPeriodo ? 'Ocultar' : 'Período'"></span>
                        @if(request('data_de') || request('data_ate'))
                            <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-teal-400 rounded-full border-2 border-white"></span>
                        @endif
                    </button>

                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if(request('modalidade'))
                        <input type="hidden" name="modalidade" value="{{ request('modalidade') }}">
                    @endif

                    {{-- Ações --}}
                    <div class="flex gap-2 ml-auto">
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold bg-teal-700 hover:bg-teal-800 text-white rounded-lg px-4 py-2 transition-colors shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filtrar
                        </button>
                        @if(request('prefeitura_id') || request('status') || request('modalidade') || request('data_de') || request('data_ate'))
                            <a href="{{ route('admin.planejamento.index', ['visao' => $visao]) }}"
                                class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-400 hover:text-red-500 rounded-lg px-3 py-2 hover:bg-red-50 transition-all duration-150">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Limpar
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Painel de período --}}
                <div x-show="showPeriodo"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="rounded-xl border border-teal-100 bg-teal-50/40 p-3.5"
                    style="display: none">

                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-teal-700 mb-1.5">De</label>
                            <input type="date" name="data_de" value="{{ request('data_de') }}"
                                class="rounded-lg border-teal-200 bg-white shadow-sm text-sm focus:ring-teal-500 focus:border-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-teal-700 mb-1.5">Até</label>
                            <input type="date" name="data_ate" value="{{ request('data_ate') }}"
                                class="rounded-lg border-teal-200 bg-white shadow-sm text-sm focus:ring-teal-500 focus:border-teal-500">
                        </div>
                        <p class="text-xs text-teal-600/70 self-end pb-2">Filtra pela data de abertura da sessão</p>
                    </div>
                </div>

            </form>
        </div>

        @if($visao === 'inexigibilidade')

        {{-- ====================================================
             VISÃO INEXIGIBILIDADE / DISPENSA: colunas por cidade
             ==================================================== --}}
        @if($colunasPorPrefeitura->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="w-16 h-16 bg-violet-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-violet-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <p class="text-gray-500 font-medium">Nenhum processo de inexigibilidade encontrado</p>
                <p class="text-sm text-gray-400 mt-1">Tente ajustar os filtros.</p>
            </div>
        @else
        <div class="overflow-x-auto pb-4">
            <div class="flex gap-3" style="min-width: max-content;">

                @foreach($colunasPorPrefeitura as $grupo)
                    @php
                        $prefeitura = $grupo->first()->prefeitura;
                        $corPref    = $prefeitura->cor ?? '#7c3aed';
                        $cidade     = $prefeitura->cidade ?? $prefeitura->nome ?? '—';
                    @endphp

                    <div class="flex flex-col rounded-xl overflow-hidden border border-violet-200 bg-violet-50/40 shadow-sm min-h-[280px] w-[220px] shrink-0">

                        {{-- Cabeçalho da coluna --}}
                        <div class="px-3 py-3 flex items-center justify-between gap-2"
                            style="background-color: {{ $corPref }};">
                            <span class="text-sm font-bold truncate leading-tight text-white" title="{{ $cidade }}">
                                {{ $cidade }}
                            </span>
                            <span class="text-xs font-bold bg-black/20 text-white rounded-full px-2.5 py-0.5 shrink-0 tabular-nums min-w-[1.75rem] text-center">
                                {{ $grupo->count() }}
                            </span>
                        </div>

                        {{-- Cards --}}
                        <div class="kanban-column-cards flex flex-col gap-2 p-2 flex-1">
                            @foreach($grupo as $processo)
                                @include('Admin.Planejamento._card', ['ocultarCidade' => true, 'ocultarAcoes' => true])
                            @endforeach
                        </div>

                    </div>
                @endforeach

            </div>
        </div>
        @endif

        @else

        {{-- ====================================================
             MOBILE: lista empilhada (< md)
             ==================================================== --}}
        <div class="md:hidden space-y-3">
            @forelse($processos as $processo)
                @include('Admin.Planejamento._card', ['exibirStatus' => true])
            @empty
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 font-medium">Nenhum processo encontrado</p>
                    <p class="text-sm text-gray-400 mt-1">Tente ajustar os filtros.</p>
                </div>
            @endforelse
        </div>

        {{-- ====================================================
             DESKTOP: Kanban por fase (>= md)
             ==================================================== --}}
        <div class="hidden md:block overflow-x-auto pb-4">
            <div class="grid grid-cols-6 gap-3 min-w-[1240px]">

                @foreach($statusConfig as $status => $cfg)
                    <div class="flex flex-col rounded-xl overflow-hidden border {{ $cfg['cor_col'] }} shadow-sm min-h-[280px]">

                        {{-- Cabeçalho da coluna --}}
                        <div class="{{ $cfg['cor_head'] }} px-3 py-3 flex items-center justify-between gap-2">
                            <span class="text-sm font-bold truncate leading-tight">{{ $cfg['label'] }}</span>
                            <span class="text-xs font-bold bg-black/15 text-white rounded-full px-2.5 py-0.5 shrink-0 tabular-nums min-w-[1.75rem] text-center">
                                {{ $colunas[$status]->count() }}
                            </span>
                        </div>

                        {{-- Cards da coluna --}}
                        <div class="kanban-column-cards flex flex-col gap-2 p-2 flex-1">
                            @forelse($colunas[$status] as $processo)
                                @include('Admin.Planejamento._card')
                            @empty
                                <div class="flex flex-col items-center justify-center flex-1 py-8 text-center">
                                    <svg class="w-6 h-6 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <p class="text-xs text-gray-400">Nenhum processo</p>
                                </div>
                            @endforelse
                        </div>

                    </div>
                @endforeach

            </div>
        </div>

        @endif

    </div>

    {{-- Modal: Agendar Sessão --}}
    <div
        x-show="modalAgendar"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4"
        @click.self="modalAgendar = false"
        style="display:none">

        <div
            x-show="modalAgendar"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
            @click.stop>

            <div class="h-1 bg-amber-500"></div>

            <div class="p-6 space-y-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Agendar Sessão</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            O processo avançará para <strong class="text-gray-700">Em Andamento</strong> automaticamente na meia-noite da data escolhida.
                        </p>
                    </div>
                    <button @click="modalAgendar = false" class="shrink-0 text-gray-400 hover:text-gray-600 transition-colors mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="`{{ url('admin/planejamento') }}/${processoId}/status`">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="planejamento_status" value="aguardando_sessao">
                    <div class="space-y-4">
                        <div x-show="dataSessao" class="flex items-start gap-2.5 bg-amber-50 border border-amber-200 rounded-lg px-3.5 py-2.5" style="display:none">
                            <svg class="w-4 h-4 shrink-0 text-amber-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-xs text-amber-800">
                                Data detectada automaticamente dos dados do processo.
                                Você pode alterá-la se necessário.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Data de Abertura <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="data_abertura" required
                                :value="dataSessao"
                                class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-amber-500 focus:border-amber-500">
                        </div>
                        <div class="flex justify-end gap-3 pt-1">
                            <button type="button" @click="modalAgendar = false"
                                class="text-sm font-semibold text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-lg px-5 py-2 transition-colors shadow-sm">
                                Confirmar Agendamento
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const columns = document.querySelectorAll('.kanban-column-cards');
            columns.forEach(column => {
                new Sortable(column, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    draggable: '.kanban-card',
                    onEnd: function (evt) {
                        const cardIds = Array.from(column.querySelectorAll('.kanban-card')).map(card => card.getAttribute('data-id'));
                        
                        fetch('{{ route('admin.planejamento.reorder') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ ids: cardIds })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.sucesso) {
                                console.log('Ordem atualizada com sucesso');
                            } else {
                                alert('Erro ao atualizar a ordem. Por favor, tente novamente.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Erro de conexão ao atualizar a ordem.');
                        });
                    }
                });
            });
        });
    </script>
@endpush

<style>
    .sortable-ghost {
        opacity: 0.4;
        border: 2px dashed #0f766e !important;
        background-color: #f0fdfa !important;
    }
</style>
@endsection

