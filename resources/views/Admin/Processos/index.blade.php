@extends('layouts.app')
@section('page-title', 'Gestão de Processos')
@section('page-subtitle', 'Gerencie todos os processos licitatórios de forma centralizada')

@section('content')
    <div class="py-8">

        <!-- Botão Novo Processo -->
        <div class="flex justify-end mb-8">
            <a href="{{ route('admin.processos.create') }}"
               class="inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-[#052323] to-[#052323] rounded-xl hover:shadow-lg hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg> Novo Processo
            </a>
        </div>

        <!-- Mensagem de Sucesso -->
        @if (session('success'))
            <div class="p-4 mb-8 border border-green-200 shadow-sm rounded-2xl bg-gradient-to-r from-green-50 to-emerald-50">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Botão Voltar -->
        @if(request('prefeitura_id'))
            <div class="mb-4">
                <a href="{{ route('admin.processos.index') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar para todas as prefeituras
                </a>
            </div>
        @endif

        <!-- Prefeituras Cards -->
        @if(!request('prefeitura_id'))
            <div class="mb-8">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">Selecione uma Prefeitura</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($prefeituras as $prefeitura)
                        <a href="{{ route('admin.processos.index', ['prefeitura_id' => $prefeitura->id]) }}"
                           class="prefeitura-card group relative p-6 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg hover:border-[#009496] transition-all duration-300 transform hover:-translate-y-1 cursor-pointer block">
                            <div class="absolute transition-opacity duration-300 opacity-0 top-4 right-4 group-hover:opacity-100">
                                <svg class="w-5 h-5 text-[#009496]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                            <div class="flex items-center mb-3">
                                <div class="p-2 rounded-lg bg-[#009496]/10">
                                    <svg class="w-6 h-6 text-[#009496]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-6 0H5m2 0h4M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <h3 class="text-base font-semibold text-gray-800 group-hover:text-[#009496] transition-colors duration-300">
                                {{ $prefeitura->nome }}
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">{{ $prefeitura->email }}</p>
                            <div class="pt-3 mt-3 border-t border-gray-100">
                    <span class="text-xs font-medium text-[#009496] bg-[#009496]/10 px-2 py-1 rounded-full">
                        {{ $prefeitura->processos_count }} processos
                    </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Tabela de Processos (mostrada apenas quando há filtro de prefeitura) -->
        @if(request('prefeitura_id'))
            <!-- FILTRO AVANÇADO MELHORADO -->
            <div class="mb-6 bg-white border border-gray-200 rounded-2xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h4 class="text-lg font-semibold text-gray-800">Filtros Avançados</h4>
                    <p class="text-sm text-gray-500">Encontre processos específicos usando múltiplos critérios</p>
                </div>

                <form action="{{ route('admin.processos.index') }}" method="GET" class="p-6">
                    <input type="hidden" name="prefeitura_id" value="{{ request('prefeitura_id') }}">

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <!-- Busca unificada: nº do processo/procedimento ou objeto -->
                        <div class="md:col-span-2">
                            <label for="search" class="block mb-2 text-sm font-medium text-gray-700">
                                <i class="fas fa-search mr-1"></i> Buscar
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="text-gray-400 fas fa-search"></i>
                                </div>
                                <input type="text"
                                       name="search"
                                       id="search"
                                       value="{{ request('search') }}"
                                       placeholder="Nº do processo, nº do procedimento ou objeto..."
                                       class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg
                                              focus:ring-2 focus:ring-[#009496] focus:border-transparent
                                              placeholder-gray-500 text-sm transition-all duration-200">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Procura no número do processo/procedimento e no objeto</p>
                        </div>

                        <!-- Filtro por modalidade -->
                        <div>
                            <label for="modalidade" class="block mb-2 text-sm font-medium text-gray-700">
                                <i class="fas fa-filter mr-1"></i> Modalidade
                            </label>
                            <select name="modalidade" id="modalidade"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg
                                           focus:ring-2 focus:ring-[#009496] focus:border-transparent text-sm
                                           transition-all duration-200">
                                <option value="">Todas as Modalidades</option>
                                @foreach(\App\Enums\ModalidadeEnum::cases() as $modalidade)
                                    <option value="{{ $modalidade->value }}"
                                        {{ request('modalidade') == $modalidade->value ? 'selected' : '' }}>
                                        {{ $modalidade->getDisplayName() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtro por status -->
                        <div>
                            <label for="status" class="block mb-2 text-sm font-medium text-gray-700">
                                <i class="fas fa-tasks mr-1"></i> Status
                            </label>
                            <select name="status" id="status"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg
                                           focus:ring-2 focus:ring-[#009496] focus:border-transparent text-sm
                                           transition-all duration-200">
                                @php
                                    $filtersSubmitted = request()->hasAny(['search','modalidade','status','data_inicio','data_fim','responsavel']);
                                    $defaultStatus = $filtersSubmitted ? request('status') : 'EM_ANDAMENTO';
                                @endphp
                                <option value="" {{ $defaultStatus === '' ? 'selected' : '' }}>Todos os Status</option>
                                @foreach(\App\Enums\ProcessoStatusEnum::cases() as $status)
                                    <option value="{{ $status->value }}"
                                        {{ $defaultStatus == $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Segunda linha de filtros -->
                    <div class="grid grid-cols-1 gap-6 mt-4 md:grid-cols-3">
                        <!-- Filtro por data (criação) -->
                        <div>
                            <label for="data_inicio" class="block mb-2 text-sm font-medium text-gray-700">
                                <i class="far fa-calendar mr-1"></i> Data de Criação
                            </label>
                            <div class="flex space-x-2">
                                <input type="date"
                                       name="data_inicio"
                                       id="data_inicio"
                                       value="{{ request('data_inicio') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg
                                              focus:ring-2 focus:ring-[#009496] focus:border-transparent text-sm">
                                <span class="flex items-center text-gray-400">a</span>
                                <input type="date"
                                       name="data_fim"
                                       id="data_fim"
                                       value="{{ request('data_fim') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg
                                              focus:ring-2 focus:ring-[#009496] focus:border-transparent text-sm">
                            </div>
                        </div>

                        <!-- Filtro por responsável -->
                        <div>
                            <label for="responsavel" class="block mb-2 text-sm font-medium text-gray-700">
                                <i class="fas fa-user mr-1"></i> Responsável
                            </label>
                            <input type="text"
                                   name="responsavel"
                                   id="responsavel"
                                   value="{{ request('responsavel') }}"
                                   placeholder="Nome do responsável..."
                                   class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg
                                          focus:ring-2 focus:ring-[#009496] focus:border-transparent
                                          placeholder-gray-500 text-sm">
                        </div>

                        <!-- Botões de ação -->
                        <div class="flex items-end space-x-3">
                            <button type="submit"
                                    class="flex-1 px-6 py-2.5 text-sm font-medium text-white bg-[#009496] rounded-lg
                                           hover:bg-[#007a7a] transition-all duration-200
                                           focus:outline-none focus:ring-2 focus:ring-[#009496] focus:ring-offset-2
                                           flex items-center justify-center">
                                <i class="mr-2 fas fa-search"></i>
                                Aplicar Filtros
                            </button>

                            @if(request()->hasAny(['search','modalidade', 'status', 'data_inicio', 'data_fim', 'responsavel']))
                                <a href="{{ route('admin.processos.index', ['prefeitura_id' => request('prefeitura_id')]) }}"
                                   class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg
                                          hover:bg-gray-200 transition-all duration-200
                                          focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2
                                          flex items-center">
                                    <i class="mr-2 fas fa-times"></i>
                                    Limpar
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Mostrar filtros ativos -->
                    @if(request()->hasAny(['search','modalidade', 'status', 'data_inicio', 'data_fim', 'responsavel']))
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-medium text-gray-700">Filtros ativos:</span>

                                @if(request('search'))
                                    <span class="px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full flex items-center">
                                        <i class="mr-1 fas fa-search"></i>
                                        Busca: "{{ request('search') }}"
                                    </span>
                                @endif

                                @if(request('modalidade'))
                                    @php
                                        $modalidade = \App\Enums\ModalidadeEnum::tryFrom(request('modalidade'));
                                    @endphp
                                    @if($modalidade)
                                        <span class="px-3 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full flex items-center">
                                            <i class="mr-1 fas fa-filter"></i>
                                            {{ $modalidade->getDisplayName() }}
                                        </span>
                                    @endif
                                @endif

                                @if(request('status'))
                                    @php
                                        $status = \App\Enums\ProcessoStatusEnum::tryFrom(request('status'));
                                    @endphp
                                    @if($status)
                                        <span class="px-3 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full flex items-center">
                                            <i class="mr-1 fas fa-tasks"></i>
                                            {{ $status->label() }}
                                        </span>
                                    @endif
                                @endif

                                @if(request('data_inicio') || request('data_fim'))
                                    <span class="px-3 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full flex items-center">
                                        <i class="mr-1 far fa-calendar"></i>
                                        {{ request('data_inicio') ? Carbon\Carbon::parse(request('data_inicio'))->format('d/m/Y') : 'Início' }}
                                        @if(request('data_inicio') && request('data_fim')) a @endif
                                        {{ request('data_fim') ? Carbon\Carbon::parse(request('data_fim'))->format('d/m/Y') : 'Fim' }}
                                    </span>
                                @endif

                                @if(request('responsavel'))
                                    <span class="px-3 py-1 text-xs font-medium bg-pink-100 text-pink-800 rounded-full flex items-center">
                                        <i class="mr-1 fas fa-user"></i>
                                        Responsável: {{ request('responsavel') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </form>
            </div>

            <!-- Tabela de Processos -->
            <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-100 shadow-sm rounded-2xl">
                <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                    <div class="flex flex-col items-start justify-between lg:flex-row lg:items-center">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">
                                Processos da Prefeitura: {{ $prefeituras->find(request('prefeitura_id'))->nome ?? 'Selecionada' }}
                            </h3>
                            @if($processos->total() > 0)
                                <p class="mt-1 text-sm text-gray-600">
                                    Mostrando <span class="font-semibold">{{ $processos->firstItem() }}</span>
                                    a <span class="font-semibold">{{ $processos->lastItem() }}</span>
                                    de <span class="font-semibold">{{ $processos->total() }}</span> processos
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center mt-2 space-x-3 lg:mt-0">
                            <div class="text-sm text-gray-500">
                                @if($processos->total() > 0)
                                    <i class="mr-1 fas fa-clipboard-list"></i>
                                    {{ $processos->total() }} processo(s) encontrado(s)
                                @endif
                            </div>
{{--                            @if($processos->total() > 0)--}}
{{--                                <button type="button"--}}
{{--                                        onclick="exportarResultados()"--}}
{{--                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-[#009496] bg-white border border-[#009496] rounded-lg hover:bg-[#009496] hover:text-white transition-all duration-200">--}}
{{--                                    <i class="mr-2 fas fa-file-export"></i>--}}
{{--                                    Exportar--}}
{{--                                </button>--}}
{{--                            @endif--}}
                        </div>
                    </div>
                </div>

                <!-- Mensagem de busca por objeto -->
                @if(request('search') && $processos->total() > 0)
                    <div class="px-6 py-3 bg-blue-50 border-b border-blue-100">
                        <div class="flex items-center">
                            <i class="mr-2 text-blue-500 fas fa-info-circle"></i>
                            <p class="text-sm text-blue-700">
                                Resultados para: "<span class="font-semibold">{{ request('search') }}</span>"
                            </p>
                        </div>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full overflow-hidden divide-y divide-gray-200 rounded-lg shadow-sm">
                        <thead class="sticky top-0 z-10 bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">
                                <i class="mr-1 fas fa-file-alt"></i> Processo / Objeto
                            </th>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">
                                <i class="mr-1 fas fa-tasks"></i> Status
                            </th>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">
                                <i class="mr-1 fas fa-filter"></i> Modalidade
                            </th>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">
                                <i class="mr-1 fas fa-info-circle"></i> Detalhes
                            </th>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-center text-gray-600 uppercase">
                                <i class="mr-1 fas fa-stream"></i> Etapa
                            </th>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-center text-gray-600 uppercase">
                                <i class="mr-1 fas fa-cog"></i> Ações
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($processos as $processo)
                            <tr class="transition-colors duration-200 hover:bg-gray-50/80">
                                @php
                                    $status = $processo->status instanceof \App\Enums\ProcessoStatusEnum
                                        ? $processo->status
                                        : \App\Enums\ProcessoStatusEnum::tryFrom($processo->status) ?? \App\Enums\ProcessoStatusEnum::EM_ANDAMENTO;

                                    $modalidade = $processo->modalidade instanceof \App\Enums\ModalidadeEnum
                                        ? $processo->modalidade
                                        : \App\Enums\ModalidadeEnum::tryFrom($processo->modalidade);
                                    $modalidadeValue = $modalidade instanceof \App\Enums\ModalidadeEnum
                                        ? $modalidade->value
                                        : $processo->modalidade;

                                    // Etapa atual — derivada de dados já existentes (não altera regra de negócio):
                                    // Inicialização → Finalização → Contrato. Mesma visibilidade dos antigos botões.
                                    $ehInexigibilidade = $modalidadeValue == \App\Enums\ModalidadeEnum::INEXIGIBILIDADE->value;
                                    $temContrato = $processo->contrato !== null;
                                    $temFinalizacao = $processo->finalizacao !== null || $status->value === 'FINALIZADO';
                                    $mostraContrato = !$ehInexigibilidade
                                        && !($processo->modalidade == 4 && optional($processo->detalhe)->tipo_srp === 'nao');

                                    $etapas = [[
                                        'label' => 'Inicialização', 'icon' => 'fa-play',
                                        'rota' => route('admin.processos.iniciar', $processo->id),
                                        'estado' => ($temFinalizacao || $temContrato) ? 'concluida' : 'atual',
                                    ]];
                                    if (!$ehInexigibilidade) {
                                        $etapas[] = [
                                            'label' => 'Finalização', 'icon' => 'fa-check',
                                            'rota' => route('admin.processos.finalizacao.finalizar', $processo->id),
                                            'estado' => $temContrato ? 'concluida' : ($temFinalizacao ? 'atual' : 'pendente'),
                                        ];
                                    }
                                    if ($mostraContrato) {
                                        $etapas[] = [
                                            'label' => 'Contrato', 'icon' => 'fa-file-contract',
                                            'rota' => route('admin.processos.contrato.index', $processo->id),
                                            'estado' => $temContrato ? 'atual' : 'pendente',
                                        ];
                                    }
                                @endphp

                                {{-- Processo / Objeto --}}
                                <td class="px-4 py-3 align-top">
                                    <div class="font-mono text-sm font-semibold text-gray-900 whitespace-nowrap">
                                        {{ $processo->numero_processo }}
                                    </div>
                                    <div class="mt-0.5 text-sm text-gray-700 max-w-md line-clamp-2" title="{{ strip_tags($processo->objeto) }}">
                                        @if(request('search') && $processo->objeto)
                                            @php
                                                $objeto = strip_tags($processo->objeto);
                                                $termoEscapado = preg_quote(request('search'), '/');
                                                $objetoSeguro = htmlspecialchars($objeto);
                                                $highlighted = preg_replace("/({$termoEscapado})/iu", '<span class="bg-yellow-200 px-1 rounded">$1</span>', $objetoSeguro);
                                            @endphp
                                            {!! $highlighted !!}
                                        @else
                                            {!! strip_tags($processo->objeto) ?: '<span class="italic text-gray-400">Sem objeto</span>' !!}
                                        @endif
                                    </div>
                                    <div class="mt-1 text-xs text-gray-400">
                                        <i class="mr-1 fas fa-user"></i>{{ $processo->user->name ?? 'N/A' }}
                                        <span class="mx-1">·</span>
                                        <i class="mr-1 far fa-calendar"></i>{{ $processo->created_at->format('d/m/Y') }}
                                    </div>
                                    @if($status->value === 'CANCELADO')
                                        <div class="mt-1 text-xs text-red-600">
                                            <i class="mr-1 fas fa-times-circle"></i>Cancelado em {{ $processo->data_cancelamento ? \Carbon\Carbon::parse($processo->data_cancelamento)->format('d/m/Y') : 'N/A' }}@if($processo->motivo_cancelamento) — {{ $processo->motivo_cancelamento }}@endif
                                        </div>
                                    @elseif($status->value === 'ADIADO')
                                        <div class="mt-1 text-xs text-orange-600">
                                            <i class="mr-1 fas fa-clock"></i>Adiado para {{ $processo->data_adiamento ? \Carbon\Carbon::parse($processo->data_adiamento)->format('d/m/Y') : 'N/A' }}@if($processo->justificativa_adiamento) — {{ $processo->justificativa_adiamento }}@endif
                                        </div>
                                    @elseif($status->value === 'REPUBLICADO')
                                        <div class="mt-1 text-xs text-purple-600">
                                            <i class="mr-1 fas fa-redo"></i>Republicado @if($processo->processo_original_id) — Original: {{ $processo->processoOriginal->numero_processo ?? 'N/A' }}@endif
                                        </div>
                                    @endif
                                </td>

                                

                                {{-- Status --}}
                                <td class="px-4 py-3 align-top whitespace-nowrap">
                                    <button type="button"
                                            onclick="abrirModalStatusProcesso({{ $processo->id }}, '{{ $processo->numero_processo }}', '{{ $status->value }}')"
                                            class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full cursor-pointer transition-all duration-200 hover:opacity-90 hover:scale-105
                                                @if($status->value === 'EM_ANDAMENTO') bg-blue-100 text-blue-800 hover:bg-blue-200
                                                @elseif($status->value === 'FINALIZADO') bg-green-100 text-green-800 hover:bg-green-200
                                                @elseif($status->value === 'CANCELADO') bg-red-100 text-red-800 hover:bg-red-200
                                                @elseif($status->value === 'REPUBLICADO') bg-purple-100 text-purple-800 hover:bg-purple-200
                                                @elseif($status->value === 'ADIADO') bg-orange-100 text-orange-800 hover:bg-orange-200
                                                @endif"
                                            title="Clique para alterar o status">
                                        <i class="mr-1 fas
                                            @if($status->value === 'EM_ANDAMENTO') fa-spinner
                                            @elseif($status->value === 'FINALIZADO') fa-check-circle
                                            @elseif($status->value === 'CANCELADO') fa-times-circle
                                            @elseif($status->value === 'REPUBLICADO') fa-redo
                                            @elseif($status->value === 'ADIADO') fa-clock
                                            @endif"></i>
                                        {{ $status->label() }}
                                    </button>
                                </td>

                                {{-- Modalidade --}}
                                <td class="px-4 py-3 align-top">
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full
                                        @if($modalidadeValue == \App\Enums\ModalidadeEnum::DISPENSA->value)
                                            bg-purple-100 text-purple-800 border border-purple-200
                                        @elseif($modalidadeValue == \App\Enums\ModalidadeEnum::INEXIGIBILIDADE->value)
                                            bg-pink-100 text-pink-800 border border-pink-200
                                        @elseif($modalidadeValue == \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO->value)
                                            bg-blue-100 text-blue-800 border border-blue-200
                                        @elseif($modalidadeValue == \App\Enums\ModalidadeEnum::CONCORRENCIA->value)
                                            bg-green-100 text-green-800 border border-green-200
                                        @else
                                            bg-gray-100 text-gray-800 border border-gray-200
                                        @endif">
                                        <i class="mr-1 fas
                                            @if($modalidadeValue == \App\Enums\ModalidadeEnum::DISPENSA->value) fa-file-signature
                                            @elseif($modalidadeValue == \App\Enums\ModalidadeEnum::INEXIGIBILIDADE->value) fa-ban
                                            @elseif($modalidadeValue == \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO->value) fa-gavel
                                            @elseif($modalidadeValue == \App\Enums\ModalidadeEnum::CONCORRENCIA->value) fa-balance-scale
                                            @else fa-question-circle @endif"></i>
                                        {{ $modalidade?->getDisplayName() ?? 'Não definido' }}
                                    </span>
                                </td>

                                {{-- Detalhes (compactos) --}}
                                <td class="px-4 py-3 align-top text-xs text-gray-500 whitespace-nowrap">
                                    <div><span class="text-gray-400">Procedimento:</span> <span class="font-mono text-gray-700">{{ $processo->numero_procedimento ?: '—' }}</span></div>
                                    <div class="mt-0.5">{{ $processo->tipo_contratacao_nome }}</div>
                                    <div class="mt-0.5">{{ $processo->tipo_procedimento_nome }}</div>
                                </td>
                                {{-- Etapa (stepper clicável — navega e mostra a etapa) --}}
                                <td class="px-4 py-3 align-top">
                                    <div class="flex items-center justify-center">
                                        @foreach($etapas as $i => $etapa)
                                            @if($i > 0)
                                                <div class="w-4 h-px {{ $etapa['estado'] === 'pendente' ? 'bg-gray-200' : 'bg-green-300' }}"></div>
                                            @endif
                                            <a href="{{ $etapa['rota'] }}" title="{{ $etapa['label'] }}" class="inline-flex flex-col items-center group">
                                                <span class="flex items-center justify-center rounded-full w-7 h-7 text-xs transition
                                                    @if($etapa['estado'] === 'concluida') bg-green-100 text-green-700
                                                    @elseif($etapa['estado'] === 'atual') bg-[#062F43] text-white ring-2 ring-[#062F43]/20
                                                    @else bg-gray-100 text-gray-400 group-hover:bg-gray-200 @endif">
                                                    <i class="fas {{ $etapa['icon'] }}"></i>
                                                </span>
                                                <span class="mt-1 text-[10px] font-medium leading-tight
                                                    @if($etapa['estado'] === 'atual') text-[#062F43]
                                                    @elseif($etapa['estado'] === 'concluida') text-green-700
                                                    @else text-gray-400 @endif">{{ $etapa['label'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </td>

                                <td class="px-4 py-3 align-top text-center">
                                    <div class="relative inline-block text-left" x-data="{ open: false }" @click.outside="open = false">
                                        <button type="button" @click="open = !open"
                                                class="inline-flex items-center justify-center w-8 h-8 text-gray-500 transition-colors rounded-md hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300"
                                                title="Mais ações">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div x-show="open" x-cloak x-transition
                                             class="absolute right-0 z-[9999] w-52 py-1 mt-1 origin-top-right bg-white border border-gray-200 rounded-lg shadow-lg">
                                            <a href="{{ route('admin.processos.edit', $processo->id) }}"
                                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <i class="w-4 mr-2 text-gray-400 fas fa-edit"></i> Editar processo
                                            </a>
                                            @if($status->isAtivo())
                                                <button type="button" @click="open = false; abrirModalRepublicarProcesso({{ $processo->id }})"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-50">
                                                    <i class="w-4 mr-2 text-purple-500 fas fa-redo"></i> Republicar
                                                </button>
                                                <button type="button" @click="open = false; abrirModalAdiarLicitacao({{ $processo->id }})"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-50">
                                                    <i class="w-4 mr-2 text-yellow-500 fas fa-clock"></i> Adiar licitação
                                                </button>
                                                <button type="button" @click="open = false; abrirModalCancelarLicitacao({{ $processo->id }})"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-50">
                                                    <i class="w-4 mr-2 text-red-500 fas fa-times-circle"></i> Cancelar licitação
                                                </button>
                                            @endif
                                            @if($status->value === 'CANCELADO')
                                                <button type="button" @click="open = false; reverterCancelamento({{ $processo->id }}, '{{ $processo->numero_processo }}')"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-50">
                                                    <i class="w-4 mr-2 text-green-500 fas fa-undo"></i> Reverter cancelamento
                                                </button>
                                            @endif
                                            <div class="my-1 border-t border-gray-100"></div>
                                            <button type="button" @click="open = false; confirmDelete({{ $processo->id }}, '{{ $processo->numero_processo }}')"
                                                    class="flex items-center w-full px-4 py-2 text-sm text-left text-red-600 hover:bg-red-50">
                                                <i class="w-4 mr-2 fas fa-trash"></i> Excluir processo
                                            </button>
                                        </div>
                                    </div>
                                    {{-- Form usado pelo confirmDelete() para submeter a exclusão --}}
                                    <form action="{{ route('admin.processos.destroy', $processo->id) }}" method="POST" class="hidden" id="delete-form-{{ $processo->id }}">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                            <i class="text-gray-400 text-2xl fas fa-clipboard-list"></i>
                                        </div>
                                        @if(request()->hasAny(['search','modalidade', 'status']))
                                            <div>
                                                <p class="text-sm font-medium text-gray-700">Nenhum processo encontrado com os filtros aplicados.</p>
                                                <p class="mt-1 text-sm text-gray-500">Tente ajustar os critérios de busca.</p>
                                            </div>
                                            <a href="{{ route('admin.processos.index', ['prefeitura_id' => request('prefeitura_id')]) }}"
                                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-[#009496] bg-[#009496]/10 rounded-lg hover:bg-[#009496]/20 transition-colors">
                                                <i class="mr-2 fas fa-times"></i>
                                                Limpar filtros
                                            </a>
                                        @else
                                            <p class="text-sm font-medium text-gray-700">Nenhum processo encontrado para esta prefeitura</p>
                                            <p class="text-sm text-gray-500">Crie um novo processo para começar</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                @if ($processos->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex flex-col items-center justify-between md:flex-row">
                            <div class="text-sm text-gray-500">
                                Mostrando {{ $processos->firstItem() }} a {{ $processos->lastItem() }} de {{ $processos->total() }} resultados
                            </div>
                            <div class="mt-2 md:mt-0">
                                {{ $processos->withQueryString()->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Modal para Republicar Processo -->
    <div id="modalRepublicarProcesso" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

            <div class="inline-block p-6 my-8 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Republicar Processo</h3>
                    <div class="mt-2">
                        <form id="formRepublicarProcesso">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label for="novo_numero_processo" class="block text-sm font-medium text-gray-700">
                                        Novo Número do Processo *
                                    </label>
                                    <input type="text" name="novo_numero_processo" id="novo_numero_processo" required
                                           class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                                           placeholder="Ex: 001/2024">
                                </div>

                                <div>
                                    <label for="novo_numero_procedimento" class="block text-sm font-medium text-gray-700">
                                        Novo Número do Procedimento *
                                    </label>
                                    <input type="text" name="novo_numero_procedimento" id="novo_numero_procedimento" required
                                           class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                                           placeholder="Ex: 12345/2024">
                                </div>

                                <div>
                                    <label for="data_publicacao" class="block text-sm font-medium text-gray-700">
                                        Data da Nova Publicação *
                                    </label>
                                    <input type="date" name="data_publicacao" id="data_publicacao" required
                                           value="{{ now()->format('Y-m-d') }}"
                                           class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                </div>

                                <input type="hidden" name="processo_id" id="processo_id_republicar">
                            </div>

                            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                <button type="submit"
                                        class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-purple-600 border border-transparent rounded-md shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:col-start-2 sm:text-sm">
                                    Republicar Processo
                                </button>
                                <button type="button"
                                        onclick="fecharModal('modalRepublicarProcesso')"
                                        class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Cancelar Licitação -->
    <div id="modalCancelarLicitacao" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

            <div class="inline-block p-6 my-8 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Cancelar Licitação</h3>
                    <div class="mt-2">
                        <form id="formCancelarLicitacao">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label for="data_cancelamento" class="block text-sm font-medium text-gray-700">
                                        Data do Cancelamento *
                                    </label>
                                    <input type="date" name="data_cancelamento" id="data_cancelamento" required
                                           value="{{ now()->format('Y-m-d') }}"
                                           class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="motivo_cancelamento" class="block text-sm font-medium text-gray-700">
                                        Motivo do Cancelamento
                                    </label>
                                    <textarea name="motivo_cancelamento" id="motivo_cancelamento" rows="3"
                                              class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                              placeholder="Informe o motivo do cancelamento..."></textarea>
                                </div>

                                <input type="hidden" name="processo_id" id="processo_id_cancelar">
                            </div>

                            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                <button type="submit"
                                        class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm">
                                    Cancelar Licitação
                                </button>
                                <button type="button"
                                        onclick="fecharModal('modalCancelarLicitacao')"
                                        class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                    Voltar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Adiar Licitação -->
    <div id="modalAdiarLicitacao" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

            <div class="inline-block p-6 my-8 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Adiar Licitação</h3>
                    <div class="mt-2">
                        <form id="formAdiarLicitacao">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label for="nova_data" class="block text-sm font-medium text-gray-700">
                                        Nova Data *
                                    </label>
                                    <input type="date" name="nova_data" id="nova_data" required
                                           value="{{ now()->addDays(7)->format('Y-m-d') }}"
                                           class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="novo_horario" class="block text-sm font-medium text-gray-700">
                                        Novo Horário *
                                    </label>
                                    <input type="time" name="novo_horario" id="novo_horario" required
                                           value="09:00"
                                           class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="justificativa_adiamento" class="block text-sm font-medium text-gray-700">
                                        Justificativa do Adiamento
                                    </label>
                                    <textarea name="justificativa_adiamento" id="justificativa_adiamento" rows="3"
                                              class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm"
                                              placeholder="Informe a justificativa para o adiamento..."></textarea>
                                </div>

                                <input type="hidden" name="processo_id" id="processo_id_adiar">
                            </div>

                            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                <button type="submit"
                                        class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-yellow-600 border border-transparent rounded-md shadow-sm hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:col-start-2 sm:text-sm">
                                    Adiar Licitação
                                </button>
                                <button type="button"
                                        onclick="fecharModal('modalAdiarLicitacao')"
                                        class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Republicar Edital (será usado na view iniciar.blade.php) -->
    <div id="modalRepublicarEdital" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

            <div class="inline-block p-6 my-8 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Republicar Edital</h3>
                    <div class="mt-2">
                        <form id="formRepublicarEdital">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label for="data_republicacao" class="block text-sm font-medium text-gray-700">
                                        Data da Republicação *
                                    </label>
                                    <input type="date" name="data" id="data_republicacao" required
                                           value="{{ now()->format('Y-m-d') }}"
                                           class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="justificativa_republicacao" class="block text-sm font-medium text-gray-700">
                                        Justificativa da Republicação
                                    </label>
                                    <textarea name="justificativa" id="justificativa_republicacao" rows="3"
                                              class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                                              placeholder="Informe o motivo da republicação..."></textarea>
                                </div>

                                <input type="hidden" name="processo_id" id="processo_id_republicar_edital">
                            </div>

                            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                <button type="submit"
                                        class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-purple-600 border border-transparent rounded-md shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:col-start-2 sm:text-sm">
                                    Republicar Edital
                                </button>
                                <button type="button"
                                        onclick="fecharModal('modalRepublicarEdital')"
                                        class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Adicione este modal após os outros modais existentes -->
    <div id="modalStatusProcesso" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block p-6 my-8 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Alterar Status do Processo</h3>
                    <div class="mt-2">
                        <form id="formStatusProcesso">
                            @csrf
                            @method('PUT')

                            <div class="space-y-4">
                                <div>
                                    <label for="processo_numero" class="block text-sm font-medium text-gray-700">
                                        Processo
                                    </label>
                                    <input type="text" id="processo_numero" readonly
                                           class="block w-full px-3 py-2 mt-1 bg-gray-100 border border-gray-300 rounded-md shadow-sm sm:text-sm">
                                </div>

                                <div>
                                    <label for="status_select" class="block mb-2 text-sm font-medium text-gray-700">
                                        Selecione o novo status *
                                    </label>

                                    <div class="space-y-2">
                                        @foreach(\App\Enums\ProcessoStatusEnum::cases() as $status)
                                            <div class="flex items-center">
                                                <input type="radio"
                                                       name="status"
                                                       value="{{ $status->value }}"
                                                       id="status_{{ $status->value }}"
                                                       class="w-4 h-4 text-[#009496] border-gray-300 focus:ring-[#009496]">
                                                <label for="status_{{ $status->value }}"
                                                       class="block ml-3 text-sm text-gray-900 cursor-pointer">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                                @if($status->value === 'EM_ANDAMENTO') bg-blue-100 text-blue-800
                                                                @elseif($status->value === 'FINALIZADO') bg-green-100 text-green-800
                                                                @elseif($status->value === 'CANCELADO') bg-red-100 text-red-800
                                                                @elseif($status->value === 'REPUBLICADO') bg-purple-100 text-purple-800
                                                                @elseif($status->value === 'ADIADO') bg-orange-100 text-orange-800
                                                                @endif">
                                                                {{ $status->label() }}
                                                            </span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Campos condicionais -->
                                <div id="cancelamento_fields" class="hidden space-y-4">
                                    <div>
                                        <label for="data_cancelamento_status" class="block text-sm font-medium text-gray-700">
                                            Data do Cancelamento
                                        </label>
                                        <input type="date" name="data_cancelamento_status" id="data_cancelamento_status"
                                               value="{{ now()->format('Y-m-d') }}"
                                               class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label for="motivo_cancelamento_status" class="block text-sm font-medium text-gray-700">
                                            Motivo do Cancelamento
                                        </label>
                                        <textarea name="motivo_cancelamento_status" id="motivo_cancelamento_status" rows="2"
                                                  class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                                  placeholder="Informe o motivo do cancelamento..."></textarea>
                                    </div>
                                </div>

                                <div id="adiamento_fields" class="hidden space-y-4">
                                    <div>
                                        <label for="data_adiamento_status" class="block text-sm font-medium text-gray-700">
                                            Nova Data
                                        </label>
                                        <input type="date" name="data_adiamento_status" id="data_adiamento_status"
                                               value="{{ now()->addDays(7)->format('Y-m-d') }}"
                                               class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label for="justificativa_adiamento_status" class="block text-sm font-medium text-gray-700">
                                            Justificativa do Adiamento
                                        </label>
                                        <textarea name="justificativa_adiamento_status" id="justificativa_adiamento_status" rows="2"
                                                  class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm"
                                                  placeholder="Informe a justificativa para o adiamento..."></textarea>
                                    </div>
                                </div>

                                <input type="hidden" name="processo_id" id="processo_id_status">
                            </div>

                            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                <button type="submit"
                                        class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-[#009496] border border-transparent rounded-md shadow-sm hover:bg-[#007a7a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#009496] sm:col-start-2 sm:text-sm">
                                    Atualizar Status
                                </button>
                                <button type="button"
                                        onclick="fecharModal('modalStatusProcesso')"
                                        class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function confirmDelete(processoId, numeroProcesso) {
                Swal.fire({
                    title: 'Tem certeza?',
                    html: `Você está prestes a excluir o processo <strong>${numeroProcesso}</strong>. <br>Esta ação não pode ser desfeita!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500',
                        cancelButton: 'px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 mr-3'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`delete-form-${processoId}`).submit();
                    }
                });
            }

            // Funções para abrir/fechar modais
            function abrirModalRepublicarProcesso(processoId) {
                document.getElementById('processo_id_republicar').value = processoId;
                document.getElementById('modalRepublicarProcesso').classList.remove('hidden');
            }

            function abrirModalCancelarLicitacao(processoId) {
                document.getElementById('processo_id_cancelar').value = processoId;
                document.getElementById('data_cancelamento').value = new Date().toISOString().split('T')[0];
                document.getElementById('modalCancelarLicitacao').classList.remove('hidden');
            }

            function abrirModalAdiarLicitacao(processoId) {
                document.getElementById('processo_id_adiar').value = processoId;
                document.getElementById('nova_data').value = new Date().toISOString().split('T')[0];
                document.getElementById('novo_horario').value = '09:00';
                document.getElementById('modalAdiarLicitacao').classList.remove('hidden');
            }

            function abrirModalRepublicarEdital(processoId) {
                document.getElementById('processo_id_republicar_edital').value = processoId;
                document.getElementById('data_republicacao').value = new Date().toISOString().split('T')[0];
                document.getElementById('justificativa_republicacao').value = '';
                document.getElementById('modalRepublicarEdital').classList.remove('hidden');
            }

            // Função para abrir o modal de status
            function abrirModalStatusProcesso(processoId, numeroProcesso, statusAtual) {
                document.getElementById('processo_id_status').value = processoId;
                document.getElementById('processo_numero').value = numeroProcesso;

                // Marcar o status atual como selecionado
                const radios = document.querySelectorAll('input[name="status"]');
                radios.forEach(radio => {
                    if (radio.value === statusAtual) {
                        radio.checked = true;
                    } else {
                        radio.checked = false;
                    }
                });

                // Esconder campos condicionais
                document.getElementById('cancelamento_fields').classList.add('hidden');
                document.getElementById('adiamento_fields').classList.add('hidden');

                // Mostrar campos condicionais se o status atual for CANCELADO ou ADIADO
                if (statusAtual === 'CANCELADO') {
                    document.getElementById('cancelamento_fields').classList.remove('hidden');
                } else if (statusAtual === 'ADIADO') {
                    document.getElementById('adiamento_fields').classList.remove('hidden');
                }

                // Adicionar listeners para mostrar/ocultar campos condicionais
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.value === 'CANCELADO') {
                            document.getElementById('cancelamento_fields').classList.remove('hidden');
                            document.getElementById('adiamento_fields').classList.add('hidden');
                        } else if (this.value === 'ADIADO') {
                            document.getElementById('adiamento_fields').classList.remove('hidden');
                            document.getElementById('cancelamento_fields').classList.add('hidden');
                        } else {
                            document.getElementById('cancelamento_fields').classList.add('hidden');
                            document.getElementById('adiamento_fields').classList.add('hidden');
                        }
                    });
                });

                document.getElementById('modalStatusProcesso').classList.remove('hidden');
            }

            // Adicionar esta função ao event listener do DOM
            document.addEventListener('DOMContentLoaded', function() {
                // Status do Processo
                const formStatusProcesso = document.getElementById('formStatusProcesso');
                if (formStatusProcesso) {
                    formStatusProcesso.addEventListener('submit', async function(e) {
                        e.preventDefault();

                        const processoId = document.getElementById('processo_id_status').value;
                        const formData = new FormData(this);
                        const selectedStatus = document.querySelector('input[name="status"]:checked')?.value;

                        if (!selectedStatus) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Atenção',
                                text: 'Selecione um status para continuar.'
                            });
                            return;
                        }

                        try {
                            const response = await fetch(`/admin/processos/${processoId}/status`, {
                                method: 'PUT',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    status: selectedStatus,
                                    data_cancelamento: formData.get('data_cancelamento_status'),
                                    motivo_cancelamento: formData.get('motivo_cancelamento_status'),
                                    data_adiamento: formData.get('data_adiamento_status'),
                                    justificativa_adiamento: formData.get('justificativa_adiamento_status')
                                })
                            });

                            const data = await response.json();

                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sucesso!',
                                    text: data.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });

                                // Atualizar o badge de status na página
                                const statusButton = document.querySelector(`[onclick*="abrirModalStatusProcesso(${processoId}"]`);
                                if (statusButton) {
                                    statusButton.className = `inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full cursor-pointer transition-all duration-200 hover:opacity-90 hover:scale-105
                            ${getStatusClasses(data.data.status)}`;
                                    statusButton.textContent = data.data.status_label;

                                    // Atualizar o onclick para refletir o novo status
                                    const onclickValue = `abrirModalStatusProcesso(${processoId}, '${document.getElementById('processo_numero').value}', '${data.data.status}')`;
                                    statusButton.setAttribute('onclick', onclickValue);
                                }

                                // Fechar modal e recarregar após 1.5 segundos
                                setTimeout(() => {
                                    fecharModal('modalStatusProcesso');
                                    window.location.reload();
                                }, 1500);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro',
                                    text: data.message || 'Erro ao atualizar status'
                                });
                            }
                        } catch (error) {
                            console.error('Erro:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao atualizar o status. Tente novamente.'
                            });
                        }
                    });
                }
            });

            // Função auxiliar para obter classes CSS do status
            function getStatusClasses(statusValue) {
                switch(statusValue) {
                    case 'RASCUNHO':
                        return 'bg-gray-100 text-gray-800 hover:bg-gray-200';
                    case 'EM_INICIO':
                        return 'bg-blue-100 text-blue-800 hover:bg-blue-200';
                    case 'EM_FINALIZACAO':
                        return 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200';
                    case 'EM_CONTRATO':
                        return 'bg-indigo-100 text-indigo-800 hover:bg-indigo-200';
                    case 'FINALIZADO':
                        return 'bg-green-100 text-green-800 hover:bg-green-200';
                    case 'CANCELADO':
                        return 'bg-red-100 text-red-800 hover:bg-red-200';
                    case 'ADIADO':
                        return 'bg-orange-100 text-orange-800 hover:bg-orange-200';
                    case 'REPUBLICADO':
                        return 'bg-purple-100 text-purple-800 hover:bg-purple-200';
                    default:
                        return 'bg-gray-100 text-gray-800 hover:bg-gray-200';
                }
            }

            function fecharModal(modalId) {
                document.getElementById(modalId).classList.add('hidden');
            }

            // Helper function para mostrar mensagens
            function showMessage(message, type = 'success') {
                Swal.fire({
                    icon: type,
                    title: message,
                    showConfirmButton: false,
                    timer: 3000
                });
            }

            // Helper function para processar erros de fetch
            function handleFetchError(error) {
                console.error('Erro na requisição:', error);
                return {
                    success: false,
                    message: 'Erro de conexão. Verifique sua internet e tente novamente.'
                };
            }

            // Event listeners para os formulários
            document.addEventListener('DOMContentLoaded', function() {
                // Republicar Processo
                const formRepublicarProcesso = document.getElementById('formRepublicarProcesso');
                if (formRepublicarProcesso) {
                    formRepublicarProcesso.addEventListener('submit', async function(e) {
                        e.preventDefault();

                        const processoId = document.getElementById('processo_id_republicar').value;
                        const formData = new FormData(this);

                        try {
                            const response = await fetch(`/admin/processos/${processoId}/republicar-processo`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json'
                                },
                                body: formData
                            });

                            const data = await response.json();

                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sucesso!',
                                    text: data.message,
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                                fecharModal('modalRepublicarProcesso');

                                if (data.redirect_url) {
                                    setTimeout(() => {
                                        window.location.href = data.redirect_url;
                                    }, 2000);
                                } else {
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 2000);
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro',
                                    text: data.message || 'Erro ao republicar processo'
                                });
                            }
                        } catch (error) {
                            console.error('Erro:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao republicar o processo. Tente novamente.'
                            });
                        }
                    });
                }

                // Cancelar Licitação
                const formCancelarLicitacao = document.getElementById('formCancelarLicitacao');
                if (formCancelarLicitacao) {
                    formCancelarLicitacao.addEventListener('submit', async function(e) {
                        e.preventDefault();

                        const processoId = document.getElementById('processo_id_cancelar').value;
                        const formData = new FormData(this);

                        try {
                            const response = await fetch(`/admin/processos/${processoId}/cancelar-licitacao`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json'
                                },
                                body: formData
                            });

                            const data = await response.json();

                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sucesso!',
                                    text: data.message,
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                                fecharModal('modalCancelarLicitacao');
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro',
                                    text: data.message || 'Erro ao cancelar licitação'
                                });
                            }
                        } catch (error) {
                            console.error('Erro:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao cancelar a licitação. Tente novamente.'
                            });
                        }
                    });
                }

                // Adiar Licitação
                const formAdiarLicitacao = document.getElementById('formAdiarLicitacao');
                if (formAdiarLicitacao) {
                    formAdiarLicitacao.addEventListener('submit', async function(e) {
                        e.preventDefault();

                        const processoId = document.getElementById('processo_id_adiar').value;
                        const formData = new FormData(this);

                        try {
                            const response = await fetch(`/admin/processos/${processoId}/adiar-licitacao`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json'
                                },
                                body: formData
                            });

                            const data = await response.json();

                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sucesso!',
                                    text: data.message,
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                                fecharModal('modalAdiarLicitacao');
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro',
                                    text: data.message || 'Erro ao adiar licitação'
                                });
                            }
                        } catch (error) {
                            console.error('Erro:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao adiar a licitação. Tente novamente.'
                            });
                        }
                    });
                }

                // Republicar Edital
                const formRepublicarEdital = document.getElementById('formRepublicarEdital');
                if (formRepublicarEdital) {
                    formRepublicarEdital.addEventListener('submit', async function(e) {
                        e.preventDefault();

                        const processoId = document.getElementById('processo_id_republicar_edital').value;
                        const formData = new FormData(this);

                        try {
                            const response = await fetch(`/admin/processos/${processoId}/republicar-edital`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json'
                                },
                                body: formData
                            });

                            const data = await response.json();

                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sucesso!',
                                    text: data.message,
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                                fecharModal('modalRepublicarEdital');
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro',
                                    text: data.message || 'Erro ao republicar edital'
                                });
                            }
                        } catch (error) {
                            console.error('Erro:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao republicar o edital. Tente novamente.'
                            });
                        }
                    });
                }
            });

            document.addEventListener('DOMContentLoaded', function() {
                // Foco automático no campo de pesquisa
                const searchInput = document.getElementById('search');
                if (searchInput && !searchInput.value) {
                    searchInput.focus();
                }

                // Pesquisa em tempo real (opcional)
                let searchTimeout;
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(() => {
                            // Se quiser fazer pesquisa em tempo real, pode implementar aqui
                            // this.form.submit(); // Descomente para submit automático
                        }, 500);
                    });
                }
            });

            // Adicione esta função no seu script JavaScript
            function reverterCancelamento(processoId, numeroProcesso) {
                Swal.fire({
                    title: 'Reverter Cancelamento',
                    html: `Tem certeza que deseja reverter o cancelamento do processo <strong>${numeroProcesso}</strong>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sim, reverter!',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500',
                        cancelButton: 'px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 mr-3'
                    }
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            // Mostrar loading
                            Swal.fire({
                                title: 'Processando...',
                                text: 'Revertendo cancelamento...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            // Fazer a requisição
                            const response = await fetch(`/admin/processos/${processoId}/reverter-cancelamento`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                }
                            });

                            const data = await response.json();

                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sucesso!',
                                    text: data.message,
                                    showConfirmButton: false,
                                    timer: 2000
                                });

                                // Recarregar a página após 2 segundos
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro',
                                    text: data.message || 'Erro ao reverter cancelamento'
                                });
                            }
                        } catch (error) {
                            console.error('Erro:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao reverter o cancelamento. Tente novamente.'
                            });
                        }
                    }
                });
            }
        </script>
    @endpush

    <style>
        .swal2-popup {
            border-radius: 16px !important;
        }

        /* Destaque para o campo de pesquisa por objeto */
        #search:focus {
            box-shadow: 0 0 0 3px rgba(0, 148, 150, 0.1);
            border-color: #009496;
        }

        /* Animação para os cards de prefeitura */
        .prefeitura-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .prefeitura-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Estilo para badges de filtro ativo */
        .filter-badge {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection
