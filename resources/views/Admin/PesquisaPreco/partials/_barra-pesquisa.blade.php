<div class="bg-[var(--card)] rounded-2xl shadow-sm border border-[var(--line)] p-4">
    <div class="flex gap-3">
        <div class="relative flex-1">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-[var(--ink-4)] pointer-events-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input
                type="text"
                id="pp_termo"
                placeholder="Pesquise um item ou serviço (ex: Ar condicionado, Caneta, Serviço de limpeza)..."
                class="w-full pl-12 pr-4 py-3.5 bg-[var(--bg)] border border-[var(--line)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-transparent text-sm text-[var(--ink)] placeholder-[var(--ink-4)] transition-all"
                autocomplete="off"
                minlength="3">
        </div>

        <button
            id="pp_btn_buscar"
            type="button"
            class="px-5 py-3.5 bg-[var(--mint)] hover:bg-[var(--mint-h)] text-[var(--on-mint)] font-bold text-sm rounded-xl transition-all shadow-sm">
            Buscar
        </button>

        <button
            id="pp_btn_filtros"
            type="button"
            class="inline-flex items-center gap-2 px-4 py-3.5 bg-[var(--hover-bg)] hover:bg-[var(--card-3)] border border-[var(--line)] text-[var(--ink-2)] hover:text-[var(--ink)] font-semibold text-sm rounded-xl transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
            </svg>
            <span>Filtros</span>
        </button>
    </div>
    <p class="mt-2 text-[10px] text-[var(--ink-4)] uppercase font-semibold tracking-wider px-1">
        Mínimo 3 caracteres · Busca em tempo real com debounce de 600ms
    </p>

    <div id="pp_aviso_modo_filtrado"
        class="hidden mt-2.5 flex items-start gap-2.5 px-3.5 py-2.5 bg-amber-500/10 border border-amber-500/30 rounded-xl">
        <svg class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <p class="text-[11px] text-amber-300 leading-relaxed">
            <strong class="font-semibold text-[var(--ink)]">Consulta estruturada ativa.</strong>
            A API do PNCP não aceita busca por texto neste modo — o termo filtrará apenas os itens já carregados em cada página.
            Para buscar por descrição em todo o acervo, remova a modalidade ou o período.
        </p>
    </div>
</div>
