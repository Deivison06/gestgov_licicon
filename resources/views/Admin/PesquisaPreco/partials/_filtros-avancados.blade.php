{{--
    Painel de Filtros Avançados — inicialmente oculto.
    Os campos ficam habilitados mas o painel fica colapsado até o usuário clicar em "Filtros".
    Implementação completa de filtragem via API será ativada quando os parâmetros forem
    confirmados na documentação do PNCP.
--}}
<div id="pp_painel_filtros" class="hidden mt-3">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                </svg>
                Filtros Avançados
            </h3>
            <span class="text-[10px] text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded font-bold uppercase tracking-wider">
                Parâmetros em validação
            </span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">

            {{-- Período: Data Inicial --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="pp_data_inicial">
                    Data Inicial
                </label>
                <input
                    type="date"
                    id="pp_data_inicial"
                    name="data_inicial"
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>

            {{-- Período: Data Final --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="pp_data_final">
                    Data Final
                </label>
                <input
                    type="date"
                    id="pp_data_final"
                    name="data_final"
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>

            {{-- UF --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="pp_uf">
                    UF
                </label>
                <select
                    id="pp_uf"
                    name="uf"
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="">Todos os estados</option>
                    @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                        <option value="{{ $uf }}">{{ $uf }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Código IBGE --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="pp_codigo_ibge">
                    Código IBGE
                </label>
                <input
                    type="text"
                    id="pp_codigo_ibge"
                    name="codigo_ibge"
                    placeholder="Ex: 3550308"
                    maxlength="7"
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>

            {{-- CNPJ do Órgão --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="pp_cnpj_orgao">
                    CNPJ do Órgão
                </label>
                <input
                    type="text"
                    id="pp_cnpj_orgao"
                    name="cnpj_orgao"
                    placeholder="Somente números"
                    maxlength="14"
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>

        </div>

        <div class="mt-4 flex items-center justify-end gap-3">
            <button
                type="button"
                onclick="document.querySelectorAll('#pp_painel_filtros input, #pp_painel_filtros select').forEach(el => el.value = '')"
                class="px-4 py-2 text-xs font-semibold text-gray-500 hover:text-gray-700 transition-colors">
                Limpar filtros
            </button>
            <button
                id="pp_btn_aplicar_filtros"
                type="button"
                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm">
                Aplicar e buscar
            </button>
        </div>
    </div>
</div>
