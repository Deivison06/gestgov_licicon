<div class="lote-card border border-[#24403c] rounded-2xl bg-[#10201e] shadow-sm overflow-hidden" id="lote-{{ $loteIndex }}">

    {{-- HEADER --}}
    <div class="flex items-center justify-between px-5 py-3.5 bg-[#0d1b19] border-b border-[#24403c]">
        <button type="button" class="flex items-center gap-2 flex-1 text-left min-w-0" onclick="toggleLote({{ $loteIndex }})">
            <i class="fas fa-chevron-down text-[#a2b9b5] transition-transform duration-200" id="chevron-lote-{{ $loteIndex }}"></i>
            <span class="text-sm font-semibold text-[#e6f0ee] truncate" id="label-lote-{{ $loteIndex }}">
                {{ $loteData['nome'] ?? 'Lote ' . ($loteIndex + 1) }}
            </span>
        </button>
        <div class="flex items-center gap-1 flex-shrink-0 ml-3">
            <button type="button" onclick="duplicarLote({{ $loteIndex }})" title="Duplicar este lote"
                class="inline-flex items-center justify-center w-8 h-8 rounded-full text-[#a2b9b5] hover:text-[#2DC197] hover:bg-[#2DC197]/10 transition">
                <i class="fas fa-copy text-sm"></i>
            </button>
            <button type="button" onclick="removerLote(this)" title="Remover lote"
                class="inline-flex items-center justify-center w-8 h-8 rounded-full text-[#a2b9b5] hover:text-red-400 hover:bg-red-500/10 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
    </div>

    {{-- BODY --}}
    <div class="px-6 py-5 bg-[#10201e]" id="body-lote-{{ $loteIndex }}">
        <div class="mb-4">
            <label class="block text-sm font-semibold text-[#a2b9b5] mb-2">Nome do Lote *</label>
            <input type="text"
                name="lotes[{{ $loteIndex }}][nome]"
                value="{{ $loteData['nome'] ?? '' }}"
                placeholder="Ex: Lote {{ $loteIndex + 1 }} - Materiais de Escritório"
                oninput="atualizarLabelLote({{ $loteIndex }}, this.value)"
                class="w-full px-3 py-2.5 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] placeholder-[#a2b9b5]/50"
                required>
        </div>
        @include('Admin.Etps.partials.itens-selector', ['loteIndex' => $loteIndex])
    </div>

</div>
