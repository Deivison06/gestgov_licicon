<!-- Modal de Importação de Itens via Excel -->
<div id="modalImportarItens" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">

        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm transition-opacity" id="modal-overlay"></div>

        <!-- Painel do modal -->
        <div class="relative bg-[#10201e] border border-[#24403c] rounded-2xl text-left shadow-xl w-full max-w-lg z-10 flex flex-col max-h-[90vh] overflow-hidden">

            <!-- Cabeçalho -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-[#24403c] bg-[#0d1b19] flex-shrink-0">
                <h3 class="text-base font-semibold text-[#e6f0ee]" id="modal-title">
                    <i class="fas fa-file-excel text-[#2DC197] mr-2"></i>Importar Itens via Excel
                </h3>
                <button type="button" id="btnFecharModalImportar"
                        class="text-[#a2b9b5] hover:text-[#e6f0ee] transition-colors rounded-lg p-1 hover:bg-[#10201e]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Conteúdo rolável -->
            <div class="overflow-y-auto flex-1 px-6 py-4 space-y-4 bg-[#10201e]">

                <!-- Aviso: importação por lote -->
                <div id="info-lote-import" class="hidden flex-col gap-3 p-3 bg-amber-950/20 border border-amber-500/30 rounded-xl text-sm text-amber-200">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Os itens são importados <strong>um lote por vez</strong>. Escolha abaixo em qual lote eles serão inseridos.</span>
                    </div>
                    <div class="mt-2 w-full">
                        <label for="select-lote-import" class="block text-sm font-semibold text-[#a2b9b5] mb-1">Selecione o Lote</label>
                        <select id="select-lote-import" class="w-full rounded-xl border border-[#24403c] text-sm focus:border-[#2DC197] focus:ring-[#2DC197] bg-[#0a1514] text-[#e6f0ee] px-3 py-2">
                        </select>
                    </div>
                </div>

                <!-- Baixar modelo -->
                <div>
                    <button type="button" id="btnBaixarModelo"
                            class="inline-flex items-center px-4 py-2 text-sm font-semibold text-[#2DC197] bg-[#2DC197]/15 border border-[#2DC197]/30 rounded-xl hover:bg-[#2DC197]/25 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Baixar Modelo da Planilha
                    </button>
                </div>

                <!-- Upload -->
                <div>
                    <label class="block text-sm font-semibold text-[#a2b9b5] mb-2">
                        Selecione o arquivo Excel (.xlsx, .xls, .csv) *
                    </label>
                    <div class="flex items-center justify-center w-full">
                        <label for="arquivo_excel"
                               class="flex flex-col items-center justify-center w-full h-28 border-2 border-[#24403c] border-dashed rounded-xl cursor-pointer bg-[#0a1514] hover:bg-[#0d1b19] transition-colors">
                            <div class="flex flex-col items-center justify-center py-4">
                                <svg class="w-8 h-8 mb-2 text-[#2DC197]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-sm text-[#e6f0ee]">
                                    <span class="font-semibold text-[#2DC197]">Clique para upload</span> ou arraste o arquivo
                                </p>
                                <p class="text-xs text-[#a2b9b5] mt-1">.xlsx, .xls ou .csv (max. 10MB)</p>
                            </div>
                            <input id="arquivo_excel" name="arquivo_excel" type="file" class="hidden" accept=".xlsx,.xls,.csv" />
                        </label>
                    </div>
                    <p id="nome-arquivo-selecionado" class="mt-1 text-sm text-[#2DC197] font-medium hidden"></p>
                </div>

                <!-- Progresso -->
                <div id="area-progresso" class="hidden">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-[#a2b9b5]">Processando...</span>
                        <span class="text-sm font-semibold text-[#2DC197]" id="percentual-progresso">0%</span>
                    </div>
                    <div class="w-full bg-[#0a1514] border border-[#24403c] rounded-full h-2">
                        <div id="barra-progresso" class="bg-[#2DC197] h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Erro -->
                <div id="mensagem-erro" class="hidden p-3 text-sm text-red-300 bg-red-950/30 border border-red-500/30 rounded-xl"></div>

                <!-- Prévia dos itens importados -->
                <div id="previa-itens" class="hidden">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-semibold text-[#e6f0ee]">
                            Itens importados
                            <span id="contador-itens-importados" class="text-xs font-normal text-[#a2b9b5] ml-1"></span>
                        </h4>
                        <span class="text-xs text-[#a2b9b5] flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>
                            </svg>
                            Arraste para reordenar · Clique na descrição para editar
                        </span>
                    </div>
                    <ul id="lista-itens-importados" class="space-y-1 max-h-52 overflow-y-auto border border-[#24403c] rounded-xl p-2 bg-[#0d1b19]"></ul>
                </div>

            </div><!-- /conteúdo rolável -->

            <!-- Rodapé -->
            <div class="px-6 py-4 border-t border-[#24403c] bg-[#0d1b19] flex justify-end gap-3 flex-shrink-0">
                <button type="button" id="btnCancelarImportacao"
                        class="px-4 py-2 text-sm font-semibold text-[#e6f0ee] bg-[#10201e] border border-[#24403c] rounded-xl hover:bg-[#142825] hover:text-[#2DC197] transition-colors">
                    Cancelar
                </button>
                <button type="button" id="btnConfirmarImportacao"
                        class="px-5 py-2 text-sm font-bold text-[#041d1a] bg-[#2DC197] rounded-xl hover:bg-[#26aa84] transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:scale-105"
                        disabled>
                    <i class="fas fa-check mr-1"></i> Confirmar Importação
                </button>
            </div>

        </div>
    </div>
</div>

<style>
    #modalImportarItens.show { display: block; }
    #lista-itens-importados .sortable-ghost {
        opacity: 0.35;
        background-color: #d1faf8;
        border-radius: 0.5rem;
    }
    #lista-itens-importados .drag-handle { cursor: grab; }
    #lista-itens-importados .drag-handle:active { cursor: grabbing; }
</style>
