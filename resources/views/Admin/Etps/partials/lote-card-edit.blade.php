<div class="lote-card border border-gray-200 rounded-xl bg-white shadow-sm overflow-hidden" id="lote-{{ $loteIndex }}">

    {{-- HEADER --}}
    <div class="flex items-center justify-between px-5 py-3.5 bg-gray-50 border-b border-gray-200">
        <button type="button" class="flex items-center gap-2 flex-1 text-left min-w-0" onclick="toggleLote({{ $loteIndex }})">
            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200" id="chevron-lote-{{ $loteIndex }}"></i>
            <span class="text-sm font-semibold text-gray-700 truncate" id="label-lote-{{ $loteIndex }}">
                {{ $lote->nome ?? 'Lote ' . ($loteIndex + 1) }}
            </span>
        </button>
        <div class="flex items-center gap-1 flex-shrink-0 ml-3">
            <button type="button" onclick="duplicarLote({{ $loteIndex }})" title="Duplicar este lote"
                class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:text-[#009496] hover:bg-[#009496]/10 transition">
                <i class="fas fa-copy text-sm"></i>
            </button>
            <button type="button" onclick="removerLote(this)" title="Remover lote"
                class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:text-red-500 hover:bg-red-50 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
    </div>

    {{-- BODY --}}
    <div class="px-6 py-5" id="body-lote-{{ $loteIndex }}">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Lote *</label>
            <input type="text"
                name="lotes[{{ $loteIndex }}][nome]"
                value="{{ $lote->nome ?? '' }}"
                placeholder="Ex: Lote {{ $loteIndex + 1 }} - Materiais de Escritório"
                oninput="atualizarLabelLote({{ $loteIndex }}, this.value)"
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496]"
                required>
        </div>
        @include('Admin.Etps.partials.itens-selector-edit', [
            'loteIndex' => $loteIndex,
            'lote' => $lote,
            'itens' => $itens,
            'etp' => $etp
        ])
    </div>
</div>