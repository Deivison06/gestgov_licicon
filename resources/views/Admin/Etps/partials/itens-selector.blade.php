@php
    $lotePrefix = $loteIndex !== null ? "lotes[{$loteIndex}][itens]" : 'itens';
    $containerId = $loteIndex !== null ? "itens-selecionados-lote-{$loteIndex}" : 'itens-selecionados-sem-lote';
    $buscaId = $loteIndex !== null ? "buscar_item_lote_{$loteIndex}" : 'buscar_item_global';
    $listaId = $loteIndex !== null ? "lista_itens_lote_{$loteIndex}" : 'lista_itens_global';
@endphp

<div class="mb-4 itens-selector" data-lote-index="{{ $loteIndex }}">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Selecionar Itens *
    </label>

    {{-- BUSCA --}}
    <div class="mb-3">
        <input type="text"
            id="{{ $buscaId }}"
            placeholder="Buscar item..."
            class="buscar-item w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496]"
            data-lista="{{ $listaId }}">
    </div>

    {{-- LISTA DE ITENS --}}
    <div id="{{ $listaId }}"
        class="hidden border border-gray-200 rounded-lg p-4 max-h-60 overflow-y-auto space-y-2 bg-gray-50">
        @foreach($itens as $item)
            <label class="flex items-center space-x-3 item-option"
                data-descricao="{{ strtolower($item->descricao_item) }}">
                
                <input type="checkbox"
                    value="{{ $item->id }}"
                    data-descricao="{{ $item->descricao_item }}"
                    data-lote-index="{{ $loteIndex }}"
                    class="item-checkbox w-4 h-4 text-[#009496]"
                    onchange="toggleItemSelecionado(this, {{ $loteIndex ?: 'null' }})">

                <span class="text-sm text-gray-700">
                    {{ $item->descricao_item }}
                </span>
            </label>
        @endforeach
    </div>

    {{-- SELECIONADOS --}}
    <div class="mt-6">
        <h5 class="text-sm font-semibold mb-2 text-gray-700">
            Itens Selecionados 
{{ is_numeric($loteIndex) ? "- Lote " . ((int)$loteIndex + 1) : '' }}

        </h5>

        <div id="{{ $containerId }}" class="space-y-3"></div>
    </div>
</div>