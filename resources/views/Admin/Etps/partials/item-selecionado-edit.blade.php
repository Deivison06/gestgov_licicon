@php
    $unidadeSalva = strtoupper(trim($item->pivot->unidade));
    $unidadesPadrao = ['UN', 'KG', 'CX', 'PCT', 'L', 'M', 'RES', 'SAC', 'FR', 'KIT', 'JG', 'FD', 'GL', 'RL'];
@endphp

<div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:border-[#009496]/30 transition overflow-hidden" 
     id="item-{{ $loteIndex !== null ? 'itens-selecionados-lote-'.$loteIndex : 'itens-selecionados-sem-lote' }}-{{ $item->id }}">
    
    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between p-4 gap-4 cursor-pointer" 
         onclick="toggleAccordionItem('{{ $loteIndex !== null ? 'itens-selecionados-lote-'.$loteIndex : 'itens-selecionados-sem-lote' }}-{{ $item->id }}')" 
         title="Clique para expandir/recolher">
        
        <div class="flex items-center gap-3 flex-1 min-w-0">
            <i class="fas fa-grip-vertical text-gray-300 hover:text-gray-500 cursor-grab px-1 drag-handle" title="Arrastar para reordenar" onclick="event.stopPropagation()"></i>
            <span class="item-numero inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#009496]/10 text-[#009496] text-xs font-bold flex-shrink-0 border border-[#009496]/20">0</span>
            <div class="min-w-0 flex-1 flex items-center gap-2">
                <p class="desc-item-{{ $item->id }} text-sm font-semibold text-gray-800 leading-relaxed truncate" title="{{ $item->descricao_item }}">{{ $item->descricao_item }}</p>
            </div>
            <button type="button" class="text-gray-400 hover:text-[#009496] focus:outline-none flex-shrink-0 transition-transform duration-200" id="chevron-item-{{ $loteIndex !== null ? 'itens-selecionados-lote-'.$loteIndex : 'itens-selecionados-sem-lote' }}-{{ $item->id }}">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        
        <div class="flex items-center gap-3 flex-shrink-0" onclick="event.stopPropagation()">
            <div class="flex flex-col gap-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Unidade</span>
                <select name="{{ $namePrefix }}[{{ $item->id }}][unidade]" required class="rounded-lg border-gray-300 text-xs py-1.5 focus:border-[#009496] focus:ring-[#009496] w-32 bg-gray-50/50 hover:bg-gray-50 transition">
                    <option value="UN" {{ $unidadeSalva === 'UN' ? 'selected' : '' }}>UN (Unidade)</option>
                    <option value="KG" {{ $unidadeSalva === 'KG' ? 'selected' : '' }}>KG (Quilograma)</option>
                    <option value="CX" {{ $unidadeSalva === 'CX' ? 'selected' : '' }}>CX (Caixa)</option>
                    <option value="PCT" {{ $unidadeSalva === 'PCT' ? 'selected' : '' }}>PCT (Pacote)</option>
                    <option value="L" {{ $unidadeSalva === 'L' ? 'selected' : '' }}>L (Litro)</option>
                    <option value="M" {{ $unidadeSalva === 'M' ? 'selected' : '' }}>M (Metro)</option>
                    <option value="RES" {{ $unidadeSalva === 'RES' ? 'selected' : '' }}>RES (Resma)</option>
                    <option value="SAC" {{ $unidadeSalva === 'SAC' ? 'selected' : '' }}>SAC (Saco)</option>
                    <option value="FR" {{ $unidadeSalva === 'FR' ? 'selected' : '' }}>FR (Frasco)</option>
                    <option value="KIT" {{ $unidadeSalva === 'KIT' ? 'selected' : '' }}>KIT (Kit)</option>
                    <option value="JG" {{ $unidadeSalva === 'JG' ? 'selected' : '' }}>JG (Jogo)</option>
                    <option value="FD" {{ $unidadeSalva === 'FD' ? 'selected' : '' }}>FD (Fardo)</option>
                    <option value="GL" {{ $unidadeSalva === 'GL' ? 'selected' : '' }}>GL (Galão)</option>
                    <option value="RL" {{ $unidadeSalva === 'RL' ? 'selected' : '' }}>RL (Rolo)</option>
                    @if ($unidadeSalva && !in_array($unidadeSalva, $unidadesPadrao))
                        <option value="{{ $unidadeSalva }}" selected>{{ $unidadeSalva }}</option>
                    @endif
                </select>
            </div>
            
            <div class="flex flex-col gap-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Quantidade</span>
                <input type="number" name="{{ $namePrefix }}[{{ $item->id }}][quantidade]" value="{{ $item->pivot->quantidade }}" placeholder="Qtd" min="1" required class="rounded-lg border-gray-300 text-xs py-1.5 focus:border-[#009496] focus:ring-[#009496] w-20 bg-gray-50/50 hover:bg-gray-50 transition">
            </div>

            <div class="flex items-end self-end pb-0.5 gap-2">
                <input type="hidden" name="{{ $namePrefix }}[{{ $item->id }}][item_id]" value="{{ $item->id }}">
                <button type="button" onclick="openModalItemQuickEdit({{ $item->id }}, document.querySelector('#body-item-{{ $loteIndex !== null ? 'itens-selecionados-lote-'.$loteIndex : 'itens-selecionados-sem-lote' }}-{{ $item->id }} .desc-item-{{ $item->id }}').textContent)" class="btn-edit-item-{{ $item->id }} inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:text-[#009496] hover:bg-[#009496]/10 transition" title="Editar Descrição Completa">
                    <i class="fas fa-edit text-sm"></i>
                </button>
                <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:text-red-600 hover:bg-red-50 focus:outline-none transition" onclick="removerItemSelecionado({{ $item->id }}, {{ $loteIndex !== null ? $loteIndex : 'null' }})" title="Excluir Item">
                    <i class="fas fa-trash-alt text-sm"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- BODY --}}
    <div id="body-item-{{ $loteIndex !== null ? 'itens-selecionados-lote-'.$loteIndex : 'itens-selecionados-sem-lote' }}-{{ $item->id }}" class="hidden border-t border-gray-100 bg-gray-50/80 p-4">
        <h6 class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Descrição Completa</h6>
        <p class="desc-item-{{ $item->id }} text-sm text-gray-700 whitespace-pre-wrap">{{ $item->descricao_item }}</p>
    </div>
</div>