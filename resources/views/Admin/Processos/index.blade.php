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
            <!-- ADICIONE ESTA PARTE: Campo de Pesquisa Avançada -->
            <div class="px-6 py-4 bg-white border-b border-gray-200">
                <form action="{{ route('admin.processos.index') }}" method="GET"
                      class="flex flex-col gap-4 p-4 bg-gray-50 rounded-lg">
                    <input type="hidden" name="prefeitura_id" value="{{ request('prefeitura_id') }}">

                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
                        <!-- Campo de pesquisa principal -->
                        <div class="flex-1">
                            <label for="search" class="block mb-2 text-sm font-medium text-gray-700">
                                Pesquisa Livre
                            </label>
                            <div class="relative">
                                <svg class="absolute w-5 h-5 text-gray-400 transform -translate-y-1/2 left-3 top-1/2"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <input type="text"
                                       name="search"
                                       id="search"
                                       value="{{ request('search') }}"
                                       placeholder="Pesquisar por objeto, número, prefeitura ou responsável..."
                                       class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg
                                  focus:ring-2 focus:ring-[#009496] focus:border-transparent
                                  placeholder-gray-500 text-sm">
                            </div>
                        </div>

                        <!-- Filtro por modalidade (opcional) -->
                        <div class="w-full lg:w-48">
                            <label for="modalidade" class="block mb-2 text-sm font-medium text-gray-700">
                                Modalidade
                            </label>
                            <select name="modalidade" id="modalidade"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg
                               focus:ring-2 focus:ring-[#009496] focus:border-transparent text-sm">
                                <option value="">Todas</option>
                                @foreach(\App\Enums\ModalidadeEnum::cases() as $modalidade)
                                    <option value="{{ $modalidade->value }}"
                                        {{ request('modalidade') == $modalidade->value ? 'selected' : '' }}>
                                        {{ $modalidade->getDisplayName() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtro por status (opcional) -->
                        <div class="w-full lg:w-48">
                            <label for="status" class="block mb-2 text-sm font-medium text-gray-700">
                                Status
                            </label>
                            <select name="status" id="status"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg
                               focus:ring-2 focus:ring-[#009496] focus:border-transparent text-sm">
                                <option value="">Todos</option>
                                @foreach(\App\Enums\ProcessoStatusEnum::cases() as $status)
                                    <option value="{{ $status->value }}"
                                        {{ request('status') == $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Botões -->
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="px-4 py-2.5 text-sm font-medium text-white bg-[#009496] rounded-lg
                               hover:bg-[#007a7a] transition-colors duration-200
                               focus:outline-none focus:ring-2 focus:ring-[#009496] focus:ring-offset-2">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Pesquisar
                            </button>

                            @if(request('search') || request('modalidade') || request('status'))
                                <a href="{{ route('admin.processos.index', ['prefeitura_id' => request('prefeitura_id')]) }}"
                                   class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg
                          hover:bg-gray-200 transition-colors duration-200
                          focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2">
                                    Limpar filtros
                                </a>
                            @endif
                        </div>
                    </div>

                    @if(request('search') || request('modalidade') || request('status'))
                        <div class="pt-4 mt-2 border-t border-gray-200">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm text-gray-600">Filtros aplicados:</span>
                                @if(request('search'))
                                    <span class="px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                    Pesquisa: "{{ request('search') }}"
                </span>
                                @endif
                                @if(request('modalidade'))
                                    @php
                                        $modalidade = \App\Enums\ModalidadeEnum::tryFrom(request('modalidade'));
                                    @endphp
                                    @if($modalidade)
                                        <span class="px-3 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                    Modalidade: {{ $modalidade->getDisplayName() }}
                </span>
                                    @endif
                                @endif
                                @if(request('status'))
                                    @php
                                        $status = \App\Enums\ProcessoStatusEnum::tryFrom(request('status'));
                                    @endphp
                                    @if($status)
                                        <span class="px-3 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                    Status: {{ $status->label() }}
                </span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif
                </form>
            </div>
            <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-100 shadow-sm rounded-2xl">
                <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                    <div class="flex flex-col items-start justify-between lg:flex-row lg:items-center">
                        <h3 class="text-xl font-semibold text-gray-800">
                            Processos da Prefeitura: {{ $prefeituras->find(request('prefeitura_id'))->nome ?? 'Selecionada' }}
                        </h3>
                        <span class="mt-2 text-sm text-gray-500 lg:mt-0">
                    Total: {{ $processos->total() }} processos
                </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full overflow-hidden divide-y divide-gray-200 rounded-lg shadow-sm">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Status</th>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Nº Processo</th>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Nº Procedimento</th>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Tipo Contratação</th>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Tipo Procedimento</th>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Modalidade</th>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-center text-gray-600 uppercase">Ações</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($processos as $processo)
                            <tr class="transition-colors duration-200 hover:bg-gray-50/80">
                                <!-- Na seção onde mostra o status, atualize para: -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @php
                                        $status = $processo->status instanceof \App\Enums\ProcessoStatusEnum
                                            ? $processo->status
                                            : \App\Enums\ProcessoStatusEnum::tryFrom($processo->status) ?? \App\Enums\ProcessoStatusEnum::EM_ANDAMENTO;
                                    @endphp

                                    <button type="button"
                                            onclick="abrirModalStatusProcesso({{ $processo->id }}, '{{ $processo->numero_processo }}', '{{ $status->value }}')"
                                            class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full cursor-pointer transition-all duration-200 hover:opacity-90 hover:scale-105
                                                @if($status->value === 'EM_ANDAMENTO') bg-blue-100 text-blue-800 hover:bg-blue-200
                                                @elseif($status->value === 'FINALIZADO') bg-green-100 text-green-800 hover:bg-green-200
                                                @elseif($status->value === 'CANCELADO') bg-red-100 text-red-800 hover:bg-red-200
                                                @elseif($status->value === 'REPUBLICADO') bg-purple-100 text-purple-800 hover:bg-purple-200
                                                @elseif($status->value === 'ADIADO') bg-orange-100 text-orange-800 hover:bg-orange-200
                                                @endif"
                                            title="Clique para alterar o status">
                                        {{ $status->label() }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 font-mono text-sm text-gray-900 whitespace-nowrap">{{ $processo->numero_processo }}</td>
                                <td class="px-4 py-3 font-mono text-sm text-gray-900 whitespace-nowrap">{{ $processo->numero_procedimento }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $processo->tipo_contratacao_nome }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $processo->tipo_procedimento_nome }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full
                                        @if ($processo->modalidade->value === 'dispensa') bg-purple-100 text-purple-800 border border-purple-200
                                        @elseif($processo->modalidade->value === 'inexigibilidade') bg-pink-100 text-pink-800 border border-pink-200
                                        @elseif($processo->modalidade->value === 'pregão') bg-blue-100 text-blue-800 border border-blue-200
                                        @elseif($processo->modalidade->value === 'concorrência') bg-green-100 text-green-800 border border-green-200
                                        @else bg-gray-100 text-gray-800 border border-gray-200 @endif">
                                        {{ $processo->modalidade->getDisplayName() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap justify-center gap-1.5">
                                        <a href="{{ route('admin.processos.iniciar', $processo->id) }}"
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white transition-colors duration-200 bg-[#062F43] rounded-md hover:bg-[#065f8b] focus:outline-none focus:ring-2 focus:ring-[#062F43] focus:ring-offset-1"
                                           title="Iniciar processo">
{{--                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">--}}
{{--                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>--}}
{{--                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>--}}
{{--                                            </svg>--}}
                                            Iniciar
                                        </a>

                                        <a href="{{ route('admin.processos.finalizacao.finalizar', $processo->id) }}"
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white transition-colors duration-200 bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-1"
                                           title="Finalizar processo">
{{--                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">--}}
{{--                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>--}}
{{--                                            </svg>--}}
                                            Finalizar
                                        </a>

                                        @if (!($processo->modalidade == 4 && optional($processo->detalhe)->tipo_srp === 'nao'))
                                            <a href="{{ route('admin.processos.contrato.index', $processo->id) }}"
                                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-1"
                                               title="Emitir Contrato">
{{--                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">--}}
{{--                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>--}}
{{--                                                </svg>--}}
                                                Contrato
                                            </a>
                                        @endif

                                        <!-- Botões de ações especiais -->
                                        @if($processo->status && method_exists($processo->status, 'isAtivo') && $processo->status->isAtivo())
                                            <button type="button"
                                                    onclick="abrirModalRepublicarProcesso({{ $processo->id }})"
                                                    class="inline-flex items-center justify-center w-8 h-8 text-white bg-purple-600 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-1"
                                                    title="Republicar Processo">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                            </button>

                                            <button type="button"
                                                    onclick="abrirModalCancelarLicitacao({{ $processo->id }})"
                                                    class="inline-flex items-center justify-center w-8 h-8 text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1"
                                                    title="Cancelar Licitação">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                                </svg>
                                            </button>

                                            <button type="button"
                                                    onclick="abrirModalAdiarLicitacao({{ $processo->id }})"
                                                    class="inline-flex items-center justify-center w-8 h-8 text-white bg-yellow-600 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-1"
                                                    title="Adiar Licitação">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </button>
                                        @endif

                                        <!-- Botão Reverter Cancelamento - Mostrar apenas para processos cancelados -->
                                        @if($processo->status->value === 'CANCELADO')
                                            <button type="button"
                                                    onclick="reverterCancelamento({{ $processo->id }}, '{{ $processo->numero_processo }}')"
                                                    class="inline-flex items-center justify-center w-8 h-8 text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1"
                                                    title="Reverter Cancelamento">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                </svg>
                                            </button>
                                        @endif

                                        <a href="{{ route('admin.processos.edit', $processo->id) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 text-gray-600 transition-colors duration-200 rounded-md hover:bg-gray-100 hover:text-[#009496] focus:outline-none focus:ring-2 focus:ring-[#009496] focus:ring-offset-1"
                                           title="Editar processo">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>

                                        <form action="{{ route('admin.processos.destroy', $processo->id) }}" method="POST"
                                              class="inline" id="delete-form-{{ $processo->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                onclick="confirmDelete({{ $processo->id }}, '{{ $processo->numero_processo }}')"
                                                class="inline-flex items-center justify-center w-8 h-8 text-gray-600 transition-colors duration-200 rounded-md hover:bg-red-100 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-1"
                                                title="Excluir processo">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr class="bg-gray-50/50">
                                <td colspan="7" class="px-4 py-3 text-sm text-gray-700">
                                    <div class="space-y-1">
                                        <div><strong class="text-gray-900">Objeto:</strong> {!! strip_tags($processo->objeto) !!}</div>
                                        <div class="text-xs text-gray-500">Criado Por: {{ $processo->user->name ?? 'N/A' }}</div>
                                        @if($processo->status->value === 'CANCELADO')
                                            <div class="text-xs text-red-600">
                                                <strong>Cancelado em:</strong> {{ $processo->data_cancelamento ? \Carbon\Carbon::parse($processo->data_cancelamento)->format('d/m/Y') : 'N/A' }}
                                                @if($processo->motivo_cancelamento)
                                                    - {{ $processo->motivo_cancelamento }}
                                                @endif
                                            </div>
                                        @endif
                                        @if($processo->status->value === 'ADIADO')
                                            <div class="text-xs text-orange-600">
                                                <strong>Adiado para:</strong> {{ $processo->data_adiamento ? \Carbon\Carbon::parse($processo->data_adiamento)->format('d/m/Y') : 'N/A' }}
                                                @if($processo->justificativa_adiamento)
                                                    - {{ $processo->justificativa_adiamento }}
                                                @endif
                                            </div>
                                        @endif
                                        @if($processo->status->value === 'REPUBLICADO')
                                            <div class="text-xs text-purple-600">
                                                <strong>Republicado</strong>
                                                @if($processo->processo_original_id)
                                                    - Original: {{ $processo->processoOriginal->numero_processo ?? 'N/A' }}
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-sm font-medium">Nenhum processo encontrado para esta prefeitura</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($processos->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        {{ $processos->withQueryString()->links() }}
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

    <!-- Incluir SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal2-popup {
            border-radius: 16px !important;
        }
    </style>
@endsection
