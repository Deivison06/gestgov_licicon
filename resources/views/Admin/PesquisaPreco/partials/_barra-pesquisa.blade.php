<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
    <div class="flex gap-3">
        <div class="relative flex-1">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input
                type="text"
                id="pp_termo"
                placeholder="Pesquise um item ou serviço (ex: Ar condicionado, Caneta, Serviço de limpeza)..."
                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all"
                autocomplete="off"
                minlength="3">
        </div>

        <button
            id="pp_btn_buscar"
            type="button"
            class="px-5 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl transition-all shadow-sm hover:shadow-md">
            Buscar
        </button>

        <button
            id="pp_btn_filtros"
            type="button"
            class="inline-flex items-center gap-2 px-4 py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold text-sm rounded-xl transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
            </svg>
            <span>Filtros</span>
        </button>
    </div>
    <p class="mt-2 text-[10px] text-gray-400 uppercase font-semibold tracking-wider px-1">
        Mínimo 3 caracteres · Busca em tempo real com debounce de 600ms
    </p>
</div>
