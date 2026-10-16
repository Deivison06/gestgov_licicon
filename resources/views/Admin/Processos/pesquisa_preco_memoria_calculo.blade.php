@extends('layouts.app')
@section('page-title', 'Memória de Cálculo (IA)')
@section('page-subtitle', 'Importe a planilha gerada pela pesquisa de preços via IA')

@section('content')
<div class="px-6 pb-10">

    {{-- CABEÇALHO --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.processos.iniciar', $processo->id) }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Voltar ao Processo
            </a>
            <div class="text-gray-300">›</div>
            <span class="text-sm font-semibold text-gray-700">Memória de Cálculo (IA)</span>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-xs text-emerald-800">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-800">
        <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
    </div>
    @endif

    {{-- CARD DO PROCESSO --}}
    <div class="mb-6 p-4 bg-white border border-gray-200 rounded-xl shadow-sm flex items-center gap-5">
        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
            <i class="fas fa-calculator text-lg"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Processo</p>
            <p class="text-sm font-bold text-gray-900 truncate">{{ $processo->numero_processo }}</p>
            <p class="text-xs text-gray-500 truncate">{!! strip_tags($processo->objeto) !!}</p>
        </div>
        <div class="text-right text-xs text-gray-500">
            <span class="font-bold">{{ $etpItens->count() }}</span> {{ Str::plural('item', $etpItens->count()) }} no ETP
        </div>
    </div>

    {{-- UPLOAD --}}
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
        <p class="text-xs text-blue-800 mb-3">
            <i class="fas fa-info-circle mr-1"></i>
            Envie a planilha <strong>MEMÓRIA DE CÁLCULO</strong> gerada pela pesquisa de preços via IA.
            O número de itens da planilha precisa ser igual ao número de itens do ETP vinculado a este processo
            (o item 1 da planilha corresponde ao 1º item do ETP, o item 2 ao 2º, e assim por diante).
            Importar novamente substitui os dados anteriores.
        </p>
        <form action="{{ route('admin.processos.pesquisa_preco_memoria_calculo.store', $processo->id) }}"
              method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
            @csrf
            <input type="file" name="memoria_calculo_excel" accept=".xlsx,.xls" required
                   class="text-xs text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 file:cursor-pointer cursor-pointer">
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-all shadow-sm">
                <i class="fas fa-file-upload"></i> Importar
            </button>
        </form>
    </div>

    {{-- PREVIEW DOS ITENS IMPORTADOS --}}
    @if($memoriaItens->isEmpty())
        <div class="py-16 flex flex-col items-center justify-center text-gray-400 bg-white rounded-xl border border-gray-200">
            <i class="fas fa-box-open text-4xl mb-3 opacity-30"></i>
            <p class="text-sm font-medium">Nenhuma memória de cálculo importada ainda.</p>
            <p class="text-xs mt-1">Envie a planilha acima para preencher a Seção 8 do relatório.</p>
        </div>
    @else
    <div class="space-y-4" x-data="{ aberto: null }">
        @foreach($memoriaItens as $item)
        @php $etpItem = $etpItens->get($item->ordem - 1); @endphp
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <button type="button" @click="aberto = (aberto === {{ $item->id }} ? null : {{ $item->id }})"
                    class="w-full flex items-center justify-between gap-3 p-4 text-left hover:bg-gray-50 transition-all">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex-shrink-0">
                        {{ $item->ordem }}
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate">
                            @if($item->lote)<span class="text-indigo-600">Lote {{ $item->lote }} ·</span> @endif
                            Item {{ $item->numero_item ?? $item->ordem }} — {{ $item->titulo ?? $etpItem?->descricao_item ?? '—' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ count($item->fontes) }} {{ Str::plural('fonte', count($item->fontes)) }} consultada{{ count($item->fontes) === 1 ? '' : 's' }}
                            @if($etpItem)
                                · ETP: {{ Str::limit($etpItem->descricao_item, 60) }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <span class="text-sm font-bold text-emerald-700">
                        R$ {{ number_format($item->valor_adotado, 2, ',', '.') }}
                    </span>
                    <i class="fas text-gray-400" :class="aberto === {{ $item->id }} ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </div>
            </button>

            <div x-show="aberto === {{ $item->id }}" x-cloak class="border-t border-gray-100 p-4 bg-gray-50 space-y-3">
                @foreach($item->fontes as $fonte)
                <div class="p-3 bg-white border border-gray-200 rounded-lg text-xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="font-bold text-gray-800">
                            Fonte {{ $fonte['ordem'] ?? '-' }}
                            @if(($fonte['tipo'] ?? null) === 'painel_tce')
                                · Painel TCE/PI
                                @if(!empty($fonte['orgao_nome'])) · {{ $fonte['orgao_nome'] }}@endif
                                @if(!empty($fonte['uf'])) — {{ $fonte['uf'] }}@endif
                            @elseif(($fonte['tipo'] ?? null) === 'fornecedor_local')
                                · Fornecedor local
                            @else
                                · {{ $fonte['orgao_nome'] ?? 'Outra fonte' }}
                            @endif
                        </span>
                        <span class="font-bold text-gray-900">
                            R$ {{ number_format($fonte['valor_unitario'] ?? 0, 2, ',', '.') }}
                        </span>
                    </div>

                    @if(!empty($fonte['descricao']))
                    <p class="text-gray-600 mb-1">{{ $fonte['descricao'] }}</p>
                    @endif

                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-gray-500">
                        @if(!empty($fonte['qtd_comprada']))
                            <span>Qtd. comprada: {{ $fonte['qtd_comprada'] }} {{ $fonte['unidade_comprada'] }}</span>
                        @endif
                        @if(!empty($fonte['data_referencia']))
                            <span>Data: {{ $fonte['data_referencia'] }}</span>
                        @endif
                        @if(!empty($fonte['processo_origem']))
                            <span>Processo: {{ $fonte['processo_origem'] }}</span>
                        @endif
                        @if(!empty($fonte['fornecedor_nome']))
                            <span>Fornecedor: {{ $fonte['fornecedor_nome'] }}@if(!empty($fonte['fornecedor_cnpj'])) · CNPJ {{ $fonte['fornecedor_cnpj'] }}@endif</span>
                        @endif
                        @if(!empty($fonte['identificador']))
                            <span>Identificador: {{ $fonte['identificador'] }}</span>
                        @endif
                    </div>

                    @if(!empty($fonte['link']))
                    <a href="{{ $fonte['link'] }}" target="_blank" rel="noopener" class="inline-block mt-1 text-blue-600 hover:underline truncate max-w-full">
                        {{ $fonte['link'] }}
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
