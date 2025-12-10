@extends('layouts.app')

@section('page-title', 'Contrato - ' . $processo->numero_processo)
@section('page-subtitle', 'Gerar contrato do processo licitatório')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>

    {{-- JSON com as unidades para o JS --}}
    @php
        $unidadesData = $processo->prefeitura->unidades->map(function ($unidade) {
            return [
                'id' => $unidade->id,
                'nome' => $unidade->nome,
                'servidor_responsavel' => $unidade->servidor_responsavel,
                'numero_portaria' => $unidade->numero_portaria,
                'data_portaria' => $unidade->data_portaria,
            ];
        });

        // Carregar contratações existentes
        $contratacoes = \App\Models\LoteContratado::where('processo_id', $processo->id)
            ->with(['lote', 'vencedor'])
            ->get()
            ->groupBy('vencedor_id');
    @endphp
    <script>
        const unidadesAssinantes = @json($unidadesData);
        const processoId = {{ $processo->id }};
        const csrfToken = '{{ csrf_token() }}';
        let itensDisponiveis = [];
        let itensSelecionados = new Map();
    </script>
    {{-- Fim JSON --}}

    <div class="py-6 md:py-8">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            <!-- Cabeçalho da Página -->
            <div class="mb-6 md:mb-8">
                <div class="flex flex-col justify-between md:flex-row md:items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 md:text-3xl">Gerar Contrato</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Processo: {{ $processo->numero_processo }} • Modalidade: {{ $processo->modalidade->getDisplayName() }}
                        </p>
                    </div>
                    <div class="flex mt-4 space-x-3 md:mt-0">
                        <a href="{{ route('admin.processos.show', $processo->id) }}"
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            ← Voltar ao Processo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Painéis Principais -->
            <div class="space-y-6 md:space-y-8">

                <!-- Painel 1: Resumo do Processo -->
                <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 mr-3 bg-white rounded-lg shadow-sm">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800">Resumo do Processo</h2>
                                <p class="text-sm text-gray-600">Informações básicas do processo licitatório</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Prefeitura -->
                        <div class="flex items-center p-4 mb-6 rounded-lg bg-gray-50">
                            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Prefeitura Municipal</div>
                                <div class="text-lg font-semibold text-gray-900">{{ $processo->prefeitura->nome }}</div>
                            </div>
                        </div>

                        <!-- Grid de Informações -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <!-- Card Modalidade -->
                            <div class="p-4 bg-white border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 p-2 bg-blue-100 rounded-lg">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-500">Modalidade</div>
                                        <div class="text-base font-semibold text-gray-900">{{ $processo->modalidade->getDisplayName() }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Número Processo -->
                            <div class="p-4 bg-white border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 p-2 bg-green-100 rounded-lg">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-500">Nº Processo</div>
                                        <div class="font-mono text-base font-semibold text-gray-900">{{ $processo->numero_processo }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Número Procedimento -->
                            <div class="p-4 bg-white border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 p-2 bg-purple-100 rounded-lg">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-500">Nº Procedimento</div>
                                        <div class="font-mono text-base font-semibold text-gray-900">{{ $processo->numero_procedimento }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Tipo Contratação -->
                            <div class="p-4 bg-white border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 p-2 bg-orange-100 rounded-lg">
                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-500">Tipo Contratação</div>
                                        <div class="text-base font-semibold text-gray-900">{{ $processo->tipo_contratacao_nome }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Tipo Procedimento -->
                            <div class="p-4 bg-white border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 p-2 bg-indigo-100 rounded-lg">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-500">Tipo Procedimento</div>
                                        <div class="text-base font-semibold text-gray-900">{{ $processo->tipo_procedimento_nome }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Objeto -->
                        <div class="p-4 mt-6 rounded-lg bg-gray-50">
                            <div class="flex items-center mb-3">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                                <h4 class="text-sm font-semibold text-gray-700">Objeto do Processo</h4>
                            </div>
                            <div class="text-gray-700">{!! strip_tags($processo->objeto) !!}</div>
                        </div>
                    </div>
                </div>

                <!-- Painel 2: Contratação de Itens -->
                @if($processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO)
                    <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-100">
                            <div class="flex flex-col justify-between md:flex-row md:items-center">
                                <div class="flex items-center">
                                    <div class="flex items-center justify-center w-10 h-10 mr-3 bg-white rounded-lg shadow-sm">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-800">Contratação de Itens</h2>
                                        <p class="text-sm text-gray-600">Gerencie os itens a serem contratados</p>
                                    </div>
                                </div>
                                <button type="button"
                                        onclick="abrirModalContratacao()"
                                        class="flex items-center px-4 py-2 mt-3 text-sm font-medium text-white bg-purple-600 rounded-lg md:mt-0 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Adicionar Contratação
                                </button>
                            </div>
                        </div>

                        <!-- Área de Mensagens -->
                        <div id="message-container-contratacao" class="p-4"></div>

                        <!-- Conteúdo -->
                        <div class="p-6">
                            @if($contratacoes->isEmpty())
                            <!-- Estado vazio -->
                            <div class="px-6 py-8 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="mb-2 text-sm font-medium">Nenhuma contratação realizada</p>
                                    <p class="text-xs">Clique em "Adicionar Contratação" para começar</p>
                                </div>
                            </div>
                            @else
                            <!-- Lista de Vencedores com Acordeões -->
                            <div class="space-y-4" id="contratacoes-container">
                                @foreach($processo->vencedores as $vencedor)
                                    @php
                                        $vencedorContratacoes = $contratacoes[$vencedor->id] ?? collect([]);
                                        $vencedorIdSafe = str_replace(['.', '#', '[', ']'], '', (string) $vencedor->id);
                                        $accordionId = "accordion-vencedor-{$vencedorIdSafe}";
                                    @endphp

                                    @if($vencedorContratacoes->isNotEmpty())
                                        @php
                                            // Calcular totais para este vencedor
                                            $totalVencedor = $vencedorContratacoes->sum('valor_total');
                                            $totalQuantidadeVencedor = $vencedorContratacoes->sum('quantidade_contratada');
                                            $contratacoesCount = $vencedorContratacoes->count();
                                        @endphp

                                        <!-- Acordeão do Vencedor -->
                                        <div class="transition-colors duration-200 border border-gray-200 rounded-lg hover:border-gray-300">
                                            <!-- Cabeçalho do Acordeão -->
                                            <div class="p-4">
                                                <button type="button"
                                                        class="flex items-center justify-between w-full text-left"
                                                        onclick="toggleAccordionVencedor('{{ $accordionId }}')">
                                                    <div class="flex items-center">
                                                        <div class="flex items-center justify-center w-8 h-8 mr-3 bg-purple-100 rounded-lg">
                                                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <h3 class="text-sm font-semibold text-gray-900">{{ $vencedor->razao_social }}</h3>
                                                            <p class="text-xs text-gray-500">{{ $vencedor->cnpj }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center space-x-4">
                                                        <!-- Resumo Financeiro -->
                                                        <div class="hidden text-right sm:block">
                                                            <div class="text-xs font-medium text-gray-500">Total: R$ {{ number_format($totalVencedor, 2, ',', '.') }}</div>
                                                            <div class="text-xs text-gray-500">{{ $contratacoesCount }} item(ns)</div>
                                                        </div>
                                                        <!-- Ícone do Acordeão -->
                                                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" id="icon-{{ $accordionId }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    </div>
                                                </button>
                                            </div>

                                            <!-- Conteúdo do Acordeão -->
                                            <div id="{{ $accordionId }}" class="hidden border-t border-gray-200">
                                                <!-- Tabela de Itens do Vencedor -->
                                                <div class="p-4">
                                                    <div class="overflow-x-auto">
                                                        <table class="w-full">
                                                            <thead>
                                                                <tr class="text-xs font-medium text-gray-500 uppercase border-b border-gray-200">
                                                                    <th class="px-4 py-3 text-left">Item/Lote</th>
                                                                    <th class="px-4 py-3 text-left">Quantidade</th>
                                                                    <th class="px-4 py-3 text-left">Valor Unit.</th>
                                                                    <th class="px-4 py-3 text-left">Total</th>
                                                                    <th class="px-4 py-3 text-left">Status</th>
                                                                    <th class="px-4 py-3 text-center">Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-gray-200">
                                                                @foreach($vencedorContratacoes as $contratacao)
                                                                <tr class="contratacao-row hover:bg-gray-50" data-contratacao-id="{{ $contratacao->id }}">
                                                                    <td class="px-4 py-3">
                                                                        <div class="flex items-start">
                                                                            <div class="flex-shrink-0 w-2 h-2 mt-2 mr-2 bg-blue-500 rounded-full"></div>
                                                                            <div>
                                                                                <div class="text-sm font-medium text-gray-900">
                                                                                    {{ $contratacao->lote->item }}
                                                                                    @if($contratacao->lote->lote)
                                                                                    <span class="text-xs text-gray-500">(Lote: {{ $contratacao->lote->lote }})</span>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="max-w-xs text-xs text-gray-500 truncate">
                                                                                    {{ $contratacao->lote->descricao }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="px-4 py-3 text-sm text-gray-900">
                                                                        <div class="flex flex-col">
                                                                            <span class="font-semibold">
                                                                                {{ number_format($contratacao->quantidade_contratada, 2, ',', '.') }}
                                                                            </span>
                                                                            <span class="text-xs text-gray-500">
                                                                                Disp.: {{ number_format($contratacao->lote->quantidade_disponivel ?? $contratacao->lote->quantidade, 2, ',', '.') }}
                                                                            </span>
                                                                        </div>
                                                                    </td>
                                                                    <td class="px-4 py-3 text-sm text-gray-900">
                                                                        <span class="font-medium">
                                                                            R$ {{ number_format($contratacao->valor_unitario, 2, ',', '.') }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                                                        <span class="text-green-600">
                                                                            R$ {{ number_format($contratacao->valor_total, 2, ',', '.') }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="px-4 py-3">
                                                                        @if($contratacao->status === 'CONTRATADO')
                                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                                            </svg>
                                                                            CONTRATADO
                                                                        </span>
                                                                        @elseif($contratacao->status === 'PENDENTE')
                                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                                            </svg>
                                                                            PENDENTE
                                                                        </span>
                                                                        @else
                                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                                            {{ $contratacao->status }}
                                                                        </span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-4 py-3">
                                                                        <div class="flex justify-center space-x-2">
                                                                            <button type="button"
                                                                                    onclick="editarContratacaoIndividual({{ $contratacao->id }})"
                                                                                    class="p-1.5 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400"
                                                                                    title="Editar">
                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                                </svg>
                                                                            </button>
                                                                            @if($contratacao->status === 'PENDENTE')
                                                                            <button type="button"
                                                                                    onclick="confirmarContratacao({{ $contratacao->id }})"
                                                                                    class="p-1.5 text-green-600 bg-green-100 rounded-md hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-400"
                                                                                    title="Confirmar">
                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                                </svg>
                                                                            </button>
                                                                            @endif
                                                                            <button type="button"
                                                                                    onclick="removerContratacao({{ $contratacao->id }})"
                                                                                    class="p-1.5 text-red-600 bg-red-100 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-400"
                                                                                    title="Remover">
                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                            <!-- Rodapé do Vencedor -->
                                                            <tfoot class="bg-gray-50">
                                                                <tr>
                                                                    <td colspan="3" class="px-4 py-3 text-sm font-semibold text-right text-gray-700">
                                                                        Total do Vencedor:
                                                                    </td>
                                                                    <td class="px-4 py-3 text-sm font-semibold text-green-700">
                                                                        R$ {{ number_format($totalVencedor, 2, ',', '.') }}
                                                                    </td>
                                                                    <td colspan="2"></td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            @endif

                            <!-- Totais -->
                            @php
                                $totalContratacoes = \App\Models\LoteContratado::where('processo_id', $processo->id)->count();
                                $totalQuantidade = \App\Models\LoteContratado::where('processo_id', $processo->id)->sum('quantidade_contratada');
                                $totalValor = \App\Models\LoteContratado::where('processo_id', $processo->id)->sum('valor_total');
                            @endphp
                            <div class="grid grid-cols-1 gap-4 mt-6 md:grid-cols-3">
                                <div class="p-4 bg-white border border-gray-200 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 p-2 bg-purple-100 rounded-lg">
                                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-500">Total de Contratações</div>
                                            <div class="text-2xl font-bold text-purple-600 total-contratacoes">{{ $totalContratacoes }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 bg-white border border-gray-200 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 p-2 bg-green-100 rounded-lg">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-2.5L21 15m-10-5h.01M15 15h.01"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-500">Quantidade Total</div>
                                            <div class="text-2xl font-bold text-green-600 total-quantidade">{{ number_format($totalQuantidade, 2, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 bg-white border border-gray-200 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 p-2 bg-blue-100 rounded-lg">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-500">Valor Total</div>
                                            <div class="text-2xl font-bold text-blue-600 total-valor">R$ {{ number_format($totalValor, 2, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <!-- Painel 3: Geração de Contrato -->
                <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-green-100">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 mr-3 bg-white rounded-lg shadow-sm">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800">Gerar Contrato</h2>
                                <p class="text-sm text-gray-600">Gere os documentos necessários para o processo</p>
                            </div>
                        </div>
                    </div>

                    <!-- Área de Mensagens -->
                    <div id="message-container" class="p-4"></div>

                    <!-- Documentos -->
                    <div class="p-6">
                        <div class="space-y-4">


                            @foreach ($documentos as $tipo => $doc)
                                @php
                                    $documentoGerado = $processo->documentos
                                        ->where('tipo_documento', $tipo)
                                        ->first();
                                    $accordionId = "accordion-{$tipo}";
                                @endphp

                                <div class="transition-colors duration-200 border border-gray-200 rounded-lg hover:border-gray-300">
                                    <!-- Cabeçalho do Documento -->
                                    <div class="p-4">
                                        <div class="flex flex-col justify-between md:flex-row md:items-center">
                                            <div class="flex items-start mb-3 md:mb-0 md:items-center">
                                                <div class="flex-shrink-0 flex items-center justify-center w-8 h-8 mr-3 rounded-lg {{ $doc['cor'] }}">
                                                    @if($tipo === 'contrato')
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    @elseif($tipo === 'extrato')
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    @else
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                                    </svg>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-semibold text-gray-900">{{ $doc['titulo'] }}</h4>
                                                    @if ($documentoGerado)
                                                        <span class="inline-flex items-center mt-1 text-xs text-green-600">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            Gerado em {{ \Carbon\Carbon::parse($documentoGerado->gerado_em)->format('d/m/Y H:i') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Controles -->
                                            <div class="flex flex-col space-y-2 md:flex-row md:items-center md:space-y-0 md:space-x-3">
                                                <div class="relative">
                                                    <input type="date"
                                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                                           id="data_contrato_{{ $tipo }}"
                                                           value="{{ $documentoGerado->data_selecionada ?? now()->format('Y-m-d') }}">
                                                </div>

                                                <div class="flex space-x-2">
                                                    <button type="button"
                                                            onclick="gerarContrato('{{ $processo->id }}', document.getElementById('data_contrato_{{ $tipo }}').value, event, '{{ $tipo }}')"
                                                            class="flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        Gerar
                                                    </button>

                                                    @if ($documentoGerado)
                                                        <a href="{{ route('admin.processo.contrato.download', ['processo' => $processo->id, 'tipo' => $tipo]) }}"
                                                           download
                                                           class="flex items-center justify-center p-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                           title="Download">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                            </svg>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Configurações Avançadas -->
                                        @if ($doc['requer_assinatura'] || !empty($doc['campos']))
                                            <div class="pt-4 mt-4 border-t border-gray-200">
                                                <button type="button"
                                                        class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900"
                                                        onclick="toggleAccordion('{{ $accordionId }}')">
                                                    <svg class="w-4 h-4 mr-2 transition-transform duration-200" id="icon-{{ $accordionId }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                    <span>
                                                        @if ($doc['requer_assinatura'] && !empty($doc['campos']))
                                                            Configurar Campos e Assinantes
                                                        @elseif($doc['requer_assinatura'])
                                                            Configurar Assinantes
                                                        @elseif(!empty($doc['campos']))
                                                            Configurar Campos
                                                        @endif
                                                    </span>
                                                </button>

                                                <!-- Conteúdo do Acordeão -->
                                                <div id="{{ $accordionId }}" class="hidden mt-4 space-y-6">
                                                    <!-- Seção de Assinantes -->
                                                    @if ($doc['requer_assinatura'])
                                                        <div class="p-4 rounded-lg bg-gray-50">
                                                            <div class="flex items-center justify-between mb-4">
                                                                <h5 class="text-sm font-semibold text-gray-700">Assinantes do Contrato</h5>
                                                                <button type="button"
                                                                        onclick="adicionarAssinante('{{ $tipo }}')"
                                                                        class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 flex items-center">
                                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                                    </svg>
                                                                    Adicionar Assinante
                                                                </button>
                                                            </div>

                                                            <div id="assinantes-container-{{ $tipo }}" class="space-y-4">
                                                                <div class="p-4 bg-white border border-gray-200 rounded-lg assinante-item">
                                                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                                        <!-- Unidade -->
                                                                        <div>
                                                                            <label class="block mb-1 text-xs font-medium text-gray-600">
                                                                                Unidade
                                                                            </label>
                                                                            <select name="assinante_unidade[]"
                                                                                    class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 unidade-select"
                                                                                    onchange="updateResponsavel(this, '{{ $tipo }}')">
                                                                                <option value="">Selecione a Unidade</option>
                                                                                @foreach ($processo->prefeitura->unidades as $unidade)
                                                                                    <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>

                                                                        <!-- Responsável -->
                                                                        <div>
                                                                            <label class="block mb-1 text-xs font-medium text-gray-600">
                                                                                Responsável
                                                                            </label>
                                                                            <input type="text"
                                                                                name="assinante_responsavel[]"
                                                                                placeholder="Nome do Responsável"
                                                                                readonly
                                                                                class="w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-lg responsavel-input">
                                                                        </div>

                                                                        <!-- Número da Portaria -->
                                                                        <div>
                                                                            <label class="block mb-1 text-xs font-medium text-gray-600">
                                                                                Nº Portaria
                                                                            </label>
                                                                            <input type="text"
                                                                                name="assinante_portaria[]"
                                                                                placeholder="Número da Portaria"
                                                                                readonly
                                                                                class="w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-lg portaria-input">
                                                                        </div>

                                                                        <!-- Data da Portaria -->
                                                                        <div>
                                                                            <label class="block mb-1 text-xs font-medium text-gray-600">
                                                                                Data Portaria
                                                                            </label>
                                                                            <input type="text"
                                                                                name="assinante_data_portaria[]"
                                                                                placeholder="Data da Portaria"
                                                                                readonly
                                                                                class="w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-lg data-portaria-input">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <!-- Seção de Campos do Contrato -->
                                                    @if (!empty($doc['campos']))
                                                        <div class="p-4 rounded-lg bg-gray-50">
                                                            <h5 class="mb-3 text-sm font-semibold text-gray-700">Campos do Contrato</h5>
                                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                                @foreach ($doc['campos'] as $campo)

                                                                    @if ($campo === 'numero_contrato')
                                                                        <div>
                                                                            <label for="numero_contrato" class="block mb-1 text-xs font-medium text-gray-600">
                                                                                Número do Contrato
                                                                            </label>
                                                                            <input type="text"
                                                                                id="numero_contrato"
                                                                                name="numero_contrato"
                                                                                placeholder="Ex: 001/2024"
                                                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg 
                                                                                focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                                                        </div>

                                                                    @elseif($campo === 'data_assinatura_contrato')
                                                                        <div>
                                                                            <label for="data_assinatura_contrato" class="block mb-1 text-xs font-medium text-gray-600">
                                                                                Data de Assinatura
                                                                            </label>
                                                                            <input type="date"
                                                                                id="data_assinatura_contrato"
                                                                                name="data_assinatura_contrato"
                                                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg 
                                                                                focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                                                        </div>

                                                                    @elseif($campo === 'numero_extrato')
                                                                        <div>
                                                                            <label for="numero_extrato" class="block mb-1 text-xs font-medium text-gray-600">
                                                                                Número do Extrato
                                                                            </label>
                                                                            <input type="text"
                                                                                id="numero_extrato"
                                                                                name="numero_extrato"
                                                                                placeholder="Ex: EXT/001/2024"
                                                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg 
                                                                                focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                                                        </div>

                                                                    @elseif($campo === 'comarca')
                                                                        <div>
                                                                            <label for="comarca" class="block mb-1 text-xs font-medium text-gray-600">
                                                                                Comarca
                                                                            </label>
                                                                            <input type="text"
                                                                                id="comarca"
                                                                                name="comarca"
                                                                                placeholder="Ex: Comarca de São Paulo"
                                                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg 
                                                                                focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                                                        </div>

                                                                    {{-- ✔ Substituindo <x-form-field> (textarea) --}}
                                                                    @elseif($campo === 'fonte_recurso')
                                                                        <div class="sm:col-span-2">
                                                                            <label for="fonte_recurso" class="block mb-1 text-xs font-medium text-gray-600">
                                                                                Fonte de Recurso
                                                                            </label>
                                                                            <textarea
                                                                                id="fonte_recurso"
                                                                                name="fonte_recurso"
                                                                                rows="5"
                                                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg 
                                                                                focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                                                                        </div>

                                                                    {{-- ✔ Substituindo <x-form-field> (select) --}}
                                                                    @elseif($campo === 'subcontratacao')
                                                                        <div>
                                                                            <label for="subcontratacao" class="block mb-1 text-xs font-medium text-gray-600">
                                                                                Subcontratação?
                                                                            </label>
                                                                            <select
                                                                                id="subcontratacao"
                                                                                name="subcontratacao"
                                                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg 
                                                                                focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                                                                <option value="" selected>Selecione uma opção</option>
                                                                                <option value="1">Sim</option>
                                                                                <option value="0">Não</option>
                                                                            </select>
                                                                        </div>
                                                                    @endif

                                                                @endforeach
                                                            </div>

                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Contratação em Lote -->
    <div id="contratacaoModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="fecharModalContratacao()"></div>

            <div class="inline-block w-full max-w-6xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                <div class="px-6 py-4 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900" id="contratacaoModalTitle">
                                Contratar Itens
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">Selecione os itens que deseja contratar e informe as quantidades</p>
                        </div>
                        <button type="button"
                                onclick="fecharModalContratacao()"
                                class="text-gray-400 hover:text-gray-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <form id="contratacaoForm">
                    <div class="px-6 py-4">
                        <input type="hidden" id="contratacaoId" value="">
                        <input type="hidden" name="processo_id" value="{{ $processo->id }}">

                        <!-- Passo 1: Selecionar Vencedor -->
                        <div class="mb-6" id="step-1">
                            <label for="vencedor_id" class="block mb-2 text-sm font-medium text-gray-700">
                                Selecione o Vencedor *
                            </label>
                            <select id="vencedor_id"
                                    name="vencedor_id"
                                    required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                    onchange="carregarItensParaContratacao(this.value)">
                                <option value="">Selecione o Vencedor</option>
                                @foreach($processo->vencedores as $vencedor)
                                <option value="{{ $vencedor->id }}">{{ $vencedor->razao_social }} ({{ $vencedor->cnpj }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Passo 2: Lista de Itens -->
                        <div id="step-2" class="hidden">
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-semibold text-gray-700">Itens Disponíveis para Contratação</h4>
                                    <div class="flex items-center space-x-2">
                                        <button type="button"
                                                onclick="selecionarTodosItens()"
                                                class="px-3 py-1 text-xs text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">
                                            Selecionar Todos
                                        </button>
                                        <button type="button"
                                                onclick="desmarcarTodosItens()"
                                                class="px-3 py-1 text-xs text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">
                                            Desmarcar Todos
                                        </button>
                                    </div>
                                </div>

                                <!-- Tabela de Itens -->
                                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="w-12 px-4 py-3">
                                                    <input type="checkbox"
                                                           id="select-all-items"
                                                           class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                                           onclick="toggleTodosCheckboxes()">
                                                </th>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                    Item
                                                </th>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                    Descrição
                                                </th>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                    Quantidade Disponível
                                                </th>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                    Valor Unitário
                                                </th>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                    Quantidade a Contratar
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200" id="itens-lista">
                                            <!-- Itens serão carregados dinamicamente -->
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-4 text-sm text-gray-500" id="contador-selecionados">
                                    Nenhum item selecionado
                                </div>
                            </div>

                            <!-- Botões de Navegação -->
                            <div class="flex justify-between pt-4 border-t border-gray-200">
                                <button type="button"
                                        onclick="voltarParaSelecaoVencedor()"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    ← Voltar
                                </button>
                                <button type="button"
                                        onclick="avancarParaRevisao()"
                                        class="px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-md shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    Revisar Contratações →
                                </button>
                            </div>
                        </div>

                        <!-- Passo 3: Revisão -->
                        <div id="step-3" class="hidden">
                            <div class="mb-6">
                                <h4 class="mb-4 text-sm font-semibold text-gray-700">Revisão das Contratações</h4>

                                <!-- Informações Resumidas -->
                                <div class="p-4 mb-4 border border-gray-200 rounded-lg bg-gray-50">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        <div>
                                            <div class="text-xs font-medium text-gray-500">Vencedor Selecionado</div>
                                            <div class="text-sm font-semibold text-gray-800" id="info-vencedor-revisao"></div>
                                        </div>
                                        <div>
                                            <div class="text-xs font-medium text-gray-500">Total de Itens</div>
                                            <div class="text-sm font-semibold text-purple-600" id="info-total-itens">0</div>
                                        </div>
                                        <div>
                                            <div class="text-xs font-medium text-gray-500">Valor Total</div>
                                            <div class="text-sm font-semibold text-green-600" id="info-valor-total-revisao">R$ 0,00</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabela de Revisão -->
                                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase" colspan="2">
                                                    Item
                                                </th>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                    Quantidade
                                                </th>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                    Valor Unitário
                                                </th>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                    Valor Total
                                                </th>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">
                                                    Ações
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200" id="contratacoes-revisao">
                                            <!-- Itens selecionados -->
                                        </tbody>
                                        <tfoot class="bg-gray-50">
                                            <tr>
                                                <td colspan="4" class="px-4 py-3 text-sm font-semibold text-right text-gray-700">
                                                    Total Geral:
                                                </td>
                                                <td class="px-4 py-3 text-sm font-semibold text-gray-900" id="total-geral">
                                                    R$ 0,00
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Observações -->
                            <div class="mb-6">
                                <label for="observacoes_gerais" class="block mb-2 text-sm font-medium text-gray-700">
                                    Observações Gerais
                                </label>
                                <textarea id="observacoes_gerais"
                                        name="observacoes_gerais"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                        placeholder="Observações sobre estas contratações..."></textarea>
                            </div>

                            <!-- Botões de Navegação -->
                            <div class="flex justify-between pt-4 border-t border-gray-200">
                                <button type="button"
                                        onclick="voltarParaSelecaoItens()"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    ← Voltar
                                </button>
                                <button type="button"
                                        onclick="salvarMultiplasContratacoes()"
                                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    ✅ Salvar Contratações
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Funções para acordeão
        function toggleAccordion(id) {
            const element = document.getElementById(id);
            const icon = document.getElementById(`icon-${id}`);

            if (element.classList.contains('hidden')) {
                element.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                element.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }

        // Funções para acordeão de vencedores
        function toggleAccordionVencedor(id) {
            const element = document.getElementById(id);
            const icon = document.getElementById(`icon-${id}`);

            if (element.classList.contains('hidden')) {
                element.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                element.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }

        // Funções do Modal de Contratação em Lote
        function abrirModalContratacao() {
            // Verificar se é pregão
            const modalidadePregão = @json($processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO);

            if (!modalidadePregão) {
                alert('Esta funcionalidade está disponível apenas para Pregão Eletrônico');
                return;
            }
            document.getElementById('contratacaoModalTitle').textContent = 'Contratar Itens';
            document.getElementById('contratacaoId').value = '';

            // Resetar formulário
            document.getElementById('contratacaoForm').reset();

            // Resetar estados
            itensDisponiveis = [];
            itensSelecionados.clear();

            // Mostrar apenas o passo 1
            document.getElementById('step-1').classList.remove('hidden');
            document.getElementById('step-2').classList.add('hidden');
            document.getElementById('step-3').classList.add('hidden');

            // Resetar selects
            document.getElementById('vencedor_id').value = '';

            const modal = document.getElementById('contratacaoModal');
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        }

        async function carregarItensParaContratacao(vencedorId) {
            const step1 = document.getElementById('step-1');
            const step2 = document.getElementById('step-2');
            const itensLista = document.getElementById('itens-lista');

            if (!vencedorId) {
                step2.classList.add('hidden');
                return;
            }

            try {
                showMessageContratacao('Carregando itens disponíveis...', 'info');

                const response = await fetch(`/admin/processos/${processoId}/vencedores/${vencedorId}/lotes-disponiveis`);
                const data = await response.json();

                if (data.success) {
                    itensDisponiveis = data.lotes;

                    if (!itensDisponiveis || itensDisponiveis.length === 0) {
                        itensLista.innerHTML = `
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    Nenhum item disponível para este vencedor
                                </td>
                            </tr>
                        `;
                        showMessageContratacao('Nenhum item disponível para este vencedor', 'warning');
                    } else {
                        let html = '';

                        itensDisponiveis.forEach((lote, index) => {
                            html += `
                                <tr class="item-row hover:bg-gray-50" data-lote-id="${lote.id}">
                                    <td class="px-4 py-3">
                                        <input type="checkbox"
                                            class="w-4 h-4 text-purple-600 border-gray-300 rounded item-checkbox focus:ring-purple-500"
                                            data-lote-id="${lote.id}"
                                            onchange="toggleItemSelecionado(this, ${index})">
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            ${lote.item}
                                            ${lote.lote ? `<span class="text-xs text-gray-500">(Lote: ${lote.lote})</span>` : ''}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="max-w-xs text-sm text-gray-500 truncate">
                                            ${lote.descricao || 'Não informado'}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-900">
                                                ${parseFloat(lote.quantidade_disponivel || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                Utilizado: ${parseFloat(lote.quantidade_utilizada || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                Total: ${parseFloat(lote.quantidade_total || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        R$ ${parseFloat(lote.vl_unit || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="relative">
                                            <input type="number"
                                                id="quantidade_${lote.id}"
                                                data-lote-id="${lote.id}"
                                                min="0.01"
                                                max="${lote.quantidade_disponivel || 0}"
                                                step="0.01"
                                                disabled
                                                class="w-32 px-3 py-1 text-sm border border-gray-300 rounded-md shadow-sm quantidade-input focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                                onchange="atualizarQuantidadeItem(this)"
                                                placeholder="0,00">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <span class="text-xs text-gray-400">${lote.unidade || 'un'}</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });

                        itensLista.innerHTML = html;
                        showMessageContratacao(`${itensDisponiveis.length} itens carregados com sucesso`, 'success');
                    }

                    // Mostrar passo 2
                    step1.classList.add('hidden');
                    step2.classList.remove('hidden');

                    // Atualizar contador
                    atualizarContadorSelecionados();

                } else {
                    showMessageContratacao('Erro ao carregar itens disponíveis: ' + (data.message || 'Erro desconhecido'), 'error');
                }
            } catch (error) {
                console.error('Erro ao carregar itens:', error);
                showMessageContratacao('Erro ao carregar itens disponíveis. Verifique sua conexão e tente novamente.', 'error');
            }
        }

        function toggleItemSelecionado(checkbox, index) {
            const loteId = checkbox.dataset.loteId;
            const quantidadeInput = document.getElementById(`quantidade_${loteId}`);

            if (checkbox.checked) {
                quantidadeInput.disabled = false;
                quantidadeInput.focus();
                quantidadeInput.value = '1.00';

                // Encontrar o lote correto
                const lote = itensDisponiveis.find(l => l.id == loteId);
                if (lote) {
                    itensSelecionados.set(loteId, {
                        quantidade: 1,
                        lote: lote,
                        input: quantidadeInput
                    });
                }
            } else {
                quantidadeInput.disabled = true;
                quantidadeInput.value = '';
                itensSelecionados.delete(loteId);
            }

            atualizarContadorSelecionados();
            atualizarCheckboxMestre();
        }

        function atualizarQuantidadeItem(input) {
            const loteId = input.dataset.loteId;
            const quantidade = parseFloat(input.value) || 0;

            if (itensSelecionados.has(loteId)) {
                const item = itensSelecionados.get(loteId);
                item.quantidade = quantidade;
                itensSelecionados.set(loteId, item);
            }

            const lote = itensDisponiveis.find(l => l.id == loteId);
            if (lote && quantidade > (lote.quantidade_disponivel || 0)) {
                input.value = lote.quantidade_disponivel || 0;
                showMessageContratacao(`Quantidade máxima: ${lote.quantidade_disponivel || 0}`, 'warning');

                if (itensSelecionados.has(loteId)) {
                    const item = itensSelecionados.get(loteId);
                    item.quantidade = lote.quantidade_disponivel || 0;
                    itensSelecionados.set(loteId, item);
                }
            }
        }

        function selecionarTodosItens() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach((checkbox, index) => {
                if (!checkbox.checked) {
                    checkbox.checked = true;
                    toggleItemSelecionado(checkbox, index);
                }
            });
        }

        function desmarcarTodosItens() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach((checkbox, index) => {
                if (checkbox.checked) {
                    checkbox.checked = false;
                    toggleItemSelecionado(checkbox, index);
                }
            });
        }

        function toggleTodosCheckboxes() {
            const masterCheckbox = document.getElementById('select-all-items');
            const checkboxes = document.querySelectorAll('.item-checkbox');

            if (masterCheckbox.checked) {
                selecionarTodosItens();
            } else {
                desmarcarTodosItens();
            }
        }

        function atualizarCheckboxMestre() {
            const masterCheckbox = document.getElementById('select-all-items');
            const checkboxes = document.querySelectorAll('.item-checkbox');
            const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;

            if (checkedCount === 0) {
                masterCheckbox.checked = false;
                masterCheckbox.indeterminate = false;
            } else if (checkedCount === checkboxes.length) {
                masterCheckbox.checked = true;
                masterCheckbox.indeterminate = false;
            } else {
                masterCheckbox.checked = false;
                masterCheckbox.indeterminate = true;
            }
        }

        function atualizarContadorSelecionados() {
            const contador = document.getElementById('contador-selecionados');
            const count = itensSelecionados.size;

            if (count === 0) {
                contador.textContent = 'Nenhum item selecionado';
                contador.className = 'mt-4 text-sm text-gray-500';
            } else {
                contador.textContent = `${count} item(ns) selecionado(s)`;
                contador.className = 'mt-4 text-sm font-medium text-purple-600';
            }
        }

        function avancarParaRevisao() {
            if (itensSelecionados.size === 0) {
                showMessageContratacao('Selecione pelo menos um item para continuar', 'warning');
                return;
            }

            const step2 = document.getElementById('step-2');
            const step3 = document.getElementById('step-3');
            const revisaoTbody = document.getElementById('contratacoes-revisao');

            const vencedorSelect = document.getElementById('vencedor_id');
            const selectedOption = vencedorSelect.options[vencedorSelect.selectedIndex];
            const vencedorInfo = selectedOption ? selectedOption.text : 'Não selecionado';

            document.getElementById('info-vencedor-revisao').textContent = vencedorInfo;
            document.getElementById('info-total-itens').textContent = itensSelecionados.size;

            let html = '';
            let totalGeral = 0;

            itensSelecionados.forEach((item, loteId) => {
                const valorUnitario = parseFloat(item.lote.vl_unit || 0);
                const valorTotal = item.quantidade * valorUnitario;
                totalGeral += valorTotal;

                html += `
                    <tr class="bg-white border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-4 py-3" colspan="2">
                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    <div class="flex items-center justify-center flex-shrink-0 w-6 h-6 mr-3 bg-purple-100 rounded-full">
                                        <svg class="w-3 h-3 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            ${item.lote.item}
                                            ${item.lote.lote ? `<span class="text-xs text-gray-500">(Lote: ${item.lote.lote})</span>` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900">
                                ${item.quantidade.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                            </div>
                            <div class="text-xs text-gray-500">
                                ${item.lote.unidade || 'un'}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            R$ ${valorUnitario.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-green-700">
                            R$ ${valorTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button type="button"
                                    onclick="removerItemRevisao('${loteId}')"
                                    class="px-3 py-1.5 text-xs font-medium text-white bg-red-500 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                                Remover
                            </button>
                        </td>
                    </tr>
                `;
            });

            revisaoTbody.innerHTML = html;
            document.getElementById('total-geral').textContent =
                `R$ ${totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

            document.getElementById('info-valor-total-revisao').textContent =
                `R$ ${totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

            step2.classList.add('hidden');
            step3.classList.remove('hidden');
        }

        function removerItemRevisao(loteId) {
            itensSelecionados.delete(loteId);
            const checkbox = document.querySelector(`.item-checkbox[data-lote-id="${loteId}"]`);
            if (checkbox) {
                checkbox.checked = false;
                toggleItemSelecionado(checkbox, -1);
            }
            avancarParaRevisao();
        }

        function voltarParaSelecaoItens() {
            document.getElementById('step-3').classList.add('hidden');
            document.getElementById('step-2').classList.remove('hidden');
        }

        function voltarParaSelecaoVencedor() {
            document.getElementById('step-2').classList.add('hidden');
            document.getElementById('step-1').classList.remove('hidden');
        }

        async function salvarMultiplasContratacoes() {
            if (itensSelecionados.size === 0) {
                showMessageContratacao('Selecione pelo menos um item para contratar', 'error');
                return;
            }

            const vencedorId = document.getElementById('vencedor_id').value;
            const observacoes = document.getElementById('observacoes_gerais').value;

            const contratacoes = [];
            let isValid = true;

            itensSelecionados.forEach((item, loteId) => {
                if (item.quantidade <= 0) {
                    isValid = false;
                    showMessageContratacao(`Quantidade inválida para o item ${item.lote.item}`, 'error');
                    return;
                }

                if (item.quantidade > (item.lote.quantidade_disponivel || 0)) {
                    isValid = false;
                    showMessageContratacao(
                        `Quantidade excede a disponível para ${item.lote.item} (Máx: ${item.lote.quantidade_disponivel || 0})`,
                        'error'
                    );
                    return;
                }

                contratacoes.push({
                    vencedor_id: vencedorId,
                    lote_id: loteId,
                    quantidade_contratada: item.quantidade,
                    observacao: observacoes
                });
            });

            if (!isValid) return;

            try {
                showMessageContratacao('Salvando contratações...', 'info');

                const response = await fetch(`/admin/processos/${processoId}/contratacoes-em-lote`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        contratacoes: contratacoes
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showMessageContratacao(data.message, 'success');
                    fecharModalContratacao();
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    if (data.errors && data.errors.length > 0) {
                        showMessageContratacao(data.errors.join('<br>'), 'error');
                    } else {
                        showMessageContratacao(data.message || 'Erro ao salvar contratações', 'error');
                    }
                }
            } catch (error) {
                console.error('Erro ao salvar contratações:', error);
                showMessageContratacao('Erro de conexão ao salvar contratações. Verifique sua internet e tente novamente.', 'error');
            }
        }

        function fecharModalContratacao() {
            const modal = document.getElementById('contratacaoModal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }

        async function editarContratacaoIndividual(id) {
            try {
                showMessageContratacao('Carregando dados da contratação...', 'info');

                const response = await fetch(`/admin/processos/${processoId}/contratacao/${id}/edit`);
                const data = await response.json();

                if (data.success) {
                    abrirModalEdicaoIndividual(data.contratacao, data.disponivel_atual);
                } else {
                    showMessageContratacao(data.message, 'error');
                }
            } catch (error) {
                console.error('Erro ao carregar contratação:', error);
                showMessageContratacao('Erro ao carregar contratação para edição', 'error');
            }
        }

        function abrirModalEdicaoIndividual(contratacao, disponivelAtual) {
            // Fechar outros modais se existirem
            const modalExistente = document.getElementById('modal-edicao-individual');
            if (modalExistente) {
                modalExistente.remove();
            }

            const modalHtml = `
                <div id="modal-edicao-individual" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
                    <div class="w-full max-w-md bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b">
                            <h3 class="text-lg font-semibold">Editar Contratação</h3>
                            <p class="text-sm text-gray-600">${contratacao.lote.item}</p>
                        </div>
                        <div class="p-6">
                            <div class="mb-4">
                                <label class="block mb-2 text-sm font-medium text-gray-700">
                                    Quantidade Disponivel: ${parseFloat(disponivelAtual).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                </label>
                                <input type="number"
                                    id="quantidade-edicao"
                                    value="${parseFloat(contratacao.quantidade_contratada).toFixed(2)}"
                                    min="0.01"
                                    max="${parseFloat(disponivelAtual) + parseFloat(contratacao.quantidade_contratada)}"
                                    step="0.01"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div class="mb-4">
                                <label class="block mb-2 text-sm font-medium text-gray-700">
                                    Observação
                                </label>
                                <textarea id="observacao-edicao"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                    rows="3">${contratacao.observacao || ''}</textarea>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 px-6 py-4 rounded-b-lg bg-gray-50">
                            <button type="button" onclick="fecharModalEdicao()"
                                class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                                Cancelar
                            </button>
                            <button type="button" onclick="salvarEdicaoIndividual(${contratacao.id})"
                                class="px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                Salvar Alterações
                            </button>
                        </div>
                    </div>
                </div>
            `;

            // Adicionar modal ao DOM
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        async function salvarEdicaoIndividual(contratacaoId) {
            const quantidade = parseFloat(document.getElementById('quantidade-edicao').value);
            const observacao = document.getElementById('observacao-edicao').value;

            if (isNaN(quantidade) || quantidade <= 0) {
                showMessageContratacao('Quantidade inválida', 'error');
                return;
            }

            try {
                showMessageContratacao('Atualizando contratação...', 'info');

                const response = await fetch(`/admin/processos/${processoId}/contratacao/${contratacaoId}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        quantidade_contratada: quantidade,
                        observacao: observacao
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showMessageContratacao(data.message, 'success');
                    fecharModalEdicao();
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showMessageContratacao(data.message, 'error');
                }
            } catch (error) {
                console.error('Erro ao salvar edição:', error);
                showMessageContratacao('Erro ao salvar edição da contratação', 'error');
            }
        }

        function fecharModalEdicao() {
            const modal = document.getElementById('modal-edicao-individual');
            if (modal) {
                modal.remove();
            }
        }

        async function confirmarContratacao(id) {
            if (!confirm('Deseja confirmar esta contratação?')) {
                return;
            }

            try {
                showMessageContratacao('Confirmando contratação...', 'info');

                const response = await fetch(`/admin/processos/${processoId}/contratacao/${id}/confirmar`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showMessageContratacao(data.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showMessageContratacao(data.message, 'error');
                }
            } catch (error) {
                console.error('Erro ao confirmar contratação:', error);
                showMessageContratacao('Erro ao confirmar contratação', 'error');
            }
        }

        async function removerContratacao(id) {
            if (!confirm('Tem certeza que deseja remover esta contratação?')) {
                return;
            }

            try {
                showMessageContratacao('Removendo contratação...', 'info');

                const response = await fetch(`/admin/processos/${processoId}/contratacao/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showMessageContratacao(data.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showMessageContratacao(data.message, 'error');
                }
            } catch (error) {
                console.error('Erro ao remover contratação:', error);
                showMessageContratacao('Erro ao remover contratação', 'error');
            }
        }

        function showMessageContratacao(message, type) {
            const container = document.getElementById('message-container-contratacao');
            const bgColor = type === 'success' ? 'bg-green-100 border-green-400' :
                        type === 'warning' ? 'bg-yellow-100 border-yellow-400' :
                        type === 'info' ? 'bg-blue-100 border-blue-400' :
                        'bg-red-100 border-red-400';
            const textColor = type === 'success' ? 'text-green-800' :
                            type === 'warning' ? 'text-yellow-800' :
                            type === 'info' ? 'text-blue-800' :
                            'text-red-800';
            const icon = type === 'success' ? '✅' :
                        type === 'warning' ? '⚠️' :
                        type === 'info' ? 'ℹ️' :
                        '❌';

            container.innerHTML = `
                <div class="p-4 mb-4 border-l-4 rounded-md ${bgColor} ${textColor}">
                    <div class="flex items-center">
                        <span class="mr-2 text-lg">${icon}</span>
                        <span class="font-semibold">${message}</span>
                    </div>
                </div>
            `;

            setTimeout(() => {
                if (container.innerHTML.includes(message)) {
                    container.innerHTML = '';
                }
            }, 6000);
        }

        // =================================================================
        // NOVAS FUNÇÕES PARA GERENCIAR CAMPOS DO CONTRATO
        // =================================================================

        // Carregar dados salvos do contrato
        document.addEventListener('DOMContentLoaded', function() {
            carregarDadosContratoSalvos();
            inicializarEventosCampos();
        });

        async function carregarDadosContratoSalvos() {
            try {
                const response = await fetch(`/admin/processos/${processoId}/contrato/dados`);
                const data = await response.json();

                if (data.success && data.dados) {
                    preencherCamposContrato(data.dados);
                }
            } catch (error) {
                console.error('Erro ao carregar dados do contrato:', error);
            }
        }

        function preencherCamposContrato(dados) {
            // Preencher campos do contrato
            if (dados.numero_contrato) {
                const input = document.getElementById('numero_contrato');
                if (input) input.value = dados.numero_contrato;
            }

            if (dados.data_assinatura_contrato) {
                const input = document.getElementById('data_assinatura_contrato');
                if (input) {
                    // Converter data do formato YYYY-MM-DD para o formato do input
                    const data = new Date(dados.data_assinatura_contrato);
                    if (!isNaN(data.getTime())) {
                        input.value = data.toISOString().split('T')[0];
                    }
                }
            }

            if (dados.numero_extrato) {
                const input = document.getElementById('numero_extrato');
                if (input) input.value = dados.numero_extrato;
            }

            if (dados.comarca) {
                const input = document.getElementById('comarca');
                if (input) input.value = dados.comarca;
            }

            // Preencher outros campos se existirem
            if (dados.fonte_recurso) {
                const textarea = document.querySelector('[name="fonte_recurso"]');
                if (textarea) textarea.value = dados.fonte_recurso;
            }

            if (dados.subcontratacao !== undefined) {
                const select = document.querySelector('[name="subcontratacao"]');
                if (select) select.value = dados.subcontratacao.toString();
            }
        }

        function inicializarEventosCampos() {
            // Adicionar eventos de blur para salvar automaticamente
            const camposParaSalvar = [
                'numero_contrato',
                'data_assinatura_contrato', 
                'numero_extrato',
                'comarca',
                'fonte_recurso',
                'subcontratacao'
            ];

            camposParaSalvar.forEach(campoId => {
                const elemento = document.getElementById(campoId) || 
                            document.querySelector(`[name="${campoId}"]`);
                
                if (elemento) {
                    elemento.addEventListener('blur', function() {
                        salvarCampoContrato(campoId, this.value);
                    });
                }
            });
        }

        async function salvarCampoContrato(campo, valor) {
            try {
                const response = await fetch(`/admin/processos/${processoId}/contrato/salvar-campo`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        campo: campo,
                        valor: valor
                    })
                });

                const data = await response.json();

                if (!data.success) {
                    console.warn(`Erro ao salvar campo ${campo}:`, data.message);
                }
            } catch (error) {
                console.error('Erro ao salvar campo do contrato:', error);
            }
        }

        // =================================================================
        // FUNÇÕES PARA GERENCIAR ASSINANTES
        // =================================================================

        function adicionarAssinante(tipoDocumento) {
            const container = document.getElementById(`assinantes-container-${tipoDocumento}`);
            const novoAssinante = document.createElement('div');
            novoAssinante.className = 'p-4 mb-3 bg-white border border-gray-200 rounded-lg assinante-item';
            novoAssinante.innerHTML = `
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Unidade -->
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600">
                            Unidade
                        </label>
                        <select name="assinante_unidade[]"
                                class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 unidade-select"
                                onchange="updateResponsavel(this, '${tipoDocumento}')">
                            <option value="">Selecione a Unidade</option>
                            @foreach ($processo->prefeitura->unidades as $unidade)
                                <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Responsável -->
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600">
                            Responsável
                        </label>
                        <input type="text"
                            name="assinante_responsavel[]"
                            placeholder="Nome do Responsável"
                            readonly
                            class="w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-lg responsavel-input">
                    </div>

                    <!-- Número da Portaria -->
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600">
                            Nº Portaria
                        </label>
                        <input type="text"
                            name="assinante_portaria[]"
                            placeholder="Número da Portaria"
                            readonly
                            class="w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-lg portaria-input">
                    </div>

                    <!-- Data da Portaria -->
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600">
                            Data Portaria
                        </label>
                        <input type="text"
                            name="assinante_data_portaria[]"
                            placeholder="Data da Portaria"
                            readonly
                            class="w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-lg data-portaria-input">
                    </div>
                </div>

                <!-- Botão Remover -->
                <div class="flex justify-end mt-3">
                    <button type="button"
                            onclick="removerAssinante(this, '${tipoDocumento}')"
                            class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Remover Assinante
                    </button>
                </div>
            `;
            container.appendChild(novoAssinante);
        }

        function removerAssinante(botao, tipoDocumento) {
            const container = document.getElementById(`assinantes-container-${tipoDocumento}`);
            const assinanteDiv = botao.closest('.assinante-item');
            const todosAssinantes = container.querySelectorAll('.assinante-item');

            if (todosAssinantes.length > 1) {
                assinanteDiv.style.transition = 'opacity 0.3s ease';
                assinanteDiv.style.opacity = '0';
                setTimeout(() => assinanteDiv.remove(), 300);
            } else {
                showMessage('É obrigatório ter pelo menos um assinante.', 'error');
            }
        }

        function updateResponsavel(select, tipoDocumento) {
            const selectedUnidadeId = select.value;
            const selectedUnidade = unidadesAssinantes.find(u => u.id == selectedUnidadeId);
            const assinanteDiv = select.closest('.assinante-item');

            if (selectedUnidade) {
                const responsavelInput = assinanteDiv.querySelector('.responsavel-input');
                if (responsavelInput) {
                    responsavelInput.value = selectedUnidade.servidor_responsavel || '';
                }

                const portariaInput = assinanteDiv.querySelector('.portaria-input');
                if (portariaInput) {
                    portariaInput.value = selectedUnidade.numero_portaria || '';
                }

                const dataPortariaInput = assinanteDiv.querySelector('.data-portaria-input');
                if (dataPortariaInput) {
                    dataPortariaInput.value = selectedUnidade.data_portaria || '';
                }
            } else {
                const responsavelInput = assinanteDiv.querySelector('.responsavel-input');
                if (responsavelInput) responsavelInput.value = '';

                const portariaInput = assinanteDiv.querySelector('.portaria-input');
                if (portariaInput) portariaInput.value = '';

                const dataPortariaInput = assinanteDiv.querySelector('.data-portaria-input');
                if (dataPortariaInput) dataPortariaInput.value = '';
            }
        }

        function getCamposContrato() {
            const campos = {};

            const numeroContratoInput = document.getElementById('numero_contrato');
            if (numeroContratoInput && numeroContratoInput.value.trim() !== '') {
                campos.numero_contrato = numeroContratoInput.value.trim();
            }

            const dataAssinaturaInput = document.getElementById('data_assinatura_contrato');
            if (dataAssinaturaInput && dataAssinaturaInput.value) {
                campos.data_assinatura_contrato = dataAssinaturaInput.value;
            }

            const numeroExtratoInput = document.getElementById('numero_extrato');
            if (numeroExtratoInput && numeroExtratoInput.value.trim() !== '') {
                campos.numero_extrato = numeroExtratoInput.value.trim();
            }

            const comarcaInput = document.getElementById('comarca');
            if (comarcaInput && comarcaInput.value.trim() !== '') {
                campos.comarca = comarcaInput.value.trim();
            }

            // Adicionar outros campos
            const fonteRecursoInput = document.querySelector('[name="fonte_recurso"]');
            if (fonteRecursoInput && fonteRecursoInput.value.trim() !== '') {
                campos.fonte_recurso = fonteRecursoInput.value.trim();
            }

            const subcontratacaoInput = document.querySelector('[name="subcontratacao"]');
            if (subcontratacaoInput && subcontratacaoInput.value.trim() !== '') {
                campos.subcontratacao = subcontratacaoInput.value;
            }

            return campos;
        }

        function getAssinantes(tipoDocumento) {
            const container = document.getElementById(`assinantes-container-${tipoDocumento}`);
            const selects = container.querySelectorAll('select[name="assinante_unidade[]"]');
            const assinantes = [];

            selects.forEach((select) => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption.value) {
                    const unidade = unidadesAssinantes.find(u => u.id == select.value);
                    if (unidade) {
                        const assinanteDiv = select.closest('.assinante-item');
                        const responsavelInput = assinanteDiv.querySelector('input[name="assinante_responsavel[]"]');
                        const portariaInput = assinanteDiv.querySelector('input[name="assinante_portaria[]"]');
                        const dataPortariaInput = assinanteDiv.querySelector('input[name="assinante_data_portaria[]"]');

                        assinantes.push({
                            unidade_id: unidade.id,
                            unidade_nome: unidade.nome,
                            responsavel: responsavelInput?.value || unidade.servidor_responsavel,
                            numero_portaria: portariaInput?.value || unidade.numero_portaria,
                            data_portaria: dataPortariaInput?.value || unidade.data_portaria,
                        });
                    }
                }
            });
            return assinantes;
        }

        function gerarContrato(processoId, data, event, tipo = 'contrato') {
            if (!data) {
                showMessage('Por favor, selecione uma data antes de gerar o contrato.', 'error');
                return;
            }

            const assinantes = getAssinantes(tipo);

            if (assinantes.length < 1) {
                showMessage('Você deve adicionar pelo menos um assinante antes de gerar o contrato.', 'error');
                return;
            }

            const camposContrato = getCamposContrato();
            const assinantesJson = JSON.stringify(assinantes);
            const assinantesEncoded = encodeURIComponent(assinantesJson);
            const camposContratoJson = JSON.stringify(camposContrato);
            const camposContratoEncoded = encodeURIComponent(camposContratoJson);

            let url = `/admin/contrato/processos/${processoId}/pdf?data=${data}&tipo=${tipo}`;

            if (assinantes.length > 0) {
                url += `&assinantes=${assinantesEncoded}`;
            }

            if (Object.keys(camposContrato).length > 0) {
                url += `&campos=${camposContratoEncoded}`;
            }

            const button = event.currentTarget;
            const originalText = button.textContent;

            button.textContent = 'Gerando...';
            button.disabled = true;

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    showMessage('Erro ao gerar contrato: ' + error, 'error');
                })
                .finally(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                });
        }

        function showMessage(message, type) {
            const container = document.getElementById('message-container');
            const bgColor = type === 'success' ? 'bg-green-100 border-green-400' : 'bg-red-100 border-red-400';
            const textColor = type === 'success' ? 'text-green-800' : 'text-red-800';
            const icon = type === 'success' ? '✅' : '❌';

            container.innerHTML = `
                <div class="p-4 mb-4 border-l-4 rounded-md ${bgColor} ${textColor}">
                    <div class="flex items-center">
                        <span class="mr-2 text-lg">${icon}</span>
                        <span class="font-semibold">${message}</span>
                    </div>
                </div>
            `;

            setTimeout(() => {
                container.innerHTML = '';
            }, 6000);
        }
    </script>

    @endsection
