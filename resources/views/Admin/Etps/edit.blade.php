@extends('layouts.app')
@section('page-title', 'Editar ETP')
@section('page-subtitle', 'Edite o Estudo Técnico Preliminar')

@section('content')
    <div class="py-8">
        <div class="mb-4 flex justify-between items-center">
            <a href="{{ route('admin.etps.show', $etp->id) }}"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Voltar para Visualização
            </a>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600">Status:</span>
                <span
                    class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full
                @if ($etp->status === 'pendente') bg-yellow-100 text-yellow-800
                @elseif($etp->status === 'em_analise') bg-blue-100 text-blue-800
                @elseif($etp->status === 'aprovado') bg-green-100 text-green-800
                @elseif($etp->status === 'em_processo') bg-purple-100 text-purple-800
                @elseif($etp->status === 'recusado') bg-red-100 text-red-800 @endif">
                    {{ ucfirst(str_replace('_', ' ', $etp->status)) }}
                </span>
            </div>
        </div>

        @if ($errors->any())
            <div class="p-4 mb-8 mt-10 border border-red-200 shadow-sm rounded-2xl bg-gradient-to-r from-red-50 to-red-100">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
                {{-- Campo oculto que define se é rascunho ou conclusão --}}
                <input type="hidden" id="action_type" name="action_type" value="concluir">

                {{-- Campo para indicar se deve redirecionar ou não --}}
                <input type="hidden" id="should_redirect" name="should_redirect" value="1">

                <!-- PROGRESS BAR STEPS -->
                <div class="w-full mb-8 relative">
                    <div class="absolute w-full h-1 bg-gray-200 rounded-full top-5"></div>
                    <div id="progress-bar" class="absolute h-1 bg-[#009496] rounded-full top-5 transition-all duration-300"
                        style="width: 33%"></div>
                    <div class="flex justify-between mx-auto items-center relative z-10">
                        <div class="text-center w-1/3">
                            <div class="w-10 h-10 mx-auto bg-[#009496] rounded-full text-white flex items-center justify-center font-bold border-4 border-white shadow-sm"
                                id="indicator-1">1</div>
                            <p class="mt-2 text-sm font-medium text-[#009496]" id="label-1">Dados Iniciais</p>
                        </div>
                        <div class="text-center w-1/3">
                            <div class="w-10 h-10 mx-auto bg-gray-200 rounded-full text-gray-500 flex items-center justify-center font-bold border-4 border-white shadow-sm transition-colors duration-300"
                                id="indicator-2">2</div>
                            <p class="mt-2 text-sm font-medium text-gray-400 transition-colors duration-300" id="label-2">
                                Objeto</p>
                        </div>
                        <div class="text-center w-1/3">
                            <div class="w-10 h-10 mx-auto bg-gray-200 rounded-full text-gray-500 flex items-center justify-center font-bold border-4 border-white shadow-sm transition-colors duration-300"
                                id="indicator-3">3</div>
                            <p class="mt-2 text-sm font-medium text-gray-400 transition-colors duration-300" id="label-3">
                                Contratação</p>
                        </div>
                    </div>
                </div>

                <!-- PASSO 1 -->
                <div id="step-1" class="step-content block">
                    <h4 class="text-lg font-semibold text-gray-800 mb-6 border-b pb-2">Passo 1: Selecione a Secretaria e
                        Responsável</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Secretaria da Prefeitura *</label>
                            <select name="secretaria_id" id="secretaria_id"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent"
                                required>
                                <option value="">Selecione...</option>
                                @foreach ($secretarias as $sec)
                                    <option value="{{ $sec->id }}" data-servidor="{{ $sec->servidor_responsavel }}"
                                        {{ old('secretaria_id', $etp->secretaria_id) == $sec->id ? 'selected' : '' }}>
                                        {{ $sec->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Servidor Responsável</label>
                            <input type="text" name="servidor_responsavel" id="servidor_responsavel"
                                value="{{ old('servidor_responsavel', $etp->servidor_responsavel) }}"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent"
                                required>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Modalidade da Licitação *</label>
                        <select name="modalidade" id="modalidade"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent"
                            required onchange="toggleModalidadeFields()">
                            <option value="">Selecione...</option>
                            <option value="pregao"
                                {{ old('modalidade', $etp->modalidade) == 'pregao' ? 'selected' : '' }}>Pregão
                            </option>
                            <option value="concorrencia"
                                {{ old('modalidade', $etp->modalidade) == 'concorrencia' ? 'selected' : '' }}>
                                Concorrência</option>
                            <option value="dispensa"
                                {{ old('modalidade', $etp->modalidade) == 'dispensa' ? 'selected' : '' }}>Dispensa
                            </option>
                            <option value="inexigibilidade"
                                {{ old('modalidade', $etp->modalidade) == 'inexigibilidade' ? 'selected' : '' }}>
                                Inexigibilidade</option>
                        </select>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="button"
                            class="btn-next px-6 py-2.5 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all"
                            onclick="nextStep(2)">
                            Próximo Passo <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>

                <!-- PASSO 2 -->
                <div id="step-2" class="step-content hidden">
                    <h4 class="text-lg font-semibold text-gray-800 mb-6 border-b pb-2">Passo 2: Objeto da Licitação</h4>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Especificar o objeto da licitação
                            *</label>
                        <textarea name="objeto_licitacao" id="objeto_licitacao" rows="5"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent resize-y"
                            required>{{ old('objeto_licitacao', $etp->objeto_licitacao) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Descreva detalhadamente o que será adquirido ou contratado.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Justificativa da Necessidade *</label>
                        <textarea name="justificativa_necessidade" id="justificativa_necessidade" rows="4"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent resize-y"
                            required>{{ old('justificativa_necessidade', $etp->justificativa_necessidade ?? '') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Explique detalhadamente por que esta contratação é necessária.</p>
                    </div>
                    <div class="mt-8 flex justify-between">
                        <button type="button"
                            class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all"
                            onclick="prevStep(1)">
                            <i class="fas fa-arrow-left mr-2"></i> Voltar
                        </button>
                        <button type="button"
                            class="px-6 py-2.5 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all"
                            onclick="nextStep(3)">
                            Próximo Passo <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>

                <!-- PASSO 3 -->
                <div id="step-3" class="step-content hidden">
                    <h4 class="text-lg font-semibold text-gray-800 mb-6 border-b pb-2">Passo 3: Tipo de Contratação e
                        Anexos</h4>

                    <div id="campos-itens-contratacao">

                        {{-- TIPO DE CONTRATAÇÃO --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de contratação *</label>

                            <div id="tipo-opcoes-pregao" class="flex items-center space-x-6 hidden">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="radio" name="tipo_contratacao" value="item"
                                        class="form-radio text-[#009496] w-5 h-5"
                                        {{ old('tipo_contratacao', $etp->tipo_contratacao) == 'item' ? 'checked' : '' }}
                                        onchange="toggleContratacaoTipo()">
                                    <span class="ml-2">Por Item</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer border-l pl-6 border-gray-200">
                                    <input type="radio" name="tipo_contratacao" value="lote"
                                        class="form-radio text-[#009496] w-5 h-5"
                                        {{ old('tipo_contratacao', $etp->tipo_contratacao) == 'lote' ? 'checked' : '' }}
                                        onchange="toggleContratacaoTipo()">
                                    <span class="ml-2">Por Lote</span>
                                </label>
                            </div>

                            <div id="tipo-opcoes-dispensa" class="flex items-center space-x-6 hidden">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="radio" name="tipo_contratacao" value="servicos"
                                        class="form-radio text-[#009496] w-5 h-5"
                                        {{ old('tipo_contratacao', $etp->tipo_contratacao) == 'servicos' ? 'checked' : '' }}
                                        onchange="toggleContratacaoTipo()">
                                    <span class="ml-2">Serviços</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer border-l pl-6 border-gray-200">
                                    <input type="radio" name="tipo_contratacao" value="compras"
                                        class="form-radio text-[#009496] w-5 h-5"
                                        {{ old('tipo_contratacao', $etp->tipo_contratacao) == 'compras' ? 'checked' : '' }}
                                        onchange="toggleContratacaoTipo()">
                                    <span class="ml-2">Compras</span>
                                </label>
                            </div>
                        </div>

                        {{-- BOTÕES: IMPORTAR EXCEL + CRIAR ITEM RÁPIDO --}}
                        <div class="mb-4 flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                    Novo Item
                                </button>
                                <button type="button" id="btnConsultarPncp" onclick="abrirModalPncp()"
                                    class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-all"
                                    title="Consultar preços de referência no PNCP">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Consultar PNCP
                                </button>
                            </div>
                            <button type="button" id="btnImportarItens"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Importar Itens via Excel
                            </button>
                        </div>

                        {{-- ÁREA DE ITENS (sem lote) --}}
                        @php $tipoAtual = old('tipo_contratacao', $etp->tipo_contratacao); @endphp
                        <div id="area-itens-sem-lote" class="{{ $tipoAtual === 'lote' ? 'hidden' : 'block' }}">
                            @include('Admin.Etps.partials.itens-selector-edit', [
                                'loteIndex' => null,
                                'etp' => $etp,
                            ])
                        </div>

                        {{-- ÁREA DE LOTES --}}
                        <div id="area-lotes" class="{{ $tipoAtual === 'lote' ? 'block' : 'hidden' }}">
                            <div class="mb-4 flex justify-between items-center">
                                <h5 class="text-md font-semibold text-gray-700">Lotes da Contratação</h5>
                                <button type="button" onclick="adicionarLote()"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Novo Lote
                                </button>
                            </div>
                            <div id="lotes-container" class="space-y-6">
                                @if ($etp->tipo_contratacao == 'lote' && $etp->lotes->count() > 0)
                                    @foreach ($etp->lotes as $index => $lote)
                                        @include('Admin.Etps.partials.lote-card-edit', [
                                            'loteIndex' => $index,
                                            'lote' => $lote,
                                            'itens' => $itens,
                                        ])
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Prazo --}}
                    <div class="mb-4 mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Prazo de Entrega *</label>
                        <input type="text" name="prazo_entrega" id="prazo_entrega"
                            value="{{ old('prazo_entrega', $etp->prazo_entrega) }}"
                            placeholder="Ex: 30 dias após emissão da nota"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496]"
                            required>
                    </div>

                    {{-- Dotação Orçamentária --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dotação Orçamentária *</label>
                        <textarea name="dotacao_orcamentaria" id="dotacao_orcamentaria" placeholder="Digite a dotação orçamentária"
                            rows="3"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent resize-y"
                            required>{{ old('dotacao_orcamentaria', $etp->dotacao_orcamentaria) }}</textarea>
                    </div>

                    {{-- Cotação / Projeto Básico --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2" id="label_pdf_anexo">
                            Anexar Cotação do Fornecedor Local
                        </label>
                        @if ($etp->cotacao_path)
                            <div class="mb-2 p-2 bg-gray-50 rounded-lg flex items-center justify-between">
                                <span class="text-sm text-gray-600">
                                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                    Arquivo atual: {{ basename($etp->cotacao_path) }}
                                </span>
                                <span class="text-xs text-gray-500">(Envie um novo arquivo para substituir)</span>
                            </div>
                        @endif
                        <input type="file" name="cotacao_path" id="cotacao_path"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                        file:text-sm file:font-semibold file:bg-[#009496]/10 file:text-[#009496]
                        hover:file:bg-[#009496]/20">
                        <p class="mt-1 text-xs text-gray-500">Máximo 10MB. Para concorrência ou inexigibilidade, anexe o
                            Projeto Básico.</p>
                    </div>

                    <div class="mt-8 flex justify-between items-center">
                        <button type="button"
                            class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                            onclick="prevStep(2)">
                            <i class="fas fa-arrow-left mr-2"></i> Voltar
                        </button>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="confirmarCancelamento()"
                                class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all">
                                Cancelar
                            </button>
                            {{-- Salvar como rascunho (mantém status pendente) --}}
                            <button type="button" onclick="submeterEtp('salvar')"
                                class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-all shadow-sm">
                                <i class="fas fa-save mr-2"></i> Salvar
                            </button>
                            {{-- Concluir (envia para análise → status em_analise) --}}
                            <button type="button" onclick="submeterEtp('concluir')"
                                class="px-8 py-3 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-md transition-all">
                                <i class="fas fa-check-circle mr-2"></i> Concluir ETP
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         MODAL: CONSULTA PNCP
    ════════════════════════════════════════════════════════ --}}
    <div id="modalPncp" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="fecharModalPncp()"></div>
        <div class="relative bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-4xl mx-4 overflow-hidden z-10 border border-white/20">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4 flex items-center justify-between text-white">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/20 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold leading-tight">Consulta de Preços PNCP</h3>
                        <p class="text-xs text-blue-100 opacity-80">Portal Nacional de Contratações Públicas</p>
                    </div>
                </div>
                <button type="button" onclick="fecharModalPncp()" class="p-2 hover:bg-white/10 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <div class="mb-6">
                    <div class="relative">
                        <input type="text" id="pncp_busca_termo" 
                            placeholder="Pesquise por item (ex: Ar condicionado, Caneta, Limpeza)..."
                            class="w-full pl-12 pr-4 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 shadow-inner text-lg transition-all"
                            autocomplete="off">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-[10px] text-gray-400 uppercase font-bold tracking-wider px-1">Dica: Use termos específicos para melhores resultados</p>
                </div>

                <div id="pncp_resultados" class="max-h-[50vh] overflow-y-auto custom-scrollbar px-1">
                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                        <div class="p-4 bg-gray-50 rounded-full mb-4">
                            <svg class="w-12 h-12 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <p class="text-sm">Digite o que deseja pesquisar acima</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex justify-between items-center text-xs text-gray-500 border-t">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    API Oficial PNCP Conectada
                </span>
                <span class="italic">Os preços são de caráter referencial para o ETP.</span>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>

    {{-- Modal: Importar Itens via Excel --}}
    @include('Admin.Etps.partials.modal-importar-itens')

    {{-- ═══════════════════════════════════════════════════════
     MODAL: CRIAR ITEM RÁPIDO
════════════════════════════════════════════════════════ --}}
    <div id="modalCriarItem" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <div id="modal-criar-item-overlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Criar Novo Item</h3>
                <button type="button" onclick="fecharModalCriarItem()"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Descrição do Item *</label>
                <input type="text" id="novo_item_descricao" placeholder="Ex: Caneta esferográfica azul"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent text-sm"
                    autocomplete="off">
                <p class="mt-1 text-xs text-gray-500">O item ficará disponível para seleção imediatamente após a criação.
                </p>
            </div>

            <div id="criar-item-erro"
                class="hidden mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700"></div>
            <div id="criar-item-sucesso"
                class="hidden mt-3 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700"></div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="fecharModalCriarItem()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all">
                    Cancelar
                </button>
                <button type="button" id="btnConfirmarCriarItem" onclick="confirmarCriarItem()"
                    class="px-5 py-2 text-sm font-semibold text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all">
                    <i class="fas fa-plus mr-1"></i> Criar Item
                </button>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            /* PNCP INTEGRATION */
            const modalPncp = document.getElementById('modalPncp');
            const inputBuscaPncp = document.getElementById('pncp_busca_termo');
            const resultadosPncp = document.getElementById('pncp_resultados');
            let timeoutPncp = null;

            window.abrirModalPncp = function() {
                modalPncp.classList.remove('hidden');
                setTimeout(() => inputBuscaPncp.focus(), 100);
            };

            window.fecharModalPncp = function() {
                modalPncp.classList.add('hidden');
            };

            inputBuscaPncp.addEventListener('input', () => {
                clearTimeout(timeoutPncp);
                const termo = inputBuscaPncp.value.trim();
                if (termo.length >= 3) {
                    timeoutPncp = setTimeout(executarBuscaPncp, 600);
                }
            });

            window.executarBuscaPncp = function() {
                const termo = inputBuscaPncp.value.trim();
                if (termo.length < 3) return;

                resultadosPncp.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-12">
                        <div class="w-12 h-12 border-4 border-[var(--primary)]/20 border-t-[var(--primary)] rounded-full animate-spin mb-4"></div>
                        <p class="text-sm text-gray-500 animate-pulse">Consultando base nacional...</p>
                    </div>`;

                fetch(`{{ route('admin.etps.pncp.search') }}?termo=${encodeURIComponent(termo)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            renderizarResultadosPncp(data.data);
                        } else {
                            resultadosPncp.innerHTML = `<div class="p-4 text-red-600 bg-red-50 rounded-2xl border border-red-100 text-sm text-center">${data.message}</div>`;
                        }
                    })
                    .catch(err => {
                        resultadosPncp.innerHTML = '<div class="p-4 text-red-600 bg-red-50 rounded-2xl border border-red-100 text-sm text-center">Erro ao conectar com o servidor.</div>';
                    });
            };

            function renderizarResultadosPncp(data) {
                if (!data.data || data.data.length === 0) {
                    resultadosPncp.innerHTML = '<div class="p-12 text-center text-gray-500">Nenhum resultado encontrado no PNCP para este termo.</div>';
                    return;
                }

                let html = '<div class="grid gap-3 mb-4">';
                data.data.forEach(item => {
                    html += `
                    <div class="group p-4 bg-white border border-gray-100 rounded-2xl hover:border-[var(--primary)]/30 hover:shadow-md transition-all cursor-pointer relative overflow-hidden" 
                        onclick="verItensPncp('${item.orgaoEntidade.cnpj}', '${item.anoCompra}', '${item.sequencialCompra}')">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-transparent group-hover:bg-[var(--primary)] transition-all"></div>
                        <div class="flex justify-between items-start gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="px-2 py-0.5 bg-[var(--primary)]/10 text-[var(--primary)] text-[9px] font-bold rounded uppercase tracking-wider">${item.modalidadeNome}</span>
                                    <span class="text-[10px] font-bold text-gray-400">#${item.sequencialCompra}/${item.anoCompra}</span>
                                </div>
                                <h5 class="font-bold text-gray-800 text-sm mb-1 group-hover:text-[var(--gradient-start)] transition-colors">${item.orgaoEntidade.razaoSocial}</h5>
                                <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">${item.objeto}</p>
                            </div>
                            <div class="flex-shrink-0 flex flex-col items-end gap-1">
                                <span class="text-[10px] text-gray-400">${new Date(item.dataPublicacaoPncp).toLocaleDateString()}</span>
                                <div class="w-8 h-8 rounded-full bg-[var(--primary)]/10 flex items-center justify-center text-[var(--primary)] group-hover:bg-[var(--primary)] group-hover:text-white transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });
                html += '</div>';
                
                // Paginação simples
                if (data.totalPaginas > 1) {
                    html += `<div class="flex justify-center py-4 text-xs text-gray-400">Página ${data.numeroPagina} de ${data.totalPaginas}</div>`;
                }

                resultadosPncp.innerHTML = html;
            }

            window.verItensPncp = function(cnpj, ano, sequencial) {
                resultadosPncp.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-12">
                        <div class="w-12 h-12 border-4 border-[var(--primary)]/20 border-t-[var(--primary)] rounded-full animate-spin mb-4"></div>
                        <p class="text-sm text-gray-500 animate-pulse">Obtendo detalhes dos itens...</p>
                    </div>`;

                fetch(`/admin/etps/pncp/items/${cnpj}/${ano}/${sequencial}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            renderizarItensPncp(data.data);
                        } else {
                            alert(data.message);
                            executarBuscaPncp();
                        }
                    });
            };

            function renderizarItensPncp(itens) {
                if (!itens || itens.length === 0) {
                    resultadosPncp.innerHTML = '<div class="p-8 text-center text-gray-500">Nenhum item encontrado nesta contratação.</div>';
                    return;
                }

                let html = `
                    <div class="flex items-center justify-between mb-4 px-1">
                        <button onclick="executarBuscaPncp()" class="inline-flex items-center text-xs font-bold text-[var(--primary)] hover:text-[var(--gradient-start)] transition-colors uppercase tracking-wider">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Voltar
                        </button>
                        <span class="text-xs text-gray-400 font-medium">${itens.length} itens encontrados</span>
                    </div>`;
                
                html += '<div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">';
                html += '<div class="overflow-x-auto"><table class="w-full text-sm text-left">';
                html += '<thead class="bg-gray-50/50 text-gray-500 font-bold uppercase text-[9px] border-b border-gray-100"><tr><th class="px-4 py-3">#</th><th class="px-4 py-3">Descrição</th><th class="px-4 py-3">Qtd</th><th class="px-4 py-3 text-right">Valor Unitário</th></tr></thead>';
                html += '<tbody class="divide-y divide-gray-50">';
                
                itens.forEach(item => {
                    const valorFormatado = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(item.valorUnitario || 0);
                    html += `
                    <tr class="hover:bg-[var(--primary)]/5 transition-colors group">
                        <td class="px-4 py-4 font-bold text-gray-400 text-xs">${item.numeroItem}</td>
                        <td class="px-4 py-4 font-medium text-gray-700 leading-tight">${item.descricao}</td>
                        <td class="px-4 py-4 text-xs text-gray-500">${item.quantidade} <span class="opacity-60">${item.unidadeMedida}</span></td>
                        <td class="px-4 py-4 text-right">
                            <div class="font-bold text-green-600">${valorFormatado}</div>
                            <div class="text-[9px] text-gray-400 font-bold uppercase">Total: ${new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format((item.valorUnitario || 0) * item.quantidade)}</div>
                        </td>
                    </tr>`;
                });
                
                html += '</tbody></table></div></div>';
                resultadosPncp.innerHTML = html;
            }

            let loteCounter = {{ $etp->lotes->count() }};

            const secretariaSelect = document.getElementById('secretaria_id');
            const servidorInput = document.getElementById('servidor_responsavel');
            const modalidadeSelect = document.getElementById('modalidade');
            const cotacaoInput = document.getElementById('cotacao_path');
            const camposItens = document.getElementById('campos-itens-contratacao');
            const labelPdf = document.getElementById('label_pdf_anexo');

            /* ── SECRETARIA → SERVIDOR AUTO ── */
            function preencherServidor() {
                const selected = secretariaSelect.options[secretariaSelect.selectedIndex];
                const servidor = selected?.getAttribute('data-servidor');
                if (!servidorInput.value.trim()) servidorInput.value = servidor ?? '';
            }
            preencherServidor();
            secretariaSelect.addEventListener('change', preencherServidor);

            /* ── MODALIDADE ── */
            window.toggleModalidadeFields = function() {
                const modalidade = modalidadeSelect.value;
                const radiosTipo = document.querySelectorAll('input[name="tipo_contratacao"]');
                const opcoesPregao = document.getElementById('tipo-opcoes-pregao');
                const opcoesDisp = document.getElementById('tipo-opcoes-dispensa');
                const areaItensSemLote = document.getElementById('area-itens-sem-lote');
                const areaLotes = document.getElementById('area-lotes');
                const camposItens = document.getElementById('campos-itens-contratacao');
                const labelPdf = document.getElementById('label_pdf_anexo');
                const cotacaoInput = document.getElementById('cotacao_path');

                radiosTipo.forEach(el => {
                    el.checked = false;
                    el.required = false;
                });
                opcoesPregao.classList.add('hidden');
                opcoesDisp.classList.add('hidden');
                camposItens.classList.remove('hidden');
                labelPdf.innerText = 'Anexar Cotação do Fornecedor Local';
                cotacaoInput.removeAttribute('required');

                if (modalidade === 'concorrencia' || modalidade === 'inexigibilidade') {
                    camposItens.classList.add('hidden');
                    areaItensSemLote.classList.add('hidden');
                    areaLotes.classList.add('hidden');
                    labelPdf.innerText = 'Anexar Projeto Básico *';
                    cotacaoInput.setAttribute('required', 'required');

                } else if (modalidade === 'dispensa') {
                    opcoesDisp.classList.remove('hidden');
                    const valorSalvo = '{{ old('tipo_contratacao', $etp->tipo_contratacao) }}';
                    if (valorSalvo === 'servicos' || valorSalvo === 'compras' || valorSalvo === 'obras') {
                        const radioEl = document.querySelector(`input[name="tipo_contratacao"][value="${valorSalvo}"]`);
                        if (radioEl) radioEl.checked = true;
                    }
                    opcoesDisp.querySelectorAll('input[type="radio"]').forEach(el => {
                        el.required = true;
                    });
                    areaItensSemLote.classList.remove('hidden');
                    areaLotes.classList.add('hidden');
                    toggleContratacaoTipo();

                } else if (modalidade === 'pregao') {
                    opcoesPregao.classList.remove('hidden');
                    const valorSalvo = '{{ old('tipo_contratacao', $etp->tipo_contratacao) }}';
                    if (valorSalvo === 'item' || valorSalvo === 'lote') {
                        const radioEl = document.querySelector(`input[name="tipo_contratacao"][value="${valorSalvo}"]`);
                        if (radioEl) radioEl.checked = true;
                    }
                    opcoesPregao.querySelectorAll('input[type="radio"]').forEach(el => {
                        el.required = true;
                    });
                    toggleContratacaoTipo();

                } else {
                    camposItens.classList.add('hidden');
                    areaItensSemLote.classList.add('hidden');
                    areaLotes.classList.add('hidden');
                }
            };

            /* ── TIPO CONTRATAÇÃO ── */
            window.toggleContratacaoTipo = function() {
                const selected = document.querySelector('input[name="tipo_contratacao"]:checked');
                if (!selected) return;
                const val = selected.value;
                const areaSemLote = document.getElementById('area-itens-sem-lote');
                const areaLotes = document.getElementById('area-lotes');
                const camposItensContratacao = document.getElementById('campos-itens-contratacao');
                const labelPdf = document.getElementById('label_pdf_anexo');

                // Se for OBRAS, esconde a área de itens e muda o label do PDF
                if (val === 'obras') {
                    areaSemLote.classList.add('hidden');
                    areaLotes.classList.add('hidden');
                    labelPdf.innerText = 'Anexar Projeto Básico *';
                    document.getElementById('cotacao_path').setAttribute('required', 'required');
                }
                else if (val === 'lote') {
                    areaSemLote.classList.add('hidden');
                    areaLotes.classList.remove('hidden');
                    labelPdf.innerText = 'Anexar Cotação do Fornecedor Local';
                    document.getElementById('cotacao_path').removeAttribute('required');
                    if (document.querySelectorAll('.lote-card').length === 0) adicionarLote();
                }
                else {
                    areaSemLote.classList.remove('hidden');
                    areaLotes.classList.add('hidden');
                    labelPdf.innerText = 'Anexar Cotação do Fornecedor Local';
                    document.getElementById('cotacao_path').removeAttribute('required');
                }
            };

            /* ── LOTES ── */
            window.adicionarLote = function() {
                const container = document.getElementById('lotes-container');
                const loteDiv = document.createElement('div');
                loteDiv.className = 'lote-card border border-gray-200 rounded-xl p-6 bg-gray-50 relative';
                loteDiv.id = `lote-${loteCounter}`;
                loteDiv.innerHTML = `
            <button type="button" onclick="removerLote(this)" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="mb-4 pr-8">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Lote *</label>
                <input type="text" name="lotes[${loteCounter}][nome]"
                    placeholder="Ex: Lote ${loteCounter + 1} - Materiais de Escritório"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496]" required>
            </div>
            ${gerarItensSelector(loteCounter)}`;
                container.appendChild(loteDiv);
                inicializarBuscaLote(loteCounter);
                loteCounter++;
            };

            function gerarItensSelector(loteIndex) {
                const buscaId = `buscar_item_lote_${loteIndex}`;
                const listaId = `lista_itens_lote_${loteIndex}`;
                const containerId = `itens-selecionados-lote-${loteIndex}`;

                // Passa os itens do PHP para JavaScript
                const itens = @json($itens);

                let itensOptions = '';
                itens.forEach(item => {
                    itensOptions += `
                    <label class="flex items-center space-x-3 item-option" data-descricao="${item.descricao_item.toLowerCase()}">
                        <input type="checkbox" value="${item.id}"
                            data-descricao="${item.descricao_item}"
                            data-lote-index="${loteIndex}"
                            class="item-checkbox w-4 h-4 text-[#009496]"
                            onchange="toggleItemSelecionado(this, ${loteIndex})">
                        <span class="text-sm text-gray-700">${item.descricao_item}</span>
                    </label>`;
                });

                return `
                <div class="mb-4 itens-selector" data-lote-index="${loteIndex}">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selecionar Itens *</label>
                    <div class="mb-3">
                        <input type="text" id="${buscaId}" placeholder="Buscar item..."
                            class="buscar-item w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496]"
                            data-lista="${listaId}">
                    </div>
                    <div id="${listaId}" class="hidden border border-gray-200 rounded-lg p-4 max-h-60 overflow-y-auto space-y-2 bg-gray-50">
                        ${itensOptions}
                    </div>
                    <div class="mt-6">
                        <h5 class="text-sm font-semibold mb-2 text-gray-700">Itens Selecionados - Lote ${loteIndex + 1}</h5>
                        <div id="${containerId}" class="space-y-3"></div>
                    </div>
                </div>`;
            }

            window.removerLote = function(button) {
                const loteCard = button.closest('.lote-card');
                if (document.querySelectorAll('.lote-card').length > 1) loteCard.remove();
                else alert('É necessário ter pelo menos um lote.');
            };

            // Normaliza texto removendo acentos e convertendo para minúsculas
            function normalizarTexto(texto) {
                return texto
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');
            }

            /* ── BUSCA ── */
            function inicializarBuscaLote(loteIndex) {
                const buscaId = loteIndex !== undefined ? `buscar_item_lote_${loteIndex}` : 'buscar_item_global';
                const listaId = loteIndex !== undefined ? `lista_itens_lote_${loteIndex}` : 'lista_itens_global';
                const buscaInput = document.getElementById(buscaId);
                if (!buscaInput) return;
                // DEPOIS
                buscaInput.addEventListener('keyup', function() {
                    const termo = normalizarTexto(this.value.trim());
                    const lista = document.getElementById(listaId);
                    if (!lista) return;
                    const itens = lista.querySelectorAll('.item-option');

                    if (termo.length === 0) {
                        lista.classList.add('hidden');
                        itens.forEach(el => el.style.display = 'flex');
                        return;
                    }

                    lista.classList.remove('hidden');
                    itens.forEach(el => {
                        const descricao = normalizarTexto(el.getAttribute('data-descricao') || '');
                        el.style.display = descricao.includes(termo) ? 'flex' : 'none';
                    });
                });
            }

            inicializarBuscaLote();
            document.querySelectorAll('.lote-card').forEach((_, i) => inicializarBuscaLote(i));

            /* ── SELEÇÃO DE ITENS ── */
            window.toggleItemSelecionado = function(checkbox, loteIndex) {
                const id = checkbox.value;
                const descricao = checkbox.dataset.descricao;
                const containerId = loteIndex !== null ? `itens-selecionados-lote-${loteIndex}` :
                    'itens-selecionados-sem-lote';
                const container = document.getElementById(containerId);

                if (checkbox.checked) {
                    const namePrefix = loteIndex !== null ? `lotes[${loteIndex}][itens]` : 'itens';
                    container.insertAdjacentHTML('beforeend', `
                <div class="flex items-center justify-between bg-white border rounded-lg p-3 shadow-sm" id="item-${containerId}-${id}">
                    <div class="flex items-center gap-3 flex-1">
                        <span class="item-numero inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#009496]/10 text-[#009496] text-xs font-bold flex-shrink-0 border border-[#009496]/20">0</span>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800 mb-2">${descricao}</p>
                            <div class="flex gap-3">
                                <input type="hidden" name="${namePrefix}[${id}][item_id]" value="${id}">
                                <input type="text"
                                    name="${namePrefix}[${id}][unidade]"
                                    placeholder="Ex: Unidade, Pacote, Caixa..."
                                    value=""
                                    class="px-2 py-1 border border-gray-300 rounded text-sm w-24"
                                    required>
                                <input type="number" name="${namePrefix}[${id}][quantidade]" placeholder="Qtd" min="1" required class="px-2 py-1 border border-gray-300 rounded text-sm w-20">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="ml-4 text-red-500 hover:text-red-700 font-bold flex-shrink-0" onclick="removerItemSelecionado(${id}, ${loteIndex})">✕</button>
                </div>`);
                } else {
                    removerItemSelecionado(id, loteIndex);
                }
                renumerarItens(containerId);
            };

            window.removerItemSelecionado = function(id, loteIndex) {
                const containerId = loteIndex !== null && loteIndex !== undefined ?
                    `itens-selecionados-lote-${loteIndex}` : 'itens-selecionados-sem-lote';
                const itemDiv = document.getElementById(`item-${containerId}-${id}`);
                if (itemDiv) itemDiv.remove();
                const sel = loteIndex !== null && loteIndex !== undefined ?
                    `.item-checkbox[value="${id}"][data-lote-index="${loteIndex}"]` :
                    `.item-checkbox[value="${id}"]:not([data-lote-index])`;
                const cb = document.querySelector(sel);
                if (cb) cb.checked = false;
                renumerarItens(containerId);
            };

            window.renumerarItens = function(containerId) {
                const container = document.getElementById(containerId);
                if (!container) return;
                container.querySelectorAll('.item-numero').forEach((badge, idx) => {
                    badge.textContent = idx + 1;
                });
            };

            /* ── STEPS ── */
            window.nextStep = function(step) {
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
                // NOVA VALIDAÇÃO: Verificar justificativa
                if (!document.getElementById('justificativa_necessidade').value.trim()) {
                    alert('Preencha a justificativa da necessidade.');
                    return;
                }
            }
            document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
            document.getElementById('step-' + step).classList.remove('hidden');
            updateProgress(step);
        };

            window.prevStep = function(step) {
                document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
                document.getElementById('step-' + step).classList.remove('hidden');
                updateProgress(step);
            };

            window.updateProgress = function(step) {
                document.getElementById('progress-bar').style.width = step === 1 ? '33%' : step === 2 ? '66%' :
                    '100%';
                for (let i = 1; i <= 3; i++) {
                    const ind = document.getElementById('indicator-' + i);
                    const lbl = document.getElementById('label-' + i);
                    if (i <= step) {
                        ind.classList.replace('bg-gray-200', 'bg-[#009496]');
                        ind.classList.replace('text-gray-500', 'text-white');
                        lbl.classList.replace('text-gray-400', 'text-[#009496]');
                    } else {
                        ind.classList.replace('bg-[#009496]', 'bg-gray-200');
                        ind.classList.replace('text-white', 'text-gray-500');
                        lbl.classList.replace('text-[#009496]', 'text-gray-400');
                    }
                }
            };

            /* ── SUBMETER ETP (salvar ou concluir) ── */
            window.submeterEtp = function(tipo) {
                document.getElementById('action_type').value = tipo;

                // Se for "salvar" (rascunho), não redireciona
                // Se for "concluir", redireciona
                if (tipo === 'salvar') {
                    document.getElementById('should_redirect').value = '0';

                    // Envia o formulário via AJAX para não redirecionar
                    enviarFormularioAjax();
                } else {
                    document.getElementById('should_redirect').value = '1';
                    document.getElementById('etpForm').submit();
                }
            };

            // Função para enviar o formulário via AJAX
            function enviarFormularioAjax() {
                const form = document.getElementById('etpForm');
                const formData = new FormData(form);

                // Mostra indicador de loading
                const btnSalvar = document.querySelector('button[onclick="submeterEtp(\'salvar\')"]');
                const textoOriginal = btnSalvar.innerHTML;
                btnSalvar.disabled = true;
                btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Salvando...';

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(async response => {
                        const data = await response.json();
                        if (response.status === 422) {
                            let errorList = 'Verifique os campos:\\n';
                            if (data.errors) {
                                for (let field in data.errors) {
                                    errorList += `• ${data.errors[field][0]}\\n`;
                                }
                            }
                            throw new Error(errorList);
                        }
                        if (!response.ok) throw new Error(data.message || 'Erro ao salvar rascunho.');
                        return data;
                    })
                    .then(data => {
                        if (data.success) {
                            mostrarNotificacao('success', data.message || 'Rascunho atualizado com sucesso!');
                        } else {
                            mostrarNotificacao('error', data.message || 'Erro ao salvar rascunho.');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        let msg = error.message || 'Erro ao salvar rascunho. Tente novamente.';
                        msg = msg.replace(/\\n/g, '<br>');
                        mostrarNotificacao('error', msg);
                    })
                    .finally(() => {
                        btnSalvar.disabled = false;
                        btnSalvar.innerHTML = textoOriginal;
                    });
            }

            // Função para mostrar notificações
            function mostrarNotificacao(tipo, mensagem) {
                // Remove notificações existentes
                const notificacoes = document.querySelectorAll('.notificacao-flutuante');
                notificacoes.forEach(n => {
                    n.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => n.remove(), 300);
                });

                // Cria a notificação
                const notificacao = document.createElement('div');
                
                const bgColor = tipo === 'success' ? 'bg-[#009496]' : 'bg-[#e11d48]';
                const icon = tipo === 'success' 
                    ? '<i class="fas fa-check-circle text-xl"></i>' 
                    : '<i class="fas fa-exclamation-circle text-xl"></i>';

                notificacao.className = `notificacao-flutuante fixed top-6 right-6 z-[9999] px-6 py-4 rounded-2xl shadow-2xl ${bgColor} text-white flex items-start gap-4 transform transition-all duration-500 translate-x-full max-w-md border border-white/20 backdrop-blur-md`;

                notificacao.innerHTML = `
                    <div class="flex-shrink-0 mt-0.5">
                        ${icon}
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold leading-tight mb-1">${tipo === 'success' ? 'Sucesso!' : 'Algo deu errado'}</p>
                        <div class="text-xs font-medium leading-relaxed opacity-95">${mensagem}</div>
                    </div>
                    <button onclick="fecharNotificacao(this)" class="flex-shrink-0 ml-2 p-1 hover:bg-white/20 rounded-lg transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                `;

                document.body.appendChild(notificacao);

                // Animação de entrada
                requestAnimationFrame(() => {
                    setTimeout(() => {
                        notificacao.classList.remove('translate-x-full');
                    }, 50);
                });

                // Remove automaticamente após tempo variável
                const timer = tipo === 'success' ? 4000 : 12000;
                const autoHide = setTimeout(() => {
                    fecharNotificacao(notificacao);
                }, timer);

                notificacao.dataset.timerId = autoHide;
            }

            window.fecharNotificacao = function(el) {
                const target = el instanceof HTMLElement && el.classList.contains('notificacao-flutuante') 
                    ? el 
                    : el.closest('.notificacao-flutuante');
                
                if (target) {
                    target.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => target.remove(), 500);
                }
            }

            window.confirmarCancelamento = function() {
                if (confirm(
                        'Tem certeza que deseja cancelar a edição? As alterações não salvas serão perdidas.')) {
                    window.location.href = '{{ route('admin.etps.show', $etp->id) }}';
                }
            };

            // Inicialização
            toggleModalidadeFields();
            const modalidadeAtual = modalidadeSelect.value;
            const opcoesPregao = document.getElementById('tipo-opcoes-pregao');
            const opcoesDisp = document.getElementById('tipo-opcoes-dispensa');

            if (modalidadeAtual === 'pregao') {
                opcoesPregao.classList.remove('hidden');
                opcoesPregao.querySelectorAll('input[type="radio"]').forEach(el => el.required = true);
            } else if (modalidadeAtual === 'dispensa') {
                opcoesDisp.classList.remove('hidden');
                opcoesDisp.querySelectorAll('input[type="radio"]').forEach(el => el.required = true);
            }

            toggleContratacaoTipo();

            if (document.querySelector('input[name="tipo_contratacao"]:checked')?.value === 'lote') {
                if (document.querySelectorAll('.lote-card').length === 0) adicionarLote();
            }
        });

        /* ══════════════════════════════════════════════════════════
           MODAL: CRIAR ITEM RÁPIDO
        ══════════════════════════════════════════════════════════ */
        window.abrirModalCriarItem = function() {
            document.getElementById('modalCriarItem').classList.remove('hidden');
            document.getElementById('novo_item_descricao').value = '';
            document.getElementById('criar-item-erro').classList.add('hidden');
            document.getElementById('criar-item-sucesso').classList.add('hidden');
            setTimeout(() => document.getElementById('novo_item_descricao').focus(), 100);
        };

        window.fecharModalCriarItem = function() {
            document.getElementById('modalCriarItem').classList.add('hidden');
        };

        document.getElementById('modal-criar-item-overlay').addEventListener('click', fecharModalCriarItem);

        document.getElementById('novo_item_descricao').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmarCriarItem();
            }
        });

        window.confirmarCriarItem = function() {
            const descricao = document.getElementById('novo_item_descricao').value.trim();
            const erroDiv = document.getElementById('criar-item-erro');
            const sucessoDiv = document.getElementById('criar-item-sucesso');
            const btn = document.getElementById('btnConfirmarCriarItem');

            erroDiv.classList.add('hidden');
            sucessoDiv.classList.add('hidden');

            if (!descricao) {
                erroDiv.textContent = 'A descrição do item é obrigatória.';
                erroDiv.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Criando...';

            fetch('{{ route('admin.etps.criar-item-rapido') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        descricao_item: descricao
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        adicionarItemAosListas(data.item);
                        sucessoDiv.textContent = `Item "${data.item.descricao_item}" criado com sucesso!`;
                        sucessoDiv.classList.remove('hidden');
                        document.getElementById('novo_item_descricao').value = '';
                        setTimeout(fecharModalCriarItem, 1500);
                    } else {
                        erroDiv.textContent = data.message || 'Erro ao criar item.';
                        erroDiv.classList.remove('hidden');
                    }
                })
                .catch(err => {
                    erroDiv.textContent = 'Erro ao criar item: ' + err.message;
                    erroDiv.classList.remove('hidden');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-plus mr-1"></i> Criar Item';
                });
        };

        function adicionarItemAosListas(item) {
            const novoCheckboxHtml = (loteIndex) => {
                const dataLoteIndex = loteIndex !== null ? `data-lote-index="${loteIndex}"` : '';
                const onchange = loteIndex !== null ?
                    `onchange="toggleItemSelecionado(this, ${loteIndex})"` :
                    `onchange="toggleItemSelecionado(this, null)"`;
                return `
            <label class="flex items-center space-x-3 item-option" data-descricao="${item.descricao_item.toLowerCase()}">
                <input type="checkbox" value="${item.id}"
                    data-descricao="${item.descricao_item}"
                    ${dataLoteIndex}
                    class="item-checkbox w-4 h-4 text-[#009496]"
                    ${onchange}>
                <span class="text-sm text-gray-700">${item.descricao_item}</span>
            </label>`;
            };

            // Lista global (sem lote)
            const listaGlobal = document.getElementById('lista_itens_global');
            if (listaGlobal) listaGlobal.insertAdjacentHTML('beforeend', novoCheckboxHtml(null));

            // Listas de cada lote
            document.querySelectorAll('.lote-card').forEach(loteCard => {
                const match = loteCard.id.match(/lote-(\d+)/);
                if (!match) return;
                const idx = parseInt(match[1]);
                const lista = document.getElementById(`lista_itens_lote_${idx}`);
                if (lista) lista.insertAdjacentHTML('beforeend', novoCheckboxHtml(idx));
            });
        }

        /* ══════════════════════════════════════════════════════════
           IMPORTAÇÃO VIA EXCEL
        ══════════════════════════════════════════════════════════ */
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

        let itensImportados = [];
        let tipoContratacaoAtual = null;

        btnImportar.addEventListener('click', function() {
            tipoContratacaoAtual = document.querySelector('input[name="tipo_contratacao"]:checked')?.value;
            if (!tipoContratacaoAtual) {
                alert('Selecione o tipo de contratação primeiro.');
                return;
            }
            resetarModal();
            modalImportar.classList.remove('hidden');
            setTimeout(() => modalImportar.classList.add('show'), 10);
        });

        function fecharModal() {
            modalImportar.classList.remove('show');
            setTimeout(() => {
                modalImportar.classList.add('hidden');
                resetarModal();
            }, 300);
        }
        btnCancelar.addEventListener('click', fecharModal);
        overlay.addEventListener('click', fecharModal);

        function resetarModal() {
            arquivoInput.value = '';
            nomeArquivoSpan.classList.add('hidden');
            areaProgresso.classList.add('hidden');
            mensagemErro.classList.add('hidden');
            previaItens.classList.add('hidden');
            btnConfirmar.disabled = true;
            itensImportados = [];
        }

        btnBaixarModelo.addEventListener('click', function() {

            // Dados da planilha
            const dados = [
                ["Descricao", "Unidade", "Quantidade"],
                ["Caneta Azul", "UN", 50]
            ];

            // Cria worksheet
            const ws = XLSX.utils.aoa_to_sheet(dados);

            // Cria workbook
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Modelo");

            // Faz download como Excel
            XLSX.writeFile(wb, "modelo_importacao_itens.xlsx");
        });


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
            fetch('{{ route('admin.etps.importar-itens') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(r => {
                    barraProgresso.style.width = '70%';
                    percentualProgresso.textContent = '70%';
                    return r.json();
                })
                .then(data => {
                    barraProgresso.style.width = '100%';
                    percentualProgresso.textContent = '100%';
                    setTimeout(() => {
                        if (data.success) {
                            itensImportados = data.itens;
                            mostrarPrevia(itensImportados);
                            btnConfirmar.disabled = false;
                        } else mostrarErro(data.message);
                        areaProgresso.classList.add('hidden');
                    }, 500);
                })
                .catch(err => {
                    mostrarErro('Erro: ' + err.message);
                    areaProgresso.classList.add('hidden');
                });
        }

        function mostrarErro(msg) {
            mensagemErro.textContent = msg;
            mensagemErro.classList.remove('hidden');
            previaItens.classList.add('hidden');
        }

        function mostrarPrevia(itens) {
            listaIitensImportados.innerHTML = '';
            itens.forEach(item => {
                const li = document.createElement('li');
                li.className = 'flex justify-between items-center text-sm';
                li.innerHTML =
                    `<span class="font-medium">${item.descricao}</span><span class="text-gray-600">${item.quantidade} ${item.unidade}</span>`;
                listaIitensImportados.appendChild(li);
            });
            previaItens.classList.remove('hidden');
        }

        btnConfirmar.addEventListener('click', function() {
            if (!itensImportados.length) return;
            if (tipoContratacaoAtual === 'lote') importarItensComLote(itensImportados);
            else importarItensSemLote(itensImportados);
            fecharModal();
        });

        function importarItensSemLote(itens) {
            const container = document.getElementById('itens-selecionados-sem-lote');
            itens.forEach(item => {
                if (document.getElementById(`item-itens-selecionados-sem-lote-${item.item_id}`)) return;
                const itemDiv = document.createElement('div');
                itemDiv.className = 'flex items-center justify-between bg-white border rounded-lg p-3 shadow-sm';
                itemDiv.id = `item-itens-selecionados-sem-lote-${item.item_id}`;
                itemDiv.innerHTML =
                    `
            <div class="flex items-center gap-3 flex-1">
                <span class="item-numero inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#009496]/10 text-[#009496] text-xs font-bold flex-shrink-0 border border-[#009496]/20">0</span>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800 mb-2">${item.descricao}</p>
                    <div class="flex gap-3">
                        <input type="hidden" name="itens[${item.item_id}][item_id]" value="${item.item_id}">
                        <input type="text"
                            name="itens[${item.item_id}][unidade]"
                            value="${item.unidade}"
                            placeholder="Ex: Unidade, Pacote, Caixa..."
                            class="px-2 py-1 border border-gray-300 rounded text-sm w-24"
                            required>
                        <input type="number" name="itens[${item.item_id}][quantidade]" value="${item.quantidade}" placeholder="Qtd" min="1" required class="px-2 py-1 border border-gray-300 rounded text-sm w-20">
                    </div>
                </div>
            </div>
            <button type="button" class="ml-4 text-red-500 hover:text-red-700 font-bold" onclick="removerItemSelecionado(${item.item_id}, null)">✕</button>`;
                container.appendChild(itemDiv);
                const cb = document.querySelector(`.item-checkbox[value="${item.item_id}"]:not([data-lote-index])`);
                if (cb) cb.checked = true;
            });
            renumerarItens('itens-selecionados-sem-lote');
        }

        function importarItensComLote(itens) {
            const lotes = document.querySelectorAll('.lote-card');
            if (!lotes.length) {
                alert('Crie um lote antes de importar.');
                return;
            }
            const loteIndex = prompt('Digite o número do lote (1 a ' + lotes.length + '):');
            if (!loteIndex) return;
            const index = parseInt(loteIndex) - 1;
            if (isNaN(index) || index < 0 || index >= lotes.length) {
                alert('Número inválido.');
                return;
            }
            const container = document.getElementById(`itens-selecionados-lote-${index}`);
            if (!container) return;
            itens.forEach(item => {
                if (document.getElementById(`item-itens-selecionados-lote-${index}-${item.item_id}`)) return;
                const itemDiv = document.createElement('div');
                itemDiv.className = 'flex items-center justify-between bg-white border rounded-lg p-3 shadow-sm';
                itemDiv.id = `item-itens-selecionados-lote-${index}-${item.item_id}`;
                itemDiv.innerHTML =
                    `
            <div class="flex items-center gap-3 flex-1">
                <span class="item-numero inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#009496]/10 text-[#009496] text-xs font-bold flex-shrink-0 border border-[#009496]/20">0</span>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800 mb-2">${item.descricao}</p>
                    <div class="flex gap-3">
                        <input type="hidden" name="lotes[${index}][itens][${item.item_id}][item_id]" value="${item.item_id}">
                        <input type="text"
                            name="lotes[${index}][itens][${item.item_id}][unidade]"
                            value="${item.unidade}"
                            placeholder="Ex: Unidade, Pacote, Caixa..."
                            class="px-2 py-1 border border-gray-300 rounded text-sm w-24"
                            required>
                        <input type="number" name="lotes[${index}][itens][${item.item_id}][quantidade]" value="${item.quantidade}" placeholder="Qtd" min="1" required class="px-2 py-1 border border-gray-300 rounded text-sm w-20">
                    </div>
                </div>
            </div>
            <button type="button" class="ml-4 text-red-500 hover:text-red-700 font-bold" onclick="removerItemSelecionado(${item.item_id}, ${index})">✕</button>`;
                container.appendChild(itemDiv);
                const cb = document.querySelector(
                    `.item-checkbox[value="${item.item_id}"][data-lote-index="${index}"]`);
                if (cb) cb.checked = true;
            });
            renumerarItens(`itens-selecionados-lote-${index}`);
        }
    </script>
@endsection
