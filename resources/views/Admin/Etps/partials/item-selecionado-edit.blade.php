<div class="flex items-center justify-between bg-white border rounded-lg p-3 shadow-sm" 
     id="item-{{ $loteIndex !== null ? 'itens-selecionados-lote-'.$loteIndex : 'itens-selecionados-sem-lote' }}-{{ $item->id }}">
    <div class="flex-1">
        <p class="text-sm font-medium text-gray-800 mb-2">{{ $item->descricao_item }}</p>

        <div class="flex gap-3">
            <input type="hidden" name="{{ $namePrefix }}[{{ $item->id }}][item_id]" value="{{ $item->id }}">

            <input type="text"
                name="{{ $namePrefix }}[{{ $item->id }}][unidade]"
                value="{{ $item->pivot->unidade }}"
                placeholder="Ex: Unidade, Pacote, Caixa..."
                class="px-2 py-1 border border-gray-300 rounded text-sm w-28"
                required>

            <input type="number"
                name="{{ $namePrefix }}[{{ $item->id }}][quantidade]"
                value="{{ $item->pivot->quantidade }}"
                placeholder="Qtd"
                min="1"
                required
                class="px-2 py-1 border border-gray-300 rounded text-sm w-20">
        </div>
    </div>

    <button type="button"
        class="ml-4 text-red-500 hover:text-red-700 font-bold"
        onclick="removerItemSelecionado({{ $item->id }}, {{ $loteIndex ?: 'null' }})">
        ✕
    </button>
</div>