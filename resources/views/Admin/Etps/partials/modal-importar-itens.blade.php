<!-- Modal de Importação de Itens via Excel -->
<div id="modalImportarItens" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="modal-overlay"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                            Importar Itens via Excel
                        </h3>
                        
                        <!-- Botão para baixar modelo -->
                        <div class="mb-4">
                            <button type="button" 
                                    id="btnBaixarModelo"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-[#009496] bg-[#009496]/10 rounded-lg hover:bg-[#009496]/20 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Baixar Modelo da Planilha
                            </button>
                        </div>

                        <!-- Área de upload -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Selecione o arquivo Excel (.xlsx, .xls, .csv) *
                            </label>
                            
                            <div class="flex items-center justify-center w-full">
                                <label for="arquivo_excel" 
                                       class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-500">
                                            <span class="font-semibold">Clique para upload</span> ou arraste o arquivo
                                        </p>
                                        <p class="text-xs text-gray-500">.xlsx, .xls ou .csv (max. 10MB)</p>
                                    </div>
                                    <input id="arquivo_excel" name="arquivo_excel" type="file" class="hidden" accept=".xlsx,.xls,.csv" />
                                </label>
                            </div>
                            <p id="nome-arquivo-selecionado" class="mt-2 text-sm text-gray-600 hidden"></p>
                        </div>

                        <!-- Área de progresso (inicialmente oculta) -->
                        <div id="area-progresso" class="hidden mb-4">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Processando...</span>
                                <span class="text-sm font-medium text-gray-700" id="percentual-progresso">0%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div id="barra-progresso" class="bg-[#009496] h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>

                        <!-- Área de mensagem de erro -->
                        <div id="mensagem-erro" class="hidden p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg"></div>

                        <!-- Prévia dos itens importados (inicialmente oculta) -->
                        <div id="previa-itens" class="hidden mb-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Itens importados:</h4>
                            <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50">
                                <ul id="lista-itens-importados" class="space-y-2 text-sm"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer do modal -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        id="btnConfirmarImportacao"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#009496] text-base font-medium text-white hover:bg-[#007a7a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#009496] sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    Confirmar Importação
                </button>
                <button type="button" 
                        id="btnCancelarImportacao"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Template para item selecionado (usado pelo JavaScript) -->
<template id="template-item-selecionado">
    <div class="flex items-center justify-between bg-white border rounded-lg p-3 shadow-sm">
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-800 mb-2 descricao-item"></p>
            <div class="flex gap-3">
                <input type="hidden" class="item-id-input" name="" value="">
                <select class="px-2 py-1 border border-gray-300 rounded text-sm unidade-select" required>
                    <option value="unidade">Unidade</option>
                    <option value="pacote">Pacote</option>
                    <option value="caixa">Caixa</option>
                    <option value="metro">Metro</option>
                    <option value="quilograma">Quilograma</option>
                    <option value="litro">Litro</option>
                </select>
                <input type="number" class="px-2 py-1 border border-gray-300 rounded text-sm w-20 quantidade-input" placeholder="Qtd" min="1" required>
            </div>
        </div>
        <button type="button" class="ml-4 text-red-500 hover:text-red-700 font-bold btn-remover-item">
            ✕
        </button>
    </div>
</template>

<style>
    /* Animações do modal */
    #modalImportarItens {
        transition: opacity 0.3s ease;
    }
    
    #modalImportarItens.show {
        display: block;
    }
    
    #modalImportarItens .bg-white {
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>