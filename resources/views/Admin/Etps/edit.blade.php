@extends('layouts.app')
@section('page-title', 'Editar ETP')
@section('page-subtitle', 'Edite o Estudo Técnico Preliminar')

@section('content')
<div class="py-8">
    <div class="mb-4 flex justify-between items-center">
        <a href="{{ route('admin.etps.show', $etp->id) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar para Visualização
        </a>
        
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-600">Status:</span>
            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full
                @if($etp->status === 'pendente') bg-yellow-100 text-yellow-800
                @elseif($etp->status === 'em_analise') bg-blue-100 text-blue-800
                @elseif($etp->status === 'aprovado') bg-green-100 text-green-800
                @elseif($etp->status === 'em_processo') bg-purple-100 text-purple-800
                @elseif($etp->status === 'recusado') bg-red-100 text-red-800
                @endif">
                {{ ucfirst(str_replace('_', ' ', $etp->status)) }}
            </span>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 mb-8 border border-red-200 shadow-sm rounded-2xl bg-gradient-to-r from-red-50 to-red-100">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm font-medium text-red-800">Verifique os erros abaixo:</p>
            </div>
            <ul class="ml-8 list-disc text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden px-8 py-8 relative">
        <form action="{{ route('admin.etps.update', $etp->id) }}" method="POST" enctype="multipart/form-data" id="etpForm">
            @csrf
            @method('PUT')
            
            <!-- PROGRESS BAR STEPS -->
            <div class="w-full mb-8 relative">
                <div class="absolute w-full h-1 bg-gray-200 rounded-full top-5"></div>
                <div id="progress-bar" class="absolute w-1/3 h-1 bg-[#009496] rounded-full top-5 transition-all duration-300" style="width: 33%"></div>
                <div class="flex justify-between mx-auto items-center relative z-10">
                    <!-- Step 1 -->
                    <div class="text-center w-1/3">
                        <div class="w-10 h-10 mx-auto bg-[#009496] rounded-full text-white flex items-center justify-center font-bold border-4 border-white shadow-sm" id="indicator-1">1</div>
                        <p class="mt-2 text-sm font-medium text-[#009496]" id="label-1">Dados Iniciais</p>
                    </div>
                    <!-- Step 2 -->
                    <div class="text-center w-1/3">
                        <div class="w-10 h-10 mx-auto bg-gray-200 rounded-full text-gray-500 flex items-center justify-center font-bold border-4 border-white shadow-sm transition-colors duration-300" id="indicator-2">2</div>
                        <p class="mt-2 text-sm font-medium text-gray-400 transition-colors duration-300" id="label-2">Objeto</p>
                    </div>
                    <!-- Step 3 -->
                    <div class="text-center w-1/3">
                        <div class="w-10 h-10 mx-auto bg-gray-200 rounded-full text-gray-500 flex items-center justify-center font-bold border-4 border-white shadow-sm transition-colors duration-300" id="indicator-3">3</div>
                        <p class="mt-2 text-sm font-medium text-gray-400 transition-colors duration-300" id="label-3">Contratação</p>
                    </div>
                </div>
            </div>

            <!-- PASSO 1 -->
            <div id="step-1" class="step-content block">
                <h4 class="text-lg font-semibold text-gray-800 mb-6 border-b pb-2">Passo 1: Selecione a Secretaria e Responsável</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Secretaria da Prefeitura *</label>
                        <select name="secretaria_id"
                            id="secretaria_id"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent" required>
                            <option value="">Selecione...</option>
                            @foreach($secretarias as $sec)
                                <option value="{{ $sec->id }}"
                                    data-servidor="{{ $sec->servidor_responsavel }}"
                                    {{ old('secretaria_id', $etp->secretaria_id) == $sec->id ? 'selected' : '' }}>
                                    {{ $sec->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Servidor Responsável
                        </label>
                        <input type="text"
                            name="servidor_responsavel"
                            id="servidor_responsavel"
                            value="{{ old('servidor_responsavel', $etp->servidor_responsavel) }}"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent" required>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Modalidade da Licitação *</label>
                    <select name="modalidade" id="modalidade" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent" required onchange="toggleModalidadeFields()">
                        <option value="">Selecione...</option>
                        <option value="pregao" {{ old('modalidade', $etp->modalidade) == 'pregao' ? 'selected' : '' }}>Pregão</option>
                        <option value="concorrencia" {{ old('modalidade', $etp->modalidade) == 'concorrencia' ? 'selected' : '' }}>Concorrência</option>
                        <option value="dispensa" {{ old('modalidade', $etp->modalidade) == 'dispensa' ? 'selected' : '' }}>Dispensa</option>
                        <option value="inexigibilidade" {{ old('modalidade', $etp->modalidade) == 'inexigibilidade' ? 'selected' : '' }}>Inexigibilidade</option>
                    </select>
                </div>
                
                <div class="mt-8 flex justify-end">
                    <button type="button" class="btn-next px-6 py-2.5 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all" onclick="nextStep(2)">Próximo Passo <i class="fas fa-arrow-right ml-2"></i></button>
                </div>
            </div>

            <!-- PASSO 2 -->
            <div id="step-2" class="step-content hidden">
                <h4 class="text-lg font-semibold text-gray-800 mb-6 border-b pb-2">Passo 2: Objeto da Licitação</h4>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Especificar o objeto da licitação *</label>
                    <textarea name="objeto_licitacao" id="objeto_licitacao" rows="5" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent resize-y" required>{{ old('objeto_licitacao', $etp->objeto_licitacao) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Descreva detalhadamente o que será adquirido ou contratado.</p>
                </div>
                <div class="mt-8 flex justify-between">
                    <button type="button" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all" onclick="prevStep(1)"><i class="fas fa-arrow-left mr-2"></i> Voltar</button>
                    <button type="button" class="px-6 py-2.5 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all" onclick="nextStep(3)">Próximo Passo <i class="fas fa-arrow-right ml-2"></i></button>
                </div>
            </div>

            <!-- PASSO 3 -->
            <div id="step-3" class="step-content hidden">
                <h4 class="text-lg font-semibold text-gray-800 mb-6 border-b pb-2">
                    Passo 3: Tipo de Contratação e Anexos
                </h4>

                <div id="campos-itens-contratacao">
                    {{-- TIPO --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Tipo de contratação *
                        </label>

                        <div class="flex items-center space-x-6">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio"
                                    name="tipo_contratacao"
                                    value="item"
                                    class="form-radio text-[#009496] w-5 h-5"
                                    {{ old('tipo_contratacao', $etp->tipo_contratacao) == 'item' ? 'checked' : '' }}
                                    onchange="toggleContratacaoTipo()"
                                    required>
                                <span class="ml-2">Por Item</span>
                            </label>

                            <label class="inline-flex items-center cursor-pointer border-l pl-6 border-gray-200">
                                <input type="radio"
                                    name="tipo_contratacao"
                                    value="lote"
                                    class="form-radio text-[#009496] w-5 h-5"
                                    {{ old('tipo_contratacao', $etp->tipo_contratacao) == 'lote' ? 'checked' : '' }}
                                    onchange="toggleContratacaoTipo()">
                                <span class="ml-2">Por Lote</span>
                            </label>
                        </div>
                    </div>

                    {{-- BOTÃO DE IMPORTAÇÃO --}}
                    <div class="mb-4 flex justify-between items-center">
                        <label class="block text-sm font-medium text-gray-700">
                            Selecionar Itens *
                        </label>
                        <button type="button" 
                                id="btnImportarItens"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Importar Itens via Excel
                        </button>
                    </div>

                    {{-- ÁREA DE ITENS (SEM LOTE) --}}
                    <div id="area-itens-sem-lote" class="{{ $etp->tipo_contratacao == 'lote' ? 'hidden' : 'block' }}">
                        @include('Admin.Etps.partials.itens-selector-edit', [
                            'loteIndex' => null,
                            'etp' => $etp
                        ])
                    </div>

                    {{-- ÁREA DE LOTES --}}
                    <div id="area-lotes" class="{{ $etp->tipo_contratacao == 'lote' ? 'block' : 'hidden' }}">
                        <div class="mb-4 flex justify-between items-center">
                            <h5 class="text-md font-semibold text-gray-700">Lotes da Contratação</h5>
                            <button type="button" 
                                    onclick="adicionarLote()"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Novo Lote
                            </button>
                        </div>

                        <div id="lotes-container" class="space-y-6">
                            @if($etp->tipo_contratacao == 'lote' && $etp->lotes->count() > 0)
                                @foreach($etp->lotes as $index => $lote)
                                    @include('Admin.Etps.partials.lote-card-edit', [
                                        'loteIndex' => $index,
                                        'lote' => $lote,
                                        'itens' => $itens
                                    ])
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Prazo --}}
                <div class="mb-4 mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Prazo de Entrega *
                    </label>
                    <input type="text"
                        name="prazo_entrega"
                        id="prazo_entrega"
                        value="{{ old('prazo_entrega', $etp->prazo_entrega) }}"
                        placeholder="Ex: 30 dias após emissão da nota"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496]"
                        required>
                </div>

                {{-- Dotação Orçamentaria --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Dotação Orçamentária *
                    </label>
                    <textarea
                        name="dotacao_orcamentaria"
                        id="dotacao_orcamentaria"
                        placeholder="Digite a dotação orçamentária"
                        rows="3"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent resize-y"
                        required>{{ old('dotacao_orcamentaria', $etp->dotacao_orcamentaria) }}</textarea>
                </div>

                {{-- Cotação ou Projeto Básico --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2" id="label_pdf_anexo">
                        Anexar Cotação do Fornecedor Local
                    </label>
                    
                    @if($etp->cotacao_path)
                    <div class="mb-2 p-2 bg-gray-50 rounded-lg flex items-center justify-between">
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                            Arquivo atual: {{ basename($etp->cotacao_path) }}
                        </span>
                        <span class="text-xs text-gray-500">(Envie um novo arquivo para substituir)</span>
                    </div>
                    @endif
                    
                    <input type="file"
                        name="cotacao_path"
                        id="cotacao_path"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-[#009496]/10 file:text-[#009496]
                        hover:file:bg-[#009496]/20">
                    <p class="mt-1 text-xs text-gray-500">
                        Máximo 10MB. Para concorrência ou inexigibilidade, anexe o Projeto Básico.
                    </p>
                </div>

                <div class="mt-8 flex justify-between">
                    <button type="button"
                        class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                        onclick="prevStep(2)">
                        <i class="fas fa-arrow-left mr-2"></i> Voltar
                    </button>

                    <div class="flex space-x-3">
                        <button type="button"
                            onclick="confirmarCancelamento()"
                            class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </button>
                        
                        <button type="submit"
                            class="px-8 py-3 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-md">
                            <i class="fas fa-save mr-2"></i> Salvar Alterações
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

@include('Admin.Etps.partials.modal-importar-itens')

<script>
document.addEventListener("DOMContentLoaded", function () {
    let loteCounter = {{ $etp->lotes->count() }};

    /* =====================================================
        ELEMENTOS
    ====================================================== */

    const secretariaSelect = document.getElementById('secretaria_id');
    const servidorInput = document.getElementById('servidor_responsavel');
    const modalidadeSelect = document.getElementById('modalidade');
    const cotacaoInput = document.getElementById('cotacao_path');
    const camposItens = document.getElementById('campos-itens-contratacao');
    const labelPdf = document.getElementById('label_pdf_anexo');

    /* =====================================================
        SECRETARIA → SERVIDOR AUTO
    ====================================================== */

    function preencherServidor() {
        const selected = secretariaSelect.options[secretariaSelect.selectedIndex];
        const servidor = selected?.getAttribute('data-servidor');

        // Only auto-fill if empty
        if (!servidorInput.value.trim()) {
            servidorInput.value = servidor ?? '';
        }
    }

    preencherServidor();

    secretariaSelect.addEventListener('change', function () {
        preencherServidor();
    });

    /* =====================================================
        TIPO CONTRATAÇÃO
    ====================================================== */

    window.toggleContratacaoTipo = function () {
        const selected = document.querySelector('input[name="tipo_contratacao"]:checked');
        if (!selected) return;

        const isLote = selected.value === 'lote';
        const areaSemLote = document.getElementById('area-itens-sem-lote');
        const areaLotes = document.getElementById('area-lotes');

        if (isLote) {
            areaSemLote.classList.add('hidden');
            areaLotes.classList.remove('hidden');
            
            // Se não houver lotes, adiciona um automaticamente
            if (document.querySelectorAll('.lote-card').length === 0) {
                adicionarLote();
            }
        } else {
            areaSemLote.classList.remove('hidden');
            areaLotes.classList.add('hidden');
        }
    };

    /* =====================================================
        MODALIDADE
    ====================================================== */

    window.toggleModalidadeFields = function () {
        const modalidade = modalidadeSelect.value;
        const radiosTipo = document.querySelectorAll('input[name="tipo_contratacao"]');

        if (modalidade === 'concorrencia' || modalidade === 'inexigibilidade') {
            camposItens.classList.add('hidden');
            labelPdf.innerText = 'Anexar Projeto Básico *';
            cotacaoInput.setAttribute('required', 'required');

            radiosTipo.forEach(el => {
                el.checked = false;
                el.removeAttribute('required');
            });
        } else {
            camposItens.classList.remove('hidden');
            labelPdf.innerText = 'Anexar Cotação do Fornecedor Local';
            cotacaoInput.removeAttribute('required');

            radiosTipo.forEach(el => {
                el.setAttribute('required', 'required');
            });
        }
    };

    /* =====================================================
        GERENCIAMENTO DE LOTES
    ====================================================== */

    window.adicionarLote = function () {
        const container = document.getElementById('lotes-container');
        
        // Criar o elemento div para o novo lote
        const loteDiv = document.createElement('div');
        loteDiv.className = 'lote-card border border-gray-200 rounded-xl p-6 bg-gray-50 relative';
        loteDiv.id = `lote-${loteCounter}`;
        
        // HTML do botão remover
        const removeButtonHTML = `
            <button type="button"
                    onclick="removerLote(this)"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        
        // HTML do input nome do lote
        const nomeLoteHTML = `
            <div class="mb-4 pr-8">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nome do Lote *
                </label>
                <input type="text"
                    name="lotes[${loteCounter}][nome]"
                    placeholder="Ex: Lote ${loteCounter + 1} - Materiais de Escritório"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496]"
                    required>
            </div>
        `;
        
        // HTML do seletor de itens
        const itensSelectorHTML = gerarItensSelector(loteCounter);
        
        // Montar o lote completo
        loteDiv.innerHTML = removeButtonHTML + nomeLoteHTML + itensSelectorHTML;
        
        // Adicionar ao container
        container.appendChild(loteDiv);
        
        // Inicializar a busca para este lote
        inicializarBuscaLote(loteCounter);
        
        loteCounter++;
    };

    // Função auxiliar para gerar o HTML do seletor de itens
    function gerarItensSelector(loteIndex) {
        const buscaId = `buscar_item_lote_${loteIndex}`;
        const listaId = `lista_itens_lote_${loteIndex}`;
        const containerId = `itens-selecionados-lote-${loteIndex}`;
        
        // Gerar opções de itens
        let itensOptions = '';
        @foreach($itens as $item)
            itensOptions += `
                <label class="flex items-center space-x-3 item-option"
                    data-descricao="{{ strtolower($item->descricao_item) }}">
                    <input type="checkbox"
                        value="{{ $item->id }}"
                        data-descricao="{{ $item->descricao_item }}"
                        data-lote-index="${loteIndex}"
                        class="item-checkbox w-4 h-4 text-[#009496]"
                        onchange="toggleItemSelecionado(this, ${loteIndex})">
                    <span class="text-sm text-gray-700">
                        {{ $item->descricao_item }}
                    </span>
                </label>
            `;
        @endforeach
        
        return `
            <div class="mb-4 itens-selector" data-lote-index="${loteIndex}">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Selecionar Itens *
                </label>

                {{-- BUSCA --}}
                <div class="mb-3">
                    <input type="text"
                        id="${buscaId}"
                        placeholder="Buscar item..."
                        class="buscar-item w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496]"
                        data-lista="${listaId}">
                </div>

                {{-- LISTA DE ITENS --}}
                <div id="${listaId}"
                    class="hidden border border-gray-200 rounded-lg p-4 max-h-60 overflow-y-auto space-y-2 bg-gray-50">
                    ${itensOptions}
                </div>

                {{-- SELECIONADOS --}}
                <div class="mt-6">
                    <h5 class="text-sm font-semibold mb-2 text-gray-700">
                        Itens Selecionados - Lote ${loteIndex + 1}
                    </h5>

                    <div id="${containerId}" class="space-y-3"></div>
                </div>
            </div>
        `;
    }

    window.removerLote = function (button) {
        const loteCard = button.closest('.lote-card');
        if (document.querySelectorAll('.lote-card').length > 1) {
            loteCard.remove();
        } else {
            alert('É necessário ter pelo menos um lote.');
        }
    };

    /* =====================================================
        BUSCA DE ITENS
    ====================================================== */

    function inicializarBuscaLote(loteIndex) {
        const buscaId = loteIndex !== undefined ? `buscar_item_lote_${loteIndex}` : 'buscar_item_global';
        const listaId = loteIndex !== undefined ? `lista_itens_lote_${loteIndex}` : 'lista_itens_global';
        
        const buscaInput = document.getElementById(buscaId);
        if (!buscaInput) return;

        buscaInput.addEventListener('keyup', function () {
            const termo = this.value.toLowerCase().trim();
            const lista = document.getElementById(listaId);
            if (!lista) return;
            
            const itens = lista.querySelectorAll('.item-option');

            if (termo.length < 2) {
                lista.classList.add('hidden');
                itens.forEach(el => el.style.display = 'flex');
                return;
            }

            lista.classList.remove('hidden');

            itens.forEach(function (el) {
                const descricao = el.dataset.descricao;
                el.style.display = descricao.includes(termo) ? 'flex' : 'none';
            });
        });
    }

    // Inicializar buscas existentes
    inicializarBuscaLote();
    document.querySelectorAll('.lote-card').forEach((card, index) => {
        inicializarBuscaLote(index);
    });

    /* =====================================================
        SELEÇÃO DE ITENS
    ====================================================== */

    window.toggleItemSelecionado = function (checkbox, loteIndex) {
        const id = checkbox.value;
        const descricao = checkbox.dataset.descricao;
        
        const containerId = loteIndex !== null ? `itens-selecionados-lote-${loteIndex}` : 'itens-selecionados-sem-lote';
        const container = document.getElementById(containerId);
        
        if (checkbox.checked) {
            const namePrefix = loteIndex !== null ? `lotes[${loteIndex}][itens]` : 'itens';
            
            const itemHTML = `
                <div class="flex items-center justify-between bg-white border rounded-lg p-3 shadow-sm" id="item-${containerId}-${id}">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800 mb-2">${descricao}</p>

                        <div class="flex gap-3">
                            <input type="hidden" name="${namePrefix}[${id}][item_id]" value="${id}">

                            <select name="${namePrefix}[${id}][unidade]"
                                class="px-2 py-1 border border-gray-300 rounded text-sm"
                                required>
                                <option value="unidade">Unidade</option>
                                <option value="pacote">Pacote</option>
                                <option value="caixa">Caixa</option>
                                <option value="metro">Metro</option>
                                <option value="quilograma">Quilograma</option>
                                <option value="litro">Litro</option>
                            </select>

                            <input type="number"
                                name="${namePrefix}[${id}][quantidade]"
                                placeholder="Qtd"
                                min="1"
                                required
                                class="px-2 py-1 border border-gray-300 rounded text-sm w-20">
                        </div>
                    </div>

                    <button type="button"
                        class="ml-4 text-red-500 hover:text-red-700 font-bold"
                        onclick="removerItemSelecionado(${id}, ${loteIndex})">
                        ✕
                    </button>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', itemHTML);
        } else {
            removerItemSelecionado(id, loteIndex);
        }
    };

    window.removerItemSelecionado = function (id, loteIndex) {
        const containerId = loteIndex !== null ? `itens-selecionados-lote-${loteIndex}` : 'itens-selecionados-sem-lote';
        const itemDiv = document.getElementById(`item-${containerId}-${id}`);
        if (itemDiv) itemDiv.remove();

        // Desmarcar checkbox correspondente
        const checkboxSelector = loteIndex !== null 
            ? `.item-checkbox[value="${id}"][data-lote-index="${loteIndex}"]`
            : `.item-checkbox[value="${id}"]:not([data-lote-index])`;
        
        const checkbox = document.querySelector(checkboxSelector);
        if (checkbox) checkbox.checked = false;
    };

    /* =====================================================
        STEPS
    ====================================================== */

    window.nextStep = function (step) {
        if (step === 2) {
            if (!secretariaSelect.value) {
                alert('Selecione uma secretaria primeiro.');
                return;
            }
            if (!modalidadeSelect.value) {
                alert('Selecione uma modalidade primeiro.');
                return;
            }
        }

        if (step === 3) {
            if (!document.getElementById('objeto_licitacao').value.trim()) {
                alert('Preencha o objeto primeiro.');
                return;
            }
        }

        document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('step-' + step).classList.remove('hidden');
        updateProgress(step);
    };

    window.prevStep = function (step) {
        document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('step-' + step).classList.remove('hidden');
        updateProgress(step);
    };

    window.updateProgress = function (step) {
        const progressBar = document.getElementById('progress-bar');

        if (step === 1) progressBar.style.width = '33%';
        if (step === 2) progressBar.style.width = '66%';
        if (step === 3) progressBar.style.width = '100%';

        for (let i = 1; i <= 3; i++) {
            let indicator = document.getElementById('indicator-' + i);
            let label = document.getElementById('label-' + i);

            if (i <= step) {
                indicator.classList.remove('bg-gray-200', 'text-gray-500');
                indicator.classList.add('bg-[#009496]', 'text-white');
                label.classList.remove('text-gray-400');
                label.classList.add('text-[#009496]');
            } else {
                indicator.classList.add('bg-gray-200', 'text-gray-500');
                indicator.classList.remove('bg-[#009496]', 'text-white');
                label.classList.add('text-gray-400');
                label.classList.remove('text-[#009496]');
            }
        }
    };

    /* =====================================================
        CONFIRMAR CANCELAMENTO
    ====================================================== */

    window.confirmarCancelamento = function () {
        if (confirm('Tem certeza que deseja cancelar a edição? As alterações não salvas serão perdidas.')) {
            window.location.href = '{{ route("admin.etps.show", $etp->id) }}';
        }
    };

    // Inicialização
    toggleModalidadeFields();
    toggleContratacaoTipo();
    
    // Se for lote e não houver lotes, adiciona um
    if (document.querySelector('input[name="tipo_contratacao"]:checked')?.value === 'lote') {
        if (document.querySelectorAll('.lote-card').length === 0) {
            adicionarLote();
        }
    }
});

/* =====================================================
    IMPORTAÇÃO DE ITENS VIA EXCEL
====================================================== */

// Elementos do modal
const modalImportar = document.getElementById('modalImportarItens');
const btnImportar = document.getElementById('btnImportarItens');
const btnCancelar = document.getElementById('btnCancelarImportacao');
const btnConfirmar = document.getElementById('btnConfirmarImportacao');
const btnBaixarModelo = document.getElementById('btnBaixarModelo');
const overlay = document.getElementById('modal-overlay');
const arquivoInput = document.getElementById('arquivo_excel');
const nomeArquivoSpan = document.getElementById('nome-arquivo-selecionado');
const areaProgresso = document.getElementById('area-progresso');
const barraProgresso = document.getElementById('barra-progresso');
const percentualProgresso = document.getElementById('percentual-progresso');
const mensagemErro = document.getElementById('mensagem-erro');
const previaItens = document.getElementById('previa-itens');
const listaIitensImportados = document.getElementById('lista-itens-importados');

// Estado da importação
let itensImportados = [];
let tipoContratacaoAtual = null;

// Abrir modal
btnImportar.addEventListener('click', function() {
    tipoContratacaoAtual = document.querySelector('input[name="tipo_contratacao"]:checked')?.value;
    
    if (!tipoContratacaoAtual) {
        alert('Selecione o tipo de contratação primeiro (Por Item ou Por Lote).');
        return;
    }
    
    resetarModal();
    modalImportar.classList.remove('hidden');
    setTimeout(() => modalImportar.classList.add('show'), 10);
});

// Fechar modal
function fecharModal() {
    modalImportar.classList.remove('show');
    setTimeout(() => {
        modalImportar.classList.add('hidden');
        resetarModal();
    }, 300);
}

btnCancelar.addEventListener('click', fecharModal);
overlay.addEventListener('click', fecharModal);

// Resetar estado do modal
function resetarModal() {
    arquivoInput.value = '';
    nomeArquivoSpan.classList.add('hidden');
    areaProgresso.classList.add('hidden');
    mensagemErro.classList.add('hidden');
    previaItens.classList.add('hidden');
    btnConfirmar.disabled = true;
    itensImportados = [];
}

// Baixar modelo
btnBaixarModelo.addEventListener('click', function() {
    const headers = ['Descricao', 'Unidade', 'Quantidade'];
    const exemplo = ['Caneta Azul', 'unidade', '50'];
    
    let csvContent = headers.join(',') + '\n' + exemplo.join(',');
    
    const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.href = url;
    link.setAttribute('download', 'modelo_importacao_itens.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
});

// Upload do arquivo
arquivoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    if (file.size > 10 * 1024 * 1024) {
        mostrarErro('Arquivo muito grande. Máximo 10MB.');
        return;
    }
    
    nomeArquivoSpan.textContent = `Arquivo selecionado: ${file.name}`;
    nomeArquivoSpan.classList.remove('hidden');
    
    enviarArquivo(file);
});

function enviarArquivo(file) {
    areaProgresso.classList.remove('hidden');
    barraProgresso.style.width = '30%';
    percentualProgresso.textContent = '30%';
    
    const formData = new FormData();
    formData.append('arquivo_excel', file);
    
    fetch('{{ route("admin.etps.importar-itens") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => {
        barraProgresso.style.width = '70%';
        percentualProgresso.textContent = '70%';
        return response.json();
    })
    .then(data => {
        barraProgresso.style.width = '100%';
        percentualProgresso.textContent = '100%';
        
        setTimeout(() => {
            if (data.success) {
                itensImportados = data.itens;
                mostrarPrevia(itensImportados);
                btnConfirmar.disabled = false;
            } else {
                mostrarErro(data.message);
            }
            areaProgresso.classList.add('hidden');
        }, 500);
    })
    .catch(error => {
        mostrarErro('Erro ao enviar arquivo: ' + error.message);
        areaProgresso.classList.add('hidden');
    });
}

function mostrarErro(mensagem) {
    mensagemErro.textContent = mensagem;
    mensagemErro.classList.remove('hidden');
    previaItens.classList.add('hidden');
}

function mostrarPrevia(itens) {
    listaIitensImportados.innerHTML = '';
    
    itens.forEach(item => {
        const li = document.createElement('li');
        li.className = 'flex justify-between items-center text-sm';
        li.innerHTML = `
            <span class="font-medium">${item.descricao}</span>
            <span class="text-gray-600">${item.quantidade} ${item.unidade}</span>
        `;
        listaIitensImportados.appendChild(li);
    });
    
    previaItens.classList.remove('hidden');
}

// Confirmar importação
btnConfirmar.addEventListener('click', function() {
    if (!itensImportados.length) return;
    
    if (tipoContratacaoAtual === 'item') {
        importarItensSemLote(itensImportados);
    } else {
        importarItensComLote(itensImportados);
    }
    
    fecharModal();
});

function importarItensSemLote(itens) {
    const container = document.getElementById('itens-selecionados-sem-lote');
    
    itens.forEach(item => {
        const itemExistente = document.getElementById(`item-itens-selecionados-sem-lote-${item.item_id}`);
        if (itemExistente) return;
        
        const itemDiv = document.createElement('div');
        itemDiv.className = 'flex items-center justify-between bg-white border rounded-lg p-3 shadow-sm';
        itemDiv.id = `item-itens-selecionados-sem-lote-${item.item_id}`;
        
        itemDiv.innerHTML = `
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-800 mb-2">${item.descricao}</p>
                <div class="flex gap-3">
                    <input type="hidden" name="itens[${item.item_id}][item_id]" value="${item.item_id}">
                    <select name="itens[${item.item_id}][unidade]" class="px-2 py-1 border border-gray-300 rounded text-sm" required>
                        <option value="unidade" ${item.unidade === 'unidade' ? 'selected' : ''}>Unidade</option>
                        <option value="pacote" ${item.unidade === 'pacote' ? 'selected' : ''}>Pacote</option>
                        <option value="caixa" ${item.unidade === 'caixa' ? 'selected' : ''}>Caixa</option>
                        <option value="metro" ${item.unidade === 'metro' ? 'selected' : ''}>Metro</option>
                        <option value="quilograma" ${item.unidade === 'quilograma' ? 'selected' : ''}>Quilograma</option>
                        <option value="litro" ${item.unidade === 'litro' ? 'selected' : ''}>Litro</option>
                    </select>
                    <input type="number" name="itens[${item.item_id}][quantidade]" value="${item.quantidade}" placeholder="Qtd" min="1" required class="px-2 py-1 border border-gray-300 rounded text-sm w-20">
                </div>
            </div>
            <button type="button" class="ml-4 text-red-500 hover:text-red-700 font-bold" onclick="removerItemSelecionado(${item.item_id}, null)">✕</button>
        `;
        
        container.appendChild(itemDiv);
        
        const checkbox = document.querySelector(`.item-checkbox[value="${item.item_id}"]:not([data-lote-index])`);
        if (checkbox) {
            checkbox.checked = true;
        }
    });
}

function importarItensComLote(itens) {
    const lotes = document.querySelectorAll('.lote-card');
    if (!lotes.length) {
        alert('Crie um lote antes de importar os itens.');
        return;
    }
    
    const loteIndex = prompt('Digite o número do lote (1 a ' + lotes.length + ') para importar os itens:');
    if (!loteIndex) return;
    
    const index = parseInt(loteIndex) - 1;
    if (isNaN(index) || index < 0 || index >= lotes.length) {
        alert('Número de lote inválido.');
        return;
    }
    
    const container = document.getElementById(`itens-selecionados-lote-${index}`);
    if (!container) return;
    
    itens.forEach(item => {
        const itemExistente = document.getElementById(`item-itens-selecionados-lote-${index}-${item.item_id}`);
        if (itemExistente) return;
        
        const itemDiv = document.createElement('div');
        itemDiv.className = 'flex items-center justify-between bg-white border rounded-lg p-3 shadow-sm';
        itemDiv.id = `item-itens-selecionados-lote-${index}-${item.item_id}`;
        
        itemDiv.innerHTML = `
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-800 mb-2">${item.descricao}</p>
                <div class="flex gap-3">
                    <input type="hidden" name="lotes[${index}][itens][${item.item_id}][item_id]" value="${item.item_id}">
                    <select name="lotes[${index}][itens][${item.item_id}][unidade]" class="px-2 py-1 border border-gray-300 rounded text-sm" required>
                        <option value="unidade" ${item.unidade === 'unidade' ? 'selected' : ''}>Unidade</option>
                        <option value="pacote" ${item.unidade === 'pacote' ? 'selected' : ''}>Pacote</option>
                        <option value="caixa" ${item.unidade === 'caixa' ? 'selected' : ''}>Caixa</option>
                        <option value="metro" ${item.unidade === 'metro' ? 'selected' : ''}>Metro</option>
                        <option value="quilograma" ${item.unidade === 'quilograma' ? 'selected' : ''}>Quilograma</option>
                        <option value="litro" ${item.unidade === 'litro' ? 'selected' : ''}>Litro</option>
                    </select>
                    <input type="number" name="lotes[${index}][itens][${item.item_id}][quantidade]" value="${item.quantidade}" placeholder="Qtd" min="1" required class="px-2 py-1 border border-gray-300 rounded text-sm w-20">
                </div>
            </div>
            <button type="button" class="ml-4 text-red-500 hover:text-red-700 font-bold" onclick="removerItemSelecionado(${item.item_id}, ${index})">✕</button>
        `;
        
        container.appendChild(itemDiv);
        
        const checkbox = document.querySelector(`.item-checkbox[value="${item.item_id}"][data-lote-index="${index}"]`);
        if (checkbox) {
            checkbox.checked = true;
        }
    });
}
</script>
@endsection