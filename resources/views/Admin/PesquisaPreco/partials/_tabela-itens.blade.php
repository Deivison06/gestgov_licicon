<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    {{-- Cabeçalho do detalhe --}}
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <button
                id="pp_btn_voltar"
                type="button"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar à lista
            </button>
            <span class="text-gray-200">|</span>
            <p id="pp_label_contratacao" class="text-xs text-gray-500 line-clamp-1"></p>
        </div>
        <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wider flex-shrink-0">Itens da Contratação</span>
    </div>

    {{-- Legenda de tipos de valor --}}
    <div class="px-5 py-2.5 bg-gray-50 border-b border-gray-100 flex items-center gap-4 text-[10px] font-semibold uppercase tracking-wider">
        <span class="flex items-center gap-1.5">
            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
            <span class="text-gray-500">Valor Homologado = preço efetivamente pago</span>
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-2 h-2 bg-yellow-400 rounded-full"></span>
            <span class="text-gray-500">Valor Estimado = referência antes do pregão</span>
        </span>
    </div>

    {{-- Tabela --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50/50 text-gray-500 text-[10px] font-bold uppercase tracking-wider border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3 w-12">#</th>
                    <th class="px-4 py-3">Descrição</th>
                    <th class="px-4 py-3 w-28">Quantidade</th>
                    <th class="px-4 py-3 text-right w-40">Valor Unitário</th>
                    <th class="px-4 py-3 text-right w-36">Valor Total</th>
                    <th class="px-4 py-3 w-48">Fornecedor / Vencedor</th>
                </tr>
            </thead>
            <tbody id="pp_tabela_itens_body" class="divide-y divide-gray-50">
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">
                        Selecione uma contratação para ver os itens.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>
