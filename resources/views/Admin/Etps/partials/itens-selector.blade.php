@php
    $lotePrefix = $loteIndex !== null ? "lotes[{$loteIndex}][itens]" : 'itens';
    $containerId = $loteIndex !== null ? "itens-selecionados-lote-{$loteIndex}" : 'itens-selecionados-sem-lote';
    $buscaId = $loteIndex !== null ? "buscar_item_lote_{$loteIndex}" : 'buscar_item_global';
    $listaId = $loteIndex !== null ? "lista_itens_lote_{$loteIndex}" : 'lista_itens_global';
@endphp

<div class="mb-4 itens-selector" data-lote-index="{{ $loteIndex }}">
    <label class="block text-sm font-semibold text-[var(--ink-2)] mb-2">
        Selecionar Itens *
    </label>

    {{-- BARRA DUAL: BUSCA LOCAL E IMPORTAÇÃO PNCP --}}
    <div class="flex flex-col sm:flex-row gap-2.5 mb-3">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[var(--ink-2)]">
                <i class="fas fa-search text-xs"></i>
            </span>
            <input type="text"
                id="{{ $buscaId }}"
                placeholder="Buscar item já cadastrado no sistema..."
                class="buscar-item w-full pl-9 pr-3 py-2 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder-[var(--ink-2)]/50 transition"
                data-lista="{{ $listaId }}">
        </div>

        <button type="button" 
            onclick="abrirModalCriarItem({{ $loteIndex !== null ? $loteIndex : 'null' }})" 
            class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-[var(--ink)] bg-[var(--card)] border border-[var(--line)] hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] rounded-xl transition shadow-sm whitespace-nowrap gap-2">
            <i class="fas fa-plus"></i> Novo Item Manual
        </button>

        <button type="button" 
            onclick="openModalPncp({{ $loteIndex !== null ? $loteIndex : 'null' }})" 
            class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] hover:bg-[var(--mint-h)] rounded-xl transition-all shadow-sm hover:scale-105 whitespace-nowrap gap-2">
            <i class="fas fa-globe"></i> Novo Item (PNCP)
        </button>
    </div>

    {{-- LISTA DE ITENS LOCAL --}}
    <div id="{{ $listaId }}"
        class="hidden border border-[var(--line)] rounded-xl p-4 max-h-60 overflow-y-auto space-y-2 bg-[var(--card-2)]">
        @foreach($itens as $item)
            <label class="flex items-center space-x-3 item-option p-1.5 rounded-lg hover:bg-[var(--card)] transition-colors cursor-pointer"
                data-descricao="{{ strtolower($item->descricao_item) }}">
                
                <input type="checkbox"
                    value="{{ $item->id }}"
                    data-descricao="{{ $item->descricao_item }}"
                    data-lote-index="{{ $loteIndex }}"
                    class="item-checkbox w-4 h-4 text-[var(--mint)] focus:ring-[var(--mint)] bg-[var(--bg)] border-[var(--line)] rounded"
                    onchange="toggleItemSelecionado(this, {{ $loteIndex !== null ? $loteIndex : 'null' }})">

                <span class="text-sm text-[var(--ink)]">
                    {{ $item->descricao_item }}
                </span>
            </label>
        @endforeach
    </div>

    {{-- SELECIONADOS --}}
    <div class="mt-6">
        @if ($loteIndex === null)
            <div class="flex items-center justify-between cursor-pointer select-none mb-2" onclick="toggleSemLote()">
                <h5 class="text-sm font-semibold text-[var(--ink)]">Itens Selecionados</h5>
                <i class="fas fa-chevron-down text-[var(--ink-2)] transition-transform duration-200" id="chevron-sem-lote"></i>
            </div>
        @else
            <h5 class="text-sm font-semibold mb-2 text-[var(--ink)]">
                Itens Selecionados - Lote {{ (int)$loteIndex + 1 }}
            </h5>
        @endif

        <div id="{{ $containerId }}" class="space-y-3"></div>
    </div>
</div>