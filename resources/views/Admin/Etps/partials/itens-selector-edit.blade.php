@php
    $containerId = $loteIndex !== null ? "itens-selecionados-lote-{$loteIndex}" : 'itens-selecionados-sem-lote';
    $buscaId = $loteIndex !== null ? "buscar_item_lote_{$loteIndex}" : 'buscar_item_global';
    $listaId = $loteIndex !== null ? "lista_itens_lote_{$loteIndex}" : 'lista_itens_global';
    
    // Itens já selecionados
    $itensSelecionados = [];
    if ($loteIndex !== null && isset($lote) && $lote->itens) {
        $itensSelecionados = $lote->itens->keyBy('id');
    } elseif ($loteIndex === null && isset($etp) && $etp->itens) {
        $itensSelecionados = $etp->itens->keyBy('id');
    }
@endphp

<div class="mb-4 itens-selector" data-lote-index="{{ $loteIndex }}">
    <label class="block text-sm font-semibold text-gray-700 mb-2">
        Selecionar Itens *
    </label>

    {{-- BARRA DUAL: BUSCA LOCAL E IMPORTAÇÃO PNCP --}}
    <div class="flex flex-col sm:flex-row gap-2.5 mb-3">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fas fa-search text-xs"></i>
            </span>
            <input type="text"
                id="{{ $buscaId }}"
                placeholder="Buscar item já cadastrado no sistema..."
                class="buscar-item w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition"
                data-lista="{{ $listaId }}">
        </div>

        <button type="button" 
            onclick="openModalPncp({{ $loteIndex !== null ? $loteIndex : 'null' }})" 
            class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-white bg-[#009496] hover:bg-[#007a7a] rounded-lg transition shadow-sm whitespace-nowrap gap-2">
            <i class="fas fa-globe"></i> Novo Item (PNCP)
        </button>
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
                    {{ isset($itensSelecionados[$item->id]) ? 'checked' : '' }}
                    onchange="toggleItemSelecionado(this, {{ $loteIndex !== null ? $loteIndex : 'null' }})">

                <span class="text-sm text-gray-700">
                    {{ $item->descricao_item }}
                </span>
            </label>
        @endforeach
    </div>

    {{-- SELECIONADOS --}}
    <div class="mt-6">
        @if ($loteIndex === null)
            <div class="flex items-center justify-between cursor-pointer select-none mb-2" onclick="toggleSemLote()">
                <h5 class="text-sm font-semibold text-gray-700">Itens Selecionados</h5>
                <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200" id="chevron-sem-lote"></i>
            </div>
        @else
            <h5 class="text-sm font-semibold mb-2 text-gray-700">
                Itens Selecionados 
                {{ is_numeric($loteIndex) ? "- Lote " . ((int)$loteIndex + 1) : '' }}
            </h5>
        @endif

        <div id="{{ $containerId }}" class="space-y-3">
            @if($loteIndex !== null && isset($lote) && $lote->itens)
                @foreach($lote->itens as $item)
                    @include('Admin.Etps.partials.item-selecionado-edit', [
                        'item' => $item,
                        'loteIndex' => $loteIndex,
                        'namePrefix' => "lotes[{$loteIndex}][itens]"
                    ])
                @endforeach
            @elseif($loteIndex === null && isset($etp) && $etp->itens)
                @foreach($etp->itens as $item)
                    @include('Admin.Etps.partials.item-selecionado-edit', [
                        'item' => $item,
                        'loteIndex' => null,
                        'namePrefix' => 'itens'
                    ])
                @endforeach
            @endif
        </div>
    </div>
</div>