{{--
    Painel de Filtros — Data, UF e Modalidade.
    Modo textual: /api/search/ (só termo).
    Modo filtrado: /consulta/v1 (modalidade + período obrigatórios, UF opcional).
--}}
<div id="pp_painel_filtros" class="hidden mt-3">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-md overflow-hidden">

        {{-- Cabeçalho --}}
        <div class="px-5 py-3.5 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2 text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                <span class="text-sm font-bold">Filtros Avançados</span>
            </div>
            <span id="pp_badge_modo"
                class="text-[10px] px-2.5 py-1 rounded-full font-bold uppercase tracking-wider bg-gray-100 text-gray-400 border border-gray-200 transition-all">
                Busca textual
            </span>
        </div>

        <div class="p-5 space-y-5">

            {{-- ── SEÇÃO 1: Consulta no PNCP (server-side) ────────────── --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-2 h-2 rounded-full bg-blue-400 flex-shrink-0"></span>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">Consulta no PNCP</p>
                    <div class="flex-1 h-px bg-gray-100"></div>
                    <span class="text-[9px] text-gray-300 font-semibold uppercase tracking-wider">servidor</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">

                    {{-- Período --}}
                    <div class="md:col-span-5">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Período de Publicação</label>
                        <div class="flex items-center gap-2">
                            <input type="date" id="pp_data_inicial" name="data_inicial"
                                class="flex-1 min-w-0 px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700
                                       focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pp-filtro-watch">
                            <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                            <input type="date" id="pp_data_final" name="data_final"
                                class="flex-1 min-w-0 px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700
                                       focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pp-filtro-watch">
                        </div>
                    </div>

                    {{-- Modalidade --}}
                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="pp_modalidade">
                            Modalidade
                            <span class="text-blue-400 font-normal">(auto-preenche período)</span>
                        </label>
                        <select id="pp_modalidade" name="modalidade"
                            class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700
                                   focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pp-filtro-watch">
                            <option value="">Qualquer modalidade</option>
                            <option value="6">Pregão Eletrônico</option>
                            <option value="7">Pregão Presencial</option>
                            <option value="8">Dispensa</option>
                            <option value="9">Inexigibilidade</option>
                            <option value="4">Concorrência Eletrônica</option>
                            <option value="5">Concorrência Presencial</option>
                            <option value="12">Credenciamento</option>
                            <option value="1">Leilão Eletrônico</option>
                            <option value="3">Concurso</option>
                            <option value="2">Diálogo Competitivo</option>
                            <option value="10">Manifestação de Interesse</option>
                            <option value="11">Pré-qualificação</option>
                        </select>
                    </div>

                    {{-- Situação --}}
                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="pp_situacao">Situação</label>
                        <select id="pp_situacao" name="situacao"
                            class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700
                                   focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pp-filtro-watch">
                            <option value="">Qualquer situação</option>
                            <option value="8">Resultado Homologado</option>
                            <option value="2">Recebendo Proposta</option>
                            <option value="3">Em Julgamento</option>
                            <option value="4">Adjudicada e Encerrada</option>
                            <option value="7">Suspensa</option>
                        </select>
                    </div>

                </div>

                {{-- Badge: período auto-preenchido --}}
                <div id="pp_data_auto_badge"
                    class="hidden mt-2.5 flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-100 rounded-lg text-[11px] text-blue-700">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Período preenchido automaticamente <strong class="font-semibold">(últimos 12 meses)</strong>. Ajuste as datas se necessário.
                </div>
            </div>

            {{-- ── Divisor ──────────────────────────────────────────────── --}}
            <div class="relative flex items-center">
                <div class="flex-1 border-t border-dashed border-gray-200"></div>
                <span class="mx-3 text-[9px] font-bold text-gray-300 uppercase tracking-widest whitespace-nowrap">filtros instantâneos</span>
                <div class="flex-1 border-t border-dashed border-gray-200"></div>
            </div>

            {{-- ── SEÇÃO 2: Filtros Locais (client-side) ───────────────── --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">Filtrar resultados carregados</p>
                    <div class="flex-1 h-px bg-gray-100"></div>
                    <span class="text-[9px] text-gray-300 font-semibold uppercase tracking-wider">local · sem nova busca</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">

                    {{-- UF --}}
                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="pp_uf">Estado (UF)</label>
                        <select id="pp_uf" name="uf"
                            class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700
                                   focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all pp-filtro-watch">
                            <option value="">Todos os estados</option>
                            @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                                <option value="{{ $uf }}">{{ $uf }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Valor de referência --}}
                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="pp_valor_ref">
                            Valor de referência
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400 pointer-events-none select-none">R$</span>
                            <input type="number" id="pp_valor_ref" min="0" step="0.01" placeholder="1.500,00"
                                class="w-full pl-9 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700
                                       focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all">
                        </div>
                    </div>

                    {{-- Variação % --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="pp_valor_pct">Variação</label>
                        <div class="relative">
                            <input type="number" id="pp_valor_pct" min="0" max="100" step="1" placeholder="20"
                                class="w-full pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700
                                       focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400 pointer-events-none select-none">%</span>
                        </div>
                    </div>

                    {{-- Preview do intervalo --}}
                    <div class="md:col-span-3 flex items-end">
                        <div id="pp_valor_preview" class="hidden w-full px-3 py-2.5 bg-violet-50 border border-violet-100 rounded-lg">
                            <p class="text-[9px] font-bold text-violet-500 uppercase tracking-wider mb-0.5">Intervalo aplicado</p>
                            <p class="text-xs text-violet-700 font-semibold">
                                <span id="pp_valor_preview_min"></span>
                                <span class="mx-1 opacity-40">→</span>
                                <span id="pp_valor_preview_max"></span>
                            </p>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- Rodapé --}}
        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-4">
            <p id="pp_filtro_nota" class="text-[10px] text-gray-400 leading-relaxed">
                <strong class="text-blue-600">Modalidade</strong> ativa a consulta estruturada no PNCP (período preenchido automaticamente).
                <strong>UF</strong> e <strong>Valor</strong> filtram os resultados carregados instantaneamente.
            </p>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button type="button" id="pp_btn_limpar_filtros"
                    class="px-3.5 py-2 text-xs font-semibold text-gray-500 hover:text-gray-700 hover:bg-gray-200 rounded-lg transition-all">
                    Limpar
                </button>
                <button id="pp_btn_aplicar_filtros" type="button"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Buscar
                </button>
            </div>
        </div>

    </div>
</div>
