<div class="lote-card border border-gray-200 rounded-xl p-6 bg-gray-50 relative" id="lote-{{ $loteIndex }}">
    <button type="button"
            onclick="removerLote(this)"
            class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>

    <div class="mb-4 pr-8">
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Nome do Lote *
        </label>
        <input type="text"
            name="lotes[{{ $loteIndex }}][nome]"
            value="{{ $lote->nome }}"
            placeholder="Ex: Lote {{ $loteIndex + 1 }} - Materiais de Escritório"
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