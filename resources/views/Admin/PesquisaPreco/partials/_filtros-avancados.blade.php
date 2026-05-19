{{--
    Painel de Filtros — Data, UF e Modalidade.
    Modo textual: /api/search/ (só termo).
    Modo filtrado: /consulta/v1 (modalidade + período obrigatórios, UF opcional).
--}}
<div id="pp_painel_filtros" class="hidden mt-3">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                </svg>
                Filtros
            </h3>
            <span id="pp_badge_modo"
                class="text-[10px] px-2 py-0.5 rounded font-bold uppercase tracking-wider bg-gray-100 text-gray-400 border border-gray-200">
                Busca textual
            </span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

            {{-- Período: Data Inicial --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="pp_data_inicial">
                    Data Inicial
                </label>
                <input type="date" id="pp_data_inicial" name="data_inicial"
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700
                           focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pp-filtro-watch">
            </div>

            {{-- Período: Data Final --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="pp_data_final">
                    Data Final
                </label>
                <input type="date" id="pp_data_final" name="data_final"
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700
                           focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pp-filtro-watch">
            </div>

            {{-- UF --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="pp_uf">
                    UF
                </label>
                <select id="pp_uf" name="uf"
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700
                           focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pp-filtro-watch">
                    <option value="">Todos os estados</option>
                    @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                        <option value="{{ $uf }}">{{ $uf }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Modalidade --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="pp_modalidade">
                    Modalidade
                    <span class="text-blue-500 font-normal">(ativa filtros avançados)</span>
                </label>
                <select id="pp_modalidade" name="modalidade"
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700
                           focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pp-filtro-watch">
                    <option value="">Qualquer modalidade</option>
                    <option value="6">Pregão - Eletrônico</option>
                    <option value="7">Pregão - Presencial</option>
                    <option value="8">Dispensa</option>
                    <option value="9">Inexigibilidade</option>
                    <option value="4">Concorrência - Eletrônica</option>
                    <option value="5">Concorrência - Presencial</option>
                    <option value="12">Credenciamento</option>
                    <option value="1">Leilão - Eletrônico</option>
                    <option value="3">Concurso</option>
                    <option value="2">Diálogo Competitivo</option>
                    <option value="10">Manifestação de Interesse</option>
                    <option value="11">Pré-qualificação</option>
                </select>
            </div>

        </div>

        {{-- Nota sobre modos --}}
        <p id="pp_filtro_nota" class="mt-3 text-[11px] text-gray-400">
            Preencha <strong>Modalidade + Período</strong> para ativar a consulta estruturada com filtros reais do PNCP.
            UF funciona em ambos os modos.
        </p>

        <div class="mt-4 flex items-center justify-end gap-3">
            <button type="button" id="pp_btn_limpar_filtros"
                class="px-4 py-2 text-xs font-semibold text-gray-500 hover:text-gray-700 transition-colors">
                Limpar filtros
            </button>
            <button id="pp_btn_aplicar_filtros" type="button"
                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm">
                Aplicar e buscar
            </button>
        </div>
    </div>
</div>
