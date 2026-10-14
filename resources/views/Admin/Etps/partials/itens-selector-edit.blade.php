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
    <label class="block text-sm font-semibold text-[#a2b9b5] mb-2">
        Selecionar Itens *
    </label>

    {{-- BARRA DUAL: BUSCA LOCAL E IMPORTAÇÃO PNCP --}}
    <div class="flex flex-col sm:flex-row gap-2.5 mb-3">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#a2b9b5]">
                <i class="fas fa-search text-xs"></i>
            </span>
            <input type="text"
                id="{{ $buscaId }}"
                placeholder="Buscar item já cadastrado no sistema..."
                class="buscar-item w-full pl-9 pr-3 py-2 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] placeholder-[#a2b9b5]/50 transition"
                data-lista="{{ $listaId }}">
        </div>

        <button type="button" 
            onclick="abrirModalCriarItem({{ $loteIndex !== null ? $loteIndex : 'null' }})" 
            class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-[#e6f0ee] bg-[#10201e] border border-[#24403c] hover:bg-[#142825] hover:text-[#2DC197] rounded-xl transition shadow-sm whitespace-nowrap gap-2">
            <i class="fas fa-plus"></i> Novo Item Manual
        </button>

        <button type="button" 
            onclick="openModalPncp({{ $loteIndex !== null ? $loteIndex : 'null' }})" 
            class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-[#041d1a] bg-[#2DC197] hover:bg-[#26aa84] rounded-xl transition-all shadow-sm hover:scale-105 whitespace-nowrap gap-2">
            <i class="fas fa-globe"></i> Novo Item (PNCP)
        </button>
    </div>

    {{-- LISTA DE ITENS --}}
    <div id="{{ $listaId }}"
        class="hidden border border-[#24403c] rounded-xl p-4 max-h-60 overflow-y-auto space-y-2 bg-[#0d1b19]">
        @foreach($itens as $item)
            <label class="flex items-center space-x-3 item-option p-1.5 rounded-lg hover:bg-[#10201e] transition-colors cursor-pointer"
                data-descricao="{{ strtolower($item->descricao_item) }}">
                
                <input type="checkbox"
                    value="{{ $item->id }}"
                    data-descricao="{{ $item->descricao_item }}"
                    data-lote-index="{{ $loteIndex }}"
                    class="item-checkbox w-4 h-4 text-[#2DC197] focus:ring-[#2DC197] bg-[#0a1514] border-[#24403c] rounded"
                    {{ isset($itensSelecionados[$item->id]) ? 'checked' : '' }}
                    onchange="toggleItemSelecionado(this, {{ $loteIndex !== null ? $loteIndex : 'null' }})">

                <span class="text-sm text-[#e6f0ee]">
                    {{ $item->descricao_item }}
                </span>
            </label>
        @endforeach
    </div>

    {{-- SELECIONADOS --}}
    <div class="mt-6">
        @if ($loteIndex === null)
            <div class="flex items-center justify-between cursor-pointer select-none mb-2" onclick="toggleSemLote()">
                <h5 class="text-sm font-semibold text-[#e6f0ee]">Itens Selecionados</h5>
                <i class="fas fa-chevron-down text-[#a2b9b5] transition-transform duration-200" id="chevron-sem-lote"></i>
            </div>
        @else
            <h5 class="text-sm font-semibold mb-2 text-[#e6f0ee]">
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