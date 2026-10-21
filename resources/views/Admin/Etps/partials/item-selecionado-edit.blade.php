@php
    $unidadeSalva = strtoupper(trim($item->pivot->unidade));
@endphp

<div class="bg-[var(--card)] border border-[var(--line)] rounded-xl shadow-sm hover:border-[var(--mint)]/40 transition overflow-hidden" 
     id="item-{{ $loteIndex !== null ? 'itens-selecionados-lote-'.$loteIndex : 'itens-selecionados-sem-lote' }}-{{ $item->id }}">
    
    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between p-4 gap-4 cursor-pointer" 
         onclick="toggleAccordionItem('{{ $loteIndex !== null ? 'itens-selecionados-lote-'.$loteIndex : 'itens-selecionados-sem-lote' }}-{{ $item->id }}')" 
         title="Clique para expandir/recolher">
        
        <div class="flex items-center gap-3 flex-1 min-w-0">
            <i class="fas fa-grip-vertical text-[var(--ink-2)] hover:text-[var(--mint)] cursor-grab px-1 drag-handle" title="Arrastar para reordenar" onclick="event.stopPropagation()"></i>
            <span class="item-numero inline-flex items-center justify-center w-7 h-7 rounded-full bg-[var(--mint)]/15 text-[var(--mint)] text-xs font-bold flex-shrink-0 border border-[var(--mint)]/30">0</span>
            <div class="min-w-0 flex-1 flex items-center gap-2">
                <p class="desc-item-{{ $item->id }} text-sm font-semibold text-[var(--ink)] leading-relaxed truncate" title="{{ $item->descricao_item }}">{{ $item->descricao_item }}</p>
            </div>
            <button type="button" class="text-[var(--ink-2)] hover:text-[var(--mint)] focus:outline-none flex-shrink-0 transition-transform duration-200" id="chevron-item-{{ $loteIndex !== null ? 'itens-selecionados-lote-'.$loteIndex : 'itens-selecionados-sem-lote' }}-{{ $item->id }}">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        
        <div class="flex items-center gap-3 flex-shrink-0" onclick="event.stopPropagation()">
            <div class="flex flex-col gap-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--ink-2)]">Unidade</span>
                <select name="{{ $namePrefix }}[{{ $item->id }}][unidade]" required class="rounded-xl border border-[var(--line)] bg-[var(--bg)] text-[var(--ink)] text-xs py-1.5 focus:border-[var(--mint)] focus:ring-[var(--mint)] w-32 transition">
                    @foreach($unidadesMedida as $value => $label)
                        <option value="{{ $value }}" {{ $unidadeSalva === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                    @if ($unidadeSalva && !isset($unidadesMedida[$unidadeSalva]))
                        <option value="{{ $unidadeSalva }}" selected>{{ $unidadeSalva }}</option>
                    @endif
                </select>
            </div>
            
            <div class="flex flex-col gap-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--ink-2)]">Quantidade</span>
                <input type="number" name="{{ $namePrefix }}[{{ $item->id }}][quantidade]" value="{{ $item->pivot->quantidade }}" placeholder="Qtd" min="1" required class="rounded-xl border border-[var(--line)] bg-[var(--bg)] text-[var(--ink)] text-xs py-1.5 focus:border-[var(--mint)] focus:ring-[var(--mint)] w-20 transition">
            </div>

            <div class="flex items-end self-end pb-0.5 gap-2">
                <input type="hidden" name="{{ $namePrefix }}[{{ $item->id }}][item_id]" value="{{ $item->id }}">
                <button type="button" onclick="openModalItemQuickEdit({{ $item->id }}, document.querySelector('#body-item-{{ $loteIndex !== null ? 'itens-selecionados-lote-'.$loteIndex : 'itens-selecionados-sem-lote' }}-{{ $item->id }} .desc-item-{{ $item->id }}').textContent)" class="btn-edit-item-{{ $item->id }} inline-flex items-center justify-center w-8 h-8 rounded-full text-[var(--ink-2)] hover:text-[var(--mint)] hover:bg-[var(--mint)]/10 transition" title="Editar Descrição Completa">
                    <i class="fas fa-edit text-sm"></i>
                </button>
                <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-[var(--ink-2)] hover:text-red-400 hover:bg-red-500/10 focus:outline-none transition" onclick="removerItemSelecionado({{ $item->id }}, {{ $loteIndex !== null ? $loteIndex : 'null' }})" title="Excluir Item">
                    <i class="fas fa-trash-alt text-sm"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- BODY --}}
    <div id="body-item-{{ $loteIndex !== null ? 'itens-selecionados-lote-'.$loteIndex : 'itens-selecionados-sem-lote' }}-{{ $item->id }}" class="hidden border-t border-[var(--line)] bg-[var(--card-2)] p-4">
        <h6 class="text-[10px] font-bold text-[var(--ink-2)] uppercase tracking-wider mb-2">Descrição Completa</h6>
        <p class="desc-item-{{ $item->id }} text-sm text-[var(--ink)] whitespace-pre-wrap">{{ $item->descricao_item }}</p>
    </div>
</div>