@extends('layouts.app')
@section('page-title', 'Solicitar ETP')
@section('page-subtitle', 'Siga os passos para criar um novo Estudo Técnico Preliminar')

@section('content')
    <div class="py-8">
        <div class="mb-4">
            <a href="{{ route('admin.etps.index') }}"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Voltar
            </a>
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
            <form action="{{ route('admin.etps.store') }}" method="POST" enctype="multipart/form-data" id="etpForm">
                @csrf
                {{-- Campo oculto que define se é rascunho ou conclusão --}}
                <input type="hidden" id="action_type" name="action_type" value="concluir">

                {{-- Campo para indicar se deve redirecionar ou não --}}
                <input type="hidden" id="should_redirect" name="should_redirect" value="1">

                <!-- PROGRESS BAR STEPS -->
                <div class="w-full mb-8 relative">
                    <div class="absolute w-full h-1 bg-gray-200 rounded-full top-5"></div>
                    <div id="progress-bar"
                        class="absolute w-1/3 h-1 bg-[#009496] rounded-full top-5 transition-all duration-300"></div>
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
                    @if($isSuperAdmin)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Prefeitura *</label>
                        <select name="prefeitura_id" id="prefeitura_id"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent"
                            required>
                            <option value="">Selecione a prefeitura...</option>
                            @foreach ($prefeituras as $pref)
                                <option value="{{ $pref->id }}" {{ old('prefeitura_id') == $pref->id ? 'selected' : '' }}>
                                    {{ $pref->nome }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Selecione a prefeitura para carregar as secretarias correspondentes.</p>
                    </div>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Secretaria da Prefeitura *</label>
                            <select name="secretaria_id" id="secretaria_id"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent"
                                required>
                                <option value="">Selecione...</option>
                                @if(!$isSuperAdmin)
                                    @foreach ($secretarias as $sec)
                                        <option value="{{ $sec->id }}" data-servidor="{{ $sec->servidor_responsavel }}">
                                            {{ $sec->nome }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Servidor Responsável</label>
                            <input type="text" name="servidor_responsavel" id="servidor_responsavel"
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
                            <option value="pregao" {{ old('modalidade') == 'pregao' ? 'selected' : '' }}>Pregão
                            </option>
                            <option value="concorrencia" {{ old('modalidade') == 'concorrencia' ? 'selected' : '' }}>
                                Concorrência</option>
                            <option value="dispensa" {{ old('modalidade') == 'dispensa' ? 'selected' : '' }}>Dispensa
                            </option>
                            <option value="inexigibilidade" {{ old('modalidade') == 'inexigibilidade' ? 'selected' : '' }}>
                                Inexigibilidade</option>
                        </select>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="button"
                            class="btn-next px-6 py-2.5 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all"
                            onclick="nextStep(2)">Próximo Passo <i class="fas fa-arrow-right ml-2"></i></button>
                    </div>
                </div>

                <!-- PASSO 2 -->
                <div id="step-2" class="step-content hidden">
                    <h4 class="text-lg font-semibold text-gray-800 mb-6 border-b pb-2">Passo 2: Objeto da Licitação</h4>

                    <!-- Campo Objeto existente -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Especificar o objeto da licitação *</label>
                        <textarea name="objeto_licitacao" id="objeto_licitacao" rows="5"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent resize-y"
                            required>{{ old('objeto_licitacao') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Descreva detalhadamente o que será adquirido ou contratado.</p>
                    </div>

                    <!-- NOVO CAMPO: Justificativa da Necessidade -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Justificativa da Necessidade *</label>
                        <textarea name="justificativa_necessidade" id="justificativa_necessidade" rows="4"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent resize-y"
                            required>{{ old('justificativa_necessidade') }}</textarea>
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
                    <h4 class="text-lg font-semibold text-gray-800 mb-6 border-b pb-2">
                        Passo 3: Tipo de Contratação e Anexos
                    </h4>

                    <div id="campos-itens-contratacao">

                        {{-- TIPO DE CONTRATAÇÃO --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de contratação *</label>

                            <div id="tipo-opcoes-pregao" class="flex items-center space-x-6 hidden">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="radio" name="tipo_contratacao" value="item"
                                        class="form-radio text-[#009496] w-5 h-5"
                                        {{ old('tipo_contratacao') == 'item' ? 'checked' : '' }}
                                        onchange="toggleContratacaoTipo()">
                                    <span class="ml-2">Por Item</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer border-l pl-6 border-gray-200">
                                    <input type="radio" name="tipo_contratacao" value="lote"
                                        class="form-radio text-[#009496] w-5 h-5"
                                        {{ old('tipo_contratacao') == 'lote' ? 'checked' : '' }}
                                        onchange="toggleContratacaoTipo()">
                                    <span class="ml-2">Por Lote</span>
                                </label>
                            </div>

                            <div id="tipo-opcoes-dispensa" class="flex items-center space-x-6 hidden">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="radio" name="tipo_contratacao" value="servicos"
                                        class="form-radio text-[#009496] w-5 h-5"
                                        {{ old('tipo_contratacao') == 'servicos' ? 'checked' : '' }}
                                        onchange="toggleContratacaoTipo()">
                                    <span class="ml-2">Serviços</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer border-l pl-6 border-gray-200">
                                    <input type="radio" name="tipo_contratacao" value="compras"
                                        class="form-radio text-[#009496] w-5 h-5"
                                        {{ old('tipo_contratacao') == 'compras' ? 'checked' : '' }}
                                        onchange="toggleContratacaoTipo()">
                                    <span class="ml-2">Compras</span>
                                </label>
                                <!-- NOVA OPÇÃO: OBRAS -->
                                <label class="inline-flex items-center cursor-pointer border-l pl-6 border-gray-200">
                                    <input type="radio" name="tipo_contratacao" value="obras"
                                        class="form-radio text-[#009496] w-5 h-5"
                                        {{ old('tipo_contratacao') == 'obras' ? 'checked' : '' }}
                                        onchange="toggleContratacaoTipo()">
                                    <span class="ml-2">Obras</span>
                                </label>
                            </div>

                            {{-- Para Serviços/Compras, permite organizar por item ou por lote --}}
                            <div id="modo-organizacao-dispensa" class="flex items-center space-x-6 mt-3 hidden">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="radio" name="organizacao_itens" value="item"
                                        class="form-radio text-[#009496] w-5 h-5"
                                        {{ old('organizacao_itens') == 'item' ? 'checked' : '' }}
                                        onchange="toggleOrganizacaoItens()">
                                    <span class="ml-2">Por Item</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer border-l pl-6 border-gray-200">
                                    <input type="radio" name="organizacao_itens" value="lote"
                                        class="form-radio text-[#009496] w-5 h-5"
                                        {{ old('organizacao_itens') == 'lote' ? 'checked' : '' }}
                                        onchange="toggleOrganizacaoItens()">
                                    <span class="ml-2">Por Lote</span>
                                </label>
                            </div>
                        </div>



                        {{-- BOTÃO: IMPORTAR VIA EXCEL --}}
                        <div class="mb-6 flex justify-end">
                            <button type="button" id="btnImportarItens"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-all shadow-sm gap-2">
                                <i class="fas fa-file-excel"></i>
                                Importar Itens via Excel
                            </button>
                        </div>

                        @php
                            $isLoteOld = old('tipo_contratacao') === 'lote'
                                || (in_array(old('tipo_contratacao'), ['servicos', 'compras']) && old('organizacao_itens') === 'lote');
                        @endphp

                        {{-- ÁREA DE ITENS (sem lote / compras) --}}
                        <div id="area-itens-sem-lote" class="{{ $isLoteOld ? 'hidden' : 'block' }}">
                            @include('Admin.Etps.partials.itens-selector', ['loteIndex' => null])
                        </div>

                        {{-- ÁREA DE LOTES --}}
                        <div id="area-lotes" class="{{ $isLoteOld ? 'block' : 'hidden' }}">
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
                                @if (old('lotes'))
                                    @foreach (old('lotes') as $index => $lote)
                                        @include('Admin.Etps.partials.lote-card', [
                                            'loteIndex' => $index,
                                            'loteData' => $lote,
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
                            value="{{ old('prazo_entrega') }}" placeholder="Ex: 30 dias após emissão da nota"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496]"
                            required>
                    </div>

                    {{-- Dotação Orçamentária --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dotação Orçamentária *</label>
                        <textarea name="dotacao_orcamentaria" id="dotacao_orcamentaria" placeholder="Digite a dotação orçamentária"
                            rows="3"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent resize-y"
                            required>{{ old('dotacao_orcamentaria') }}</textarea>
                    </div>

                    {{-- Cotação ou Projeto Básico --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2" id="label_pdf_anexo">
                            Anexar Cotação do Fornecedor Local
                        </label>
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


    {{-- Modal: Validação / Confirmação antes de concluir ETP --}}
    <div id="modal-concluir-etp" class="fixed inset-0 z-[80] flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
            <div class="p-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center rounded-t-2xl">
                <h3 id="modal-concluir-titulo" class="text-lg font-bold text-gray-800"></h3>
                <button type="button" onclick="fecharModalConcluir()" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div id="modal-concluir-body" class="p-6"></div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                <button type="button" onclick="fecharModalConcluir()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                    Fechar
                </button>
                <button type="button" id="btn-confirmar-concluir" onclick="confirmarConcluirEtp()"
                    class="hidden px-5 py-2 text-sm font-bold text-white bg-green-600 rounded-lg hover:bg-green-700 transition shadow-sm">
                    <i class="fas fa-check-circle mr-1"></i> Confirmar e Enviar
                </button>
            </div>
        </div>
    </div>

    {{-- Modal: Buscar no PNCP --}}
    @include('Admin.Etps.partials.modal-pncp-search')

    {{-- Modal: Edição Rápida de Item --}}
    @include('Admin.Etps.partials.modal-item-quick-edit')

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
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Unidade *</label>
                    <select id="novo_item_unidade" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent text-sm">
                        @foreach($unidadesMedida as $value => $label)
                            <option value="{{ $value }}" {{ $value === 'UND' ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantidade *</label>
                    <input type="number" id="novo_item_quantidade" value="1" min="1"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent text-sm">
                </div>
            </div>

            <p class="mt-2 text-xs text-gray-500">O item será criado e adicionado diretamente à sua lista.</p>

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

    @php
        $unidadesPorPrefeituraJson = $prefeituras->mapWithKeys(function ($pref) {
            return [
                $pref->id => $pref->unidades->map(function ($unidade) {
                    return [
                        'id' => $unidade->id,
                        'nome' => $unidade->nome,
                        'servidor' => $unidade->servidor_responsavel,
                    ];
                }),
            ];
        });
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script>
        const UNIDADES_MEDIDA = @json($unidadesMedida);
        /* ── CSRF helpers (lê sempre do <meta>, evita token obsoleto) ── */
        window.getCsrfToken = function () {
            const meta = document.querySelector('meta[name="csrf-token"]');
            const fallbackInput = document.querySelector('input[name="_token"]');
            return (meta && meta.getAttribute('content'))
                || (fallbackInput && fallbackInput.value)
                || '';
        };

        window.handleCsrfExpirado = function (response) {
            if (response && response.status === 419) {
                if (typeof mostrarNotificacao === 'function') {
                    mostrarNotificacao('error', 'Sua sessão expirou (CSRF). A página será recarregada para você continuar.');
                } else {
                    alert('Sua sessão expirou. A página será recarregada.');
                }
                setTimeout(() => window.location.reload(), 1800);
                throw new Error('CSRF token expirado (419).');
            }
            return response;
        };

        document.addEventListener("DOMContentLoaded", function() {

            let loteCounter = {{ old('lotes') ? count(old('lotes')) : 0 }};

            const secretariaSelect = document.getElementById('secretaria_id');
            const prefeituraSelect = document.getElementById('prefeitura_id');
            const servidorInput = document.getElementById('servidor_responsavel');
            const modalidadeSelect = document.getElementById('modalidade');
            const cotacaoInput = document.getElementById('cotacao_path');
            const camposItens = document.getElementById('campos-itens-contratacao');
            const labelPdf = document.getElementById('label_pdf_anexo');

            /* ── PREFEITURA → SECRETARIA (apenas para admins, sem prefeitura própria) ── */
            if (prefeituraSelect) {
                const unidadesPorPrefeitura = @json($unidadesPorPrefeituraJson ?? []);

                function carregarSecretarias(prefeituraId, secretariaIdSelecionada) {
                    secretariaSelect.innerHTML = '<option value="">Selecione...</option>';

                    (unidadesPorPrefeitura[prefeituraId] || []).forEach(unidade => {
                        const option = document.createElement('option');
                        option.value = unidade.id;
                        option.textContent = unidade.nome;
                        option.setAttribute('data-servidor', unidade.servidor ?? '');
                        if (secretariaIdSelecionada && String(unidade.id) === String(secretariaIdSelecionada)) {
                            option.selected = true;
                        }
                        secretariaSelect.appendChild(option);
                    });
                }

                prefeituraSelect.addEventListener('change', function() {
                    carregarSecretarias(this.value);
                });

                if (prefeituraSelect.value) {
                    carregarSecretarias(prefeituraSelect.value, "{{ old('secretaria_id') }}");
                }
            }

            /* ── SECRETARIA → SERVIDOR AUTO ── */
            function preencherServidor() {
                const selected = secretariaSelect.options[secretariaSelect.selectedIndex];
                const servidor = selected?.getAttribute('data-servidor');
                if (!servidorInput.value) servidorInput.value = servidor ?? '';
            }
            preencherServidor();
            secretariaSelect.addEventListener('change', preencherServidor);

            function toggleInputs(container, enable) {
                if (!container) return;
                container.querySelectorAll('input, select, textarea').forEach(el => {
                    if (el.name !== '_token' && el.name !== '_method') {
                        el.disabled = !enable;
                    }
                });
            }

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
                const modoDispensa = document.getElementById('modo-organizacao-dispensa');

                radiosTipo.forEach(el => {
                    el.checked = false;
                    el.required = false;
                });
                modoDispensa.querySelectorAll('input[name="organizacao_itens"]').forEach(el => {
                    el.checked = false;
                    el.required = false;
                });
                modoDispensa.classList.add('hidden');
                opcoesPregao.classList.add('hidden');
                opcoesDisp.classList.add('hidden');
                camposItens.classList.remove('hidden');
                toggleInputs(camposItens, true);

                labelPdf.innerText = 'Anexar Cotação do Fornecedor Local';
                cotacaoInput.removeAttribute('required');

                if (modalidade === 'concorrencia' || modalidade === 'inexigibilidade') {
                    camposItens.classList.add('hidden');
                    toggleInputs(camposItens, false);
                    areaItensSemLote.classList.add('hidden');
                    toggleInputs(areaItensSemLote, false);
                    areaLotes.classList.add('hidden');
                    toggleInputs(areaLotes, false);
                    labelPdf.innerText = 'Anexar Projeto Básico *';
                    cotacaoInput.setAttribute('required', 'required');

                } else if (modalidade === 'dispensa') {
                    opcoesDisp.classList.remove('hidden');
                    const valorSalvo = '{{ old('tipo_contratacao') }}';
                    if (valorSalvo === 'servicos' || valorSalvo === 'compras' || valorSalvo === 'obras') {
                        const radioEl = document.querySelector(`input[name="tipo_contratacao"][value="${valorSalvo}"]`);
                        if (radioEl) radioEl.checked = true;
                    }
                    opcoesDisp.querySelectorAll('input[type="radio"]').forEach(el => {
                        el.required = true;
                    });
                    const modoSalvo = '{{ old('organizacao_itens') }}';
                    if (modoSalvo === 'item' || modoSalvo === 'lote') {
                        const modoEl = document.querySelector(`input[name="organizacao_itens"][value="${modoSalvo}"]`);
                        if (modoEl) modoEl.checked = true;
                    }
                    areaItensSemLote.classList.remove('hidden');
                    toggleInputs(areaItensSemLote, true);
                    areaLotes.classList.add('hidden');
                    toggleInputs(areaLotes, false);
                    toggleContratacaoTipo();

                } else if (modalidade === 'pregao') {
                    opcoesPregao.classList.remove('hidden');
                    const valorSalvo = '{{ old('tipo_contratacao') }}';
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
                    toggleInputs(camposItens, false);
                    areaItensSemLote.classList.add('hidden');
                    toggleInputs(areaItensSemLote, false);
                    areaLotes.classList.add('hidden');
                    toggleInputs(areaLotes, false);
                }
            };

            /* ── ÁREA DE ITENS x LOTES (compartilhado entre Pregão e Serviços/Compras) ── */
            function mostrarAreaItensOuLotes(isLote) {
                const areaSemLote = document.getElementById('area-itens-sem-lote');
                const areaLotes = document.getElementById('area-lotes');
                const labelPdf = document.getElementById('label_pdf_anexo');

                labelPdf.innerText = 'Anexar Cotação do Fornecedor Local';
                document.getElementById('cotacao_path').removeAttribute('required');

                if (isLote) {
                    areaSemLote.classList.add('hidden');
                    toggleInputs(areaSemLote, false);
                    areaLotes.classList.remove('hidden');
                    toggleInputs(areaLotes, true);
                    if (document.querySelectorAll('.lote-card').length === 0) adicionarLote();
                } else {
                    areaSemLote.classList.remove('hidden');
                    toggleInputs(areaSemLote, true);
                    areaLotes.classList.add('hidden');
                    toggleInputs(areaLotes, false);
                }
            }

            /* ── TIPO CONTRATAÇÃO ── */
            window.toggleContratacaoTipo = function() {
                const selected = document.querySelector('input[name="tipo_contratacao"]:checked');
                if (!selected) return;
                const val = selected.value;
                const labelPdf = document.getElementById('label_pdf_anexo');
                const modoDispensa = document.getElementById('modo-organizacao-dispensa');
                const radiosModo = modoDispensa.querySelectorAll('input[name="organizacao_itens"]');

                // Se for OBRAS, esconde a área de itens (e o sub-modo item/lote) e muda o label do PDF
                if (val === 'obras') {
                    modoDispensa.classList.add('hidden');
                    radiosModo.forEach(el => { el.checked = false; el.required = false; });
                    const areaSemLote = document.getElementById('area-itens-sem-lote');
                    const areaLotes = document.getElementById('area-lotes');
                    areaSemLote.classList.add('hidden');
                    toggleInputs(areaSemLote, false);
                    areaLotes.classList.add('hidden');
                    toggleInputs(areaLotes, false);
                    labelPdf.innerText = 'Anexar Projeto Básico *';
                    document.getElementById('cotacao_path').setAttribute('required', 'required');
                    return;
                }

                // Serviços/Compras: exige escolher Por Item ou Por Lote antes de mostrar a área
                if (val === 'servicos' || val === 'compras') {
                    modoDispensa.classList.remove('hidden');
                    radiosModo.forEach(el => el.required = true);
                    const modoSelecionado = document.querySelector('input[name="organizacao_itens"]:checked');
                    mostrarAreaItensOuLotes(modoSelecionado?.value === 'lote');
                    return;
                }

                // Pregão: "item"/"lote" decidem a área diretamente, sem sub-modo
                modoDispensa.classList.add('hidden');
                radiosModo.forEach(el => { el.checked = false; el.required = false; });
                mostrarAreaItensOuLotes(val === 'lote');
            };

            /* ── ORGANIZAÇÃO (POR ITEM/LOTE) PARA SERVIÇOS/COMPRAS ── */
            window.toggleOrganizacaoItens = function() {
                const selected = document.querySelector('input[name="organizacao_itens"]:checked');
                if (!selected) return;
                mostrarAreaItensOuLotes(selected.value === 'lote');
            };

            // Normaliza texto removendo acentos e convertendo para minúsculas
            function normalizarTexto(texto) {
                return texto
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');
            }

            /* ── FUNÇÃO CORRIGIDA DE BUSCA ── */
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

            /* ── FUNÇÃO PARA INICIALIZAR TODAS AS BUSCAS ── */
            function inicializarTodasBuscas() {
                // Inicializa busca global
                inicializarBuscaLote();

                // Inicializa busca para cada lote existente
                document.querySelectorAll('.lote-card').forEach((card, index) => {
                    inicializarBuscaLote(index);
                });
            }

            /* ── LOTES ── */
            window.adicionarLote = function() {
                const idx = loteCounter;
                const container = document.getElementById('lotes-container');
                const loteDiv = document.createElement('div');
                loteDiv.className = 'lote-card border border-gray-200 rounded-xl bg-white shadow-sm overflow-hidden';
                loteDiv.id = `lote-${idx}`;

                loteDiv.innerHTML = `
                <div class="flex items-center justify-between px-5 py-3.5 bg-gray-50 border-b border-gray-200">
                    <button type="button" class="flex items-center gap-2 flex-1 text-left min-w-0" onclick="toggleLote(${idx})">
                        <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200" id="chevron-lote-${idx}"></i>
                        <span class="text-sm font-semibold text-gray-700 truncate" id="label-lote-${idx}">Lote ${idx + 1}</span>
                    </button>
                    <div class="flex items-center gap-1 flex-shrink-0 ml-3">
                        <button type="button" onclick="duplicarLote(${idx})" title="Duplicar este lote"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:text-[#009496] hover:bg-[#009496]/10 transition">
                            <i class="fas fa-copy text-sm"></i>
                        </button>
                        <button type="button" onclick="removerLote(this)" title="Remover lote"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:text-red-500 hover:bg-red-50 transition">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>
                <div class="px-6 py-5" id="body-lote-${idx}">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Lote *</label>
                        <input type="text" name="lotes[${idx}][nome]"
                            placeholder="Ex: Lote ${idx + 1} - Materiais de Escritório"
                            oninput="atualizarLabelLote(${idx}, this.value)"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496]" required>
                    </div>
                    ${gerarItensSelector(idx)}
                </div>`;

                container.appendChild(loteDiv);
                inicializarBuscaLote(idx);
                initSortable(`itens-selecionados-lote-${idx}`);
                loteCounter++;
                return idx;
            };

            /* ── GERADOR DE ITENS SELECTOR (CORRIGIDO) ── */
            function gerarItensSelector(loteIndex) {
                const buscaId = `buscar_item_lote_${loteIndex}`;
                const listaId = `lista_itens_lote_${loteIndex}`;
                const containerId = `itens-selecionados-lote-${loteIndex}`;

                let itensOptions = '';
                @foreach ($itens as $item)
                    // CORREÇÃO: Garantir que a descrição esteja em minúsculas no atributo data
                    itensOptions += `
                    <label class="flex items-center space-x-3 item-option" data-descricao="{{ strtolower($item->descricao_item) }}">
                        <input type="checkbox" value="{{ $item->id }}"
                            data-descricao="{{ $item->descricao_item }}"
                            data-lote-index="${loteIndex}"
                            class="item-checkbox w-4 h-4 text-[#009496]"
                            onchange="toggleItemSelecionado(this, ${loteIndex})">
                        <span class="text-sm text-gray-700">{{ $item->descricao_item }}</span>
                    </label>`;
                @endforeach

                return `
                <div class="mb-4 itens-selector" data-lote-index="${loteIndex}">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Selecionar Itens *</label>
                    <div class="flex flex-col sm:flex-row gap-2.5 mb-3">
                        <div class="relative flex-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-search text-xs"></i>
                            </span>
                            <input type="text" id="${buscaId}" placeholder="Buscar item já cadastrado no sistema..."
                                class="buscar-item w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition"
                                data-lista="${listaId}">
                        </div>
                        <button type="button" onclick="abrirModalCriarItem(${loteIndex})" 
                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition shadow-sm whitespace-nowrap gap-2">
                            <i class="fas fa-plus"></i> Novo Item Manual
                        </button>
                        <button type="button" onclick="openModalPncp(${loteIndex})" 
                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-white bg-[#009496] hover:bg-[#007a7a] rounded-lg transition shadow-sm whitespace-nowrap gap-2">
                            <i class="fas fa-globe"></i> Novo Item (PNCP)
                        </button>
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
                if (document.querySelectorAll('.lote-card').length > 1) {
                    loteCard.remove();
                } else {
                    alert('É necessário ter pelo menos um lote.');
                }
            };

            /* ── SELEÇÃO / REMOÇÃO DE ITENS ── */
            window.toggleItemSelecionado = function(checkbox, loteIndex, overrideUnidade = null, overrideQuantidade = null) {
                const id = checkbox.value;
                const descricao = checkbox.dataset.descricao;
                const containerId = loteIndex !== null ? `itens-selecionados-lote-${loteIndex}` :
                    'itens-selecionados-sem-lote';
                const container = document.getElementById(containerId);

                if (checkbox.checked) {
                    const namePrefix = loteIndex !== null ? `lotes[${loteIndex}][itens]` : 'itens';
                    const selectedUnidade = overrideUnidade || 'UND';
                    const selectedQuantidade = overrideQuantidade || '';

                    container.insertAdjacentHTML('beforeend', `
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:border-[#009496]/30 transition overflow-hidden" id="item-${containerId}-${id}">
                        <div class="flex flex-col md:flex-row md:items-center justify-between p-4 gap-4 cursor-pointer" onclick="toggleAccordionItem('${containerId}-${id}')" title="Clique para expandir/recolher">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <i class="fas fa-grip-vertical text-gray-300 hover:text-gray-500 cursor-grab px-1 drag-handle" title="Arrastar para reordenar" onclick="event.stopPropagation()"></i>
                                <span class="item-numero inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#009496]/10 text-[#009496] text-xs font-bold flex-shrink-0 border border-[#009496]/20">0</span>
                                <div class="min-w-0 flex-1 flex items-center gap-2">
                                    <p class="desc-item-${id} text-sm font-semibold text-gray-800 leading-relaxed truncate" title="${descricao}">${descricao}</p>
                                </div>
                                <button type="button" class="text-gray-400 hover:text-[#009496] focus:outline-none flex-shrink-0 transition-transform duration-200" id="chevron-item-${containerId}-${id}">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            
                            <div class="flex items-center gap-3 flex-shrink-0" onclick="event.stopPropagation()">
                                <div class="flex flex-col gap-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Unidade</span>
                                    ${buildUnidadeSelectHtml(`${namePrefix}[${id}][unidade]`, selectedUnidade)}
                                </div>
                                
                                <div class="flex flex-col gap-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Quantidade</span>
                                    <input type="number" name="${namePrefix}[${id}][quantidade]" value="${selectedQuantidade}" placeholder="Qtd" min="1" required class="rounded-lg border-gray-300 text-xs py-1.5 focus:border-[#009496] focus:ring-[#009496] w-20 bg-gray-50/50 hover:bg-gray-50 transition">
                                </div>

                                <div class="flex items-end self-end pb-0.5 gap-2">
                                    <input type="hidden" name="${namePrefix}[${id}][item_id]" value="${id}">
                                    <button type="button" onclick="openModalItemQuickEdit(${id}, document.querySelector('#body-item-${containerId}-${id} .desc-item-${id}').textContent)" class="btn-edit-item-${id} inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:text-[#009496] hover:bg-[#009496]/10 transition" title="Editar Descrição Completa">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:text-red-600 hover:bg-red-50 focus:outline-none transition" onclick="removerItemSelecionado(${id}, ${loteIndex})" title="Excluir Item">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="body-item-${containerId}-${id}" class="hidden border-t border-gray-100 bg-gray-50/80 p-4">
                            <h6 class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Descrição Completa</h6>
                            <p class="desc-item-${id} text-sm text-gray-700 whitespace-pre-wrap">${descricao}</p>
                        </div>
                    </div>`);
                } else {
                    removerItemSelecionado(id, loteIndex);
                }
                renumerarItens(containerId);
                agendarAutoSave();
            };

            window.removerItemSelecionado = function(id, loteIndex) {
                const containerId = loteIndex !== null && loteIndex !== undefined ?
                    `itens-selecionados-lote-${loteIndex}` :
                    'itens-selecionados-sem-lote';
                const itemDiv = document.getElementById(`item-${containerId}-${id}`);
                if (itemDiv) itemDiv.remove();

                const sel = loteIndex !== null && loteIndex !== undefined ?
                    `.item-checkbox[value="${id}"][data-lote-index="${loteIndex}"]` :
                    `.item-checkbox[value="${id}"]:not([data-lote-index])`;
                const cb = document.querySelector(sel);
                if (cb) cb.checked = false;
                renumerarItens(containerId);
                agendarAutoSave();
            };

            window.renumerarItens = function(containerId) {
                const container = document.getElementById(containerId);
                if (!container) return;
                container.querySelectorAll('.item-numero').forEach((badge, idx) => {
                    badge.textContent = idx + 1;
                });
            };

            window.toggleAccordionItem = function(uniqueId) {
                const body = document.getElementById(`body-item-${uniqueId}`);
                const chevron = document.getElementById(`chevron-item-${uniqueId}`);
                if (!body) return;
                
                body.classList.toggle('hidden');
                if (chevron) {
                    chevron.classList.toggle('rotate-180');
                }
            };

            window.initSortable = function(containerId) {
                if (typeof Sortable === 'undefined') return;
                const el = document.getElementById(containerId);
                if (!el) return;
                if (el._sortable) el._sortable.destroy();
                el._sortable = new Sortable(el, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'opacity-50',
                    onEnd: function() { renumerarItens(containerId); }
                });
            };

            window.toggleLote = function(loteIndex) {
                const body = document.getElementById(`body-lote-${loteIndex}`);
                const chevron = document.getElementById(`chevron-lote-${loteIndex}`);
                if (!body) return;
                body.classList.toggle('hidden');
                if (chevron) chevron.classList.toggle('rotate-180');
            };

            window.toggleSemLote = function() {
                const body = document.getElementById('itens-selecionados-sem-lote');
                const chevron = document.getElementById('chevron-sem-lote');
                if (!body) return;
                body.classList.toggle('hidden');
                if (chevron) chevron.classList.toggle('rotate-180');
            };

            window.atualizarLabelLote = function(loteIndex, value) {
                const label = document.getElementById(`label-lote-${loteIndex}`);
                if (label) label.textContent = value.trim() || `Lote ${loteIndex + 1}`;
            };

            window.duplicarLote = function(sourceIndex) {
                const nomeOriginal = document.querySelector(`input[name="lotes[${sourceIndex}][nome]"]`)?.value || '';
                const sourceContainer = document.getElementById(`itens-selecionados-lote-${sourceIndex}`);

                const itensDuplicar = [];
                if (sourceContainer) {
                    Array.from(sourceContainer.children).forEach(div => {
                        const match = div.id.match(/item-itens-selecionados-lote-\d+-(\d+)$/);
                        if (!match) return;
                        const itemId = match[1];
                        const descEl = div.querySelector(`.desc-item-${itemId}`);
                        const descricao = descEl ? (descEl.getAttribute('title') || descEl.textContent.trim()) : '';
                        const unidadeEl = div.querySelector(`select[name="lotes[${sourceIndex}][itens][${itemId}][unidade]"]`);
                        const qtdEl = div.querySelector(`input[name="lotes[${sourceIndex}][itens][${itemId}][quantidade]"]`);
                        itensDuplicar.push({
                            id: itemId,
                            descricao,
                            unidade: unidadeEl?.value || 'UND',
                            quantidade: qtdEl?.value || '1'
                        });
                    });
                }

                const newIndex = adicionarLote();

                const nomeInput = document.querySelector(`input[name="lotes[${newIndex}][nome]"]`);
                if (nomeInput) {
                    nomeInput.value = nomeOriginal ? `Cópia de ${nomeOriginal}` : `Cópia de Lote ${sourceIndex + 1}`;
                    atualizarLabelLote(newIndex, nomeInput.value);
                }

                if (!itensDuplicar.length) return;

                const newContainer = document.getElementById(`itens-selecionados-lote-${newIndex}`);
                if (!newContainer) return;

                const namePrefix = `lotes[${newIndex}][itens]`;

                itensDuplicar.forEach(({ id, descricao, unidade, quantidade }) => {
                    newContainer.insertAdjacentHTML('beforeend', `
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:border-[#009496]/30 transition overflow-hidden" id="item-itens-selecionados-lote-${newIndex}-${id}">
                        <div class="flex flex-col md:flex-row md:items-center justify-between p-4 gap-4 cursor-pointer" onclick="toggleAccordionItem('itens-selecionados-lote-${newIndex}-${id}')" title="Clique para expandir/recolher">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <i class="fas fa-grip-vertical text-gray-300 hover:text-gray-500 cursor-grab px-1 drag-handle" title="Arrastar para reordenar" onclick="event.stopPropagation()"></i>
                                <span class="item-numero inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#009496]/10 text-[#009496] text-xs font-bold flex-shrink-0 border border-[#009496]/20">0</span>
                                <div class="min-w-0 flex-1 flex items-center gap-2">
                                    <p class="desc-item-${id} text-sm font-semibold text-gray-800 leading-relaxed truncate" title="${descricao}">${descricao}</p>
                                </div>
                                <button type="button" class="text-gray-400 hover:text-[#009496] focus:outline-none flex-shrink-0 transition-transform duration-200" id="chevron-item-itens-selecionados-lote-${newIndex}-${id}">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0" onclick="event.stopPropagation()">
                                <div class="flex flex-col gap-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Unidade</span>
                                    ${buildUnidadeSelectHtml(`${namePrefix}[${id}][unidade]`, unidade)}
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Quantidade</span>
                                    <input type="number" name="${namePrefix}[${id}][quantidade]" value="${quantidade}" placeholder="Qtd" min="1" required class="rounded-lg border-gray-300 text-xs py-1.5 focus:border-[#009496] focus:ring-[#009496] w-20 bg-gray-50/50 hover:bg-gray-50 transition">
                                </div>
                                <div class="flex items-end self-end pb-0.5 gap-2">
                                    <input type="hidden" name="${namePrefix}[${id}][item_id]" value="${id}">
                                    <button type="button" onclick="openModalItemQuickEdit(${id}, document.querySelector('#body-item-itens-selecionados-lote-${newIndex}-${id} .desc-item-${id}').textContent)" class="btn-edit-item-${id} inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:text-[#009496] hover:bg-[#009496]/10 transition" title="Editar Descrição Completa">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:text-red-600 hover:bg-red-50 focus:outline-none transition" onclick="removerItemSelecionado(${id}, ${newIndex})" title="Excluir Item">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="body-item-itens-selecionados-lote-${newIndex}-${id}" class="hidden border-t border-gray-100 bg-gray-50/80 p-4">
                            <h6 class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Descrição Completa</h6>
                            <p class="desc-item-${id} text-sm text-gray-700 whitespace-pre-wrap">${descricao}</p>
                        </div>
                    </div>`);

                    const cb = document.querySelector(`.item-checkbox[value="${id}"][data-lote-index="${newIndex}"]`);
                    if (cb) cb.checked = true;
                });

                renumerarItens(`itens-selecionados-lote-${newIndex}`);
                initSortable(`itens-selecionados-lote-${newIndex}`);
            };

            /* ── STEPS ── */
            window.nextStep = function(step) {
                if (step === 2) {
                    if (prefeituraSelect && !prefeituraSelect.value) {
                        alert('Selecione uma prefeitura primeiro.');
                        return;
                    }
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

                    // SALVAMENTO AUTOMÁTICO ANTES DE IR PARA PASSO 3
                    submeterEtp('salvar', () => {
                        document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
                        document.getElementById('step-' + step).classList.remove('hidden');
                        updateProgress(step);
                    });
                    return; // Interrompe para esperar o save
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

            /* ── VALIDAÇÃO CLIENT-SIDE ── */
            function validarFormularioEtp() {
                const erros = [];
                const modalidade = document.getElementById('modalidade').value;
                const prefeituraEl = document.getElementById('prefeitura_id');

                if (prefeituraEl && !prefeituraEl.value)
                    erros.push('Selecione a prefeitura (Passo 1).');
                if (!document.getElementById('secretaria_id').value)
                    erros.push('Selecione a secretaria (Passo 1).');
                if (!document.getElementById('servidor_responsavel').value.trim())
                    erros.push('Informe o servidor responsável (Passo 1).');
                if (!modalidade)
                    erros.push('Selecione a modalidade da licitação (Passo 1).');
                if (!document.getElementById('objeto_licitacao').value.trim())
                    erros.push('Descreva o objeto da licitação (Passo 2).');
                if (!document.getElementById('justificativa_necessidade').value.trim())
                    erros.push('Informe a justificativa da necessidade (Passo 2).');
                if (!document.getElementById('prazo_entrega').value.trim())
                    erros.push('Informe o prazo de entrega (Passo 3).');
                if (!document.getElementById('dotacao_orcamentaria').value.trim())
                    erros.push('Informe a dotação orçamentária (Passo 3).');

                if (modalidade === 'concorrencia' || modalidade === 'inexigibilidade') {
                    const f = document.getElementById('cotacao_path');
                    if (f && !f.files.length) erros.push('Anexe o Projeto Básico (Passo 3).');
                    return erros;
                }

                const tipoEl = document.querySelector('input[name="tipo_contratacao"]:checked');
                if (!tipoEl) {
                    erros.push('Selecione o tipo de contratação (Passo 3).');
                    return erros;
                }

                const tipo = tipoEl.value;

                if (tipo === 'obras') {
                    const f = document.getElementById('cotacao_path');
                    if (f && !f.files.length) erros.push('Anexe o Projeto Básico (Passo 3).');
                    return erros;
                }

                let isLote = tipo === 'lote';
                if (tipo === 'servicos' || tipo === 'compras') {
                    const modoEl = document.querySelector('input[name="organizacao_itens"]:checked');
                    if (!modoEl) {
                        erros.push('Selecione se a contratação será Por Item ou Por Lote (Passo 3).');
                        return erros;
                    }
                    isLote = modoEl.value === 'lote';
                }

                if (isLote) {
                    const loteCards = document.querySelectorAll('.lote-card');
                    if (!loteCards.length) {
                        erros.push('Adicione pelo menos um lote (Passo 3).');
                    } else {
                        loteCards.forEach((card, i) => {
                            const match = card.id.match(/lote-(\d+)/);
                            if (!match) return;
                            const idx = match[1];
                            const nomeInput = card.querySelector(`input[name="lotes[${idx}][nome]"]`);
                            if (!nomeInput?.value.trim())
                                erros.push(`Lote ${i + 1}: informe o nome do lote.`);
                            const container = document.getElementById(`itens-selecionados-lote-${idx}`);
                            if (!container || !container.children.length) {
                                erros.push(`Lote ${i + 1}: adicione pelo menos um item.`);
                            } else {
                                let semQtd = false;
                                container.querySelectorAll('input[type="number"]').forEach(el => {
                                    if (!el.value || parseFloat(el.value) <= 0) semQtd = true;
                                });
                                if (semQtd) erros.push(`Lote ${i + 1}: preencha a quantidade de todos os itens.`);
                            }
                        });
                    }
                } else {
                    const container = document.getElementById('itens-selecionados-sem-lote');
                    if (!container || !container.children.length) {
                        erros.push('Adicione pelo menos um item (Passo 3).');
                    } else {
                        let semQtd = false;
                        container.querySelectorAll('input[type="number"]').forEach(el => {
                            if (!el.value || parseFloat(el.value) <= 0) semQtd = true;
                        });
                        if (semQtd) erros.push('Preencha a quantidade de todos os itens selecionados.');
                    }
                }

                return erros;
            }

            /* ── MODAIS DE VALIDAÇÃO / CONFIRMAÇÃO ── */
            window.fecharModalConcluir = function() {
                document.getElementById('modal-concluir-etp').classList.add('hidden');
            };

            window.confirmarConcluirEtp = function() {
                fecharModalConcluir();
                document.getElementById('should_redirect').value = '1';
                document.getElementById('etpForm').submit();
            };

            function abrirModalErrosEtp(erros) {
                document.getElementById('modal-concluir-titulo').textContent = 'Verifique os campos obrigatórios';
                document.getElementById('modal-concluir-titulo').className = 'text-lg font-bold text-red-700';
                const items = erros.map(e =>
                    `<li class="flex items-start gap-2 text-sm text-gray-800">
                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5 flex-shrink-0"></i>
                        <span>${e}</span>
                    </li>`
                ).join('');
                document.getElementById('modal-concluir-body').innerHTML = `
                    <p class="text-sm text-gray-600 mb-3">Corrija os itens abaixo antes de concluir o ETP:</p>
                    <ul class="space-y-2">${items}</ul>`;
                document.getElementById('btn-confirmar-concluir').classList.add('hidden');
                document.getElementById('modal-concluir-etp').classList.remove('hidden');
            }

            function abrirModalConfirmacaoEtp() {
                document.getElementById('modal-concluir-titulo').textContent = 'Confirmar conclusão do ETP';
                document.getElementById('modal-concluir-titulo').className = 'text-lg font-bold text-gray-800';
                document.getElementById('modal-concluir-body').innerHTML = `
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-paper-plane text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800 mb-1">Tudo certo! O ETP está pronto para envio.</p>
                            <p class="text-sm text-gray-500">Após confirmar, o ETP será enviado para análise. Deseja continuar?</p>
                        </div>
                    </div>`;
                document.getElementById('btn-confirmar-concluir').classList.remove('hidden');
                document.getElementById('modal-concluir-etp').classList.remove('hidden');
            }

            /* ── SUBMETER ETP (salvar ou concluir) ── */
            window.submeterEtp = function(tipo, callback, silent) {
                document.getElementById('action_type').value = tipo;

                if (tipo === 'salvar') {
                    document.getElementById('should_redirect').value = '0';
                    enviarFormularioAjax(callback, silent);
                    return;
                }

                const erros = validarFormularioEtp();
                if (erros.length > 0) {
                    abrirModalErrosEtp(erros);
                } else {
                    abrirModalConfirmacaoEtp();
                }
            };

            // Função para enviar o formulário via AJAX
            function enviarFormularioAjax(callback, silent) {
                const form = document.getElementById('etpForm');
                const formData = new FormData(form);

                const btnSalvar = silent ? null : document.querySelector('button[onclick="submeterEtp(\'salvar\')"]');
                const btnNext   = silent ? null : document.querySelector('button[onclick="nextStep(3)"]');
                const originalContentSalvar = btnSalvar ? btnSalvar.innerHTML : '';
                const originalContentNext   = btnNext   ? btnNext.innerHTML   : '';

                if (btnSalvar) {
                    btnSalvar.disabled = true;
                    btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Salvando...';
                }
                if (btnNext) {
                    btnNext.disabled = true;
                    btnNext.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Salvando...';
                }

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json'
                        }
                    })
                    .then(handleCsrfExpirado)
                    .then(async response => {
                        if (response.status === 422) {
                            const data = await response.json();
                            let errorList = 'Verifique os campos obrigatórios:\\n';
                            for (let field in data.errors) {
                                errorList += `• ${data.errors[field][0]}\\n`;
                            }
                            throw new Error(errorList);
                        }
                        if (!response.ok) {
                            throw new Error('Erro na requisição. Entre em contato com o suporte.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            if (silent) {
                                mostrarIndicadorAutoSave();
                            } else {
                                mostrarNotificacao('success', data.message || 'Rascunho salvo com sucesso!');
                            }

                            if (data.etp_id && form.action.endsWith('/etps')) {
                                form.action = form.action + '/' + data.etp_id;
                                if (!document.querySelector('input[name="_method"]')) {
                                    const methodInput = document.createElement('input');
                                    methodInput.type = 'hidden';
                                    methodInput.name = '_method';
                                    methodInput.value = 'PUT';
                                    form.appendChild(methodInput);
                                }
                            }

                            if (typeof callback === 'function') callback();
                        } else {
                            if (!silent) mostrarNotificacao('error', data.message || 'Erro ao salvar rascunho.');
                        }
                    })
                    .catch(error => {
                        if (!silent) {
                            console.error('Erro:', error);
                            let msg = error.message || 'Erro ao salvar rascunho. Tente novamente.';
                            msg = msg.replace(/\n/g, '<br>');
                            mostrarNotificacao('error', msg);
                        }
                    })
                    .finally(() => {
                        if (btnSalvar) {
                            btnSalvar.disabled = false;
                            btnSalvar.innerHTML = originalContentSalvar;
                        }
                        if (btnNext) {
                            btnNext.disabled = false;
                            btnNext.innerHTML = originalContentNext;
                        }
                    });
            }

            function mostrarIndicadorAutoSave() {
                let ind = document.getElementById('indicador-auto-save');
                if (!ind) {
                    ind = document.createElement('span');
                    ind.id = 'indicador-auto-save';
                    ind.className = 'fixed bottom-5 left-5 z-[200] flex items-center gap-1.5 px-3 py-1.5 bg-gray-800 text-white text-xs font-medium rounded-full shadow-lg pointer-events-none';
                    ind.style.cssText = 'opacity:0; transform:translateY(6px); transition:opacity .3s,transform .3s;';
                    ind.innerHTML = '<i class="fas fa-check text-emerald-400 text-[10px]"></i> Auto-salvo';
                    document.body.appendChild(ind);
                }
                ind.style.opacity = '1';
                ind.style.transform = 'translateY(0)';
                clearTimeout(ind._timer);
                ind._timer = setTimeout(function() {
                    ind.style.opacity = '0';
                    ind.style.transform = 'translateY(6px)';
                }, 2500);
            }

            let autoSaveTimer = null;
            window.agendarAutoSave = function() {
                const form = document.getElementById('etpForm');
                if (!form || form.action.endsWith('/etps')) return;
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(function() { submeterEtp('salvar', null, true); }, 1500);
            };

            // Função para mostrar notificações
            function mostrarNotificacao(tipo, mensagem) {
                // Remove notificações existentes para evitar sobreposição
                const notificacoes = document.querySelectorAll('.notificacao-flutuante');
                notificacoes.forEach(n => {
                    n.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => n.remove(), 300);
                });

                // Cria a notificação
                const notificacao = document.createElement('div');
                
                // Estilo Premium
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

            // Inicialização
            toggleModalidadeFields();
            const modalidadeAtual = modalidadeSelect.value;
            if (modalidadeAtual === 'dispensa') {
                const opcoesDisp = document.getElementById('tipo-opcoes-dispensa');
                opcoesDisp.classList.remove('hidden');
                const tipoSelecionado = document.querySelector('input[name="tipo_contratacao"]:checked');
                if (tipoSelecionado) toggleContratacaoTipo();
                else document.getElementById('area-itens-sem-lote').classList.remove('hidden');
            } else {
                toggleContratacaoTipo();
            }

            inicializarTodasBuscas();
            initSortable('itens-selecionados-sem-lote');
            document.querySelectorAll('.lote-card').forEach((card) => {
                const match = card.id.match(/lote-(\d+)/);
                if (match) initSortable(`itens-selecionados-lote-${match[1]}`);
            });
        });

        /* ══════════════════════════════════════════════════════════
           MODAL: CRIAR ITEM RÁPIDO
        ══════════════════════════════════════════════════════════ */
        let activeLoteIndexManual = null;
        window.abrirModalCriarItem = function(loteIndex = null) {
            activeLoteIndexManual = loteIndex;
            document.getElementById('modalCriarItem').classList.remove('hidden');
            document.getElementById('novo_item_descricao').value = '';
            document.getElementById('novo_item_unidade').value = 'UND';
            document.getElementById('novo_item_quantidade').value = '1';
            document.getElementById('criar-item-erro').classList.add('hidden');
            document.getElementById('criar-item-sucesso').classList.add('hidden');
            setTimeout(() => document.getElementById('novo_item_descricao').focus(), 100);
        };

        window.fecharModalCriarItem = function() {
            document.getElementById('modalCriarItem').classList.add('hidden');
            activeLoteIndexManual = null;
        };

        document.getElementById('modal-criar-item-overlay').addEventListener('click', fecharModalCriarItem);

        // Confirmar com Enter
        document.getElementById('novo_item_descricao').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmarCriarItem();
            }
        });

        window.confirmarCriarItem = function() {
            const descricao = document.getElementById('novo_item_descricao').value.trim();
            const unidade = document.getElementById('novo_item_unidade').value;
            const quantidade = document.getElementById('novo_item_quantidade').value;
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
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        descricao_item: descricao,
                        unidade_medida: unidade
                    })
                })
                .then(handleCsrfExpirado)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const item = data.item;
                        adicionarItemAosListas(item);

                        if (activeLoteIndexManual !== null) {
                            const loteCheckbox = document.querySelector(`#lista_itens_lote_${activeLoteIndexManual} .item-checkbox[value="${item.id}"]`);
                            if (loteCheckbox) {
                                loteCheckbox.checked = true;
                                toggleItemSelecionado(loteCheckbox, activeLoteIndexManual, unidade, quantidade);
                            }
                        } else {
                            const globalCheckbox = document.querySelector(`#lista_itens_global .item-checkbox[value="${item.id}"]`);
                            if (globalCheckbox) {
                                globalCheckbox.checked = true;
                                toggleItemSelecionado(globalCheckbox, null, unidade, quantidade);
                            }
                        }

                        sucessoDiv.textContent = `Item "${item.descricao_item}" criado e selecionado com sucesso!`;
                        sucessoDiv.classList.remove('hidden');
                        setTimeout(fecharModalCriarItem, 1000);
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

        /**
         * Adiciona o novo item dinamicamente em todas as listas de itens visíveis
         * (lista global e listas de cada lote existente).
         */
        function adicionarItemAosListas(item) {
            const novoCheckboxHtml = (loteIndex) => {
                const dataLoteIndex = loteIndex !== null ? `data-lote-index="${loteIndex}"` : '';
                const onchange = loteIndex !== null ?
                    `onchange="toggleItemSelecionado(this, ${loteIndex})"` :
                    `onchange="toggleItemSelecionado(this, null)"`;
                // CORREÇÃO: Adicionar data-descricao em minúsculas para busca
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
            if (listaGlobal) {
                listaGlobal.insertAdjacentHTML('beforeend', novoCheckboxHtml(null));
            }

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
        const btnFecharImportar = document.getElementById('btnFecharModalImportar');
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

        if (btnImportar) {
            btnImportar.addEventListener('click', function() {
                tipoContratacaoAtual = document.querySelector('input[name="tipo_contratacao"]:checked')?.value;
                if (!tipoContratacaoAtual) {
                    alert('Selecione o tipo de contratação primeiro.');
                    return;
                }
                resetarModal();
                const infoLote = document.getElementById('info-lote-import');
                if (infoLote) infoLote.classList.toggle('hidden', tipoContratacaoAtual !== 'lote');
                modalImportar.classList.remove('hidden');
                setTimeout(() => modalImportar.classList.add('show'), 10);
            });
        }

        function fecharModal() {
            if (modalImportar) {
                modalImportar.classList.remove('show');
                setTimeout(() => {
                    modalImportar.classList.add('hidden');
                    resetarModal();
                }, 300);
            }
        }

        if (btnCancelar) btnCancelar.addEventListener('click', fecharModal);
        if (btnFecharImportar) btnFecharImportar.addEventListener('click', fecharModal);
        if (overlay) overlay.addEventListener('click', fecharModal);

        function resetarModal() {
            if (arquivoInput) arquivoInput.value = '';
            if (nomeArquivoSpan) nomeArquivoSpan.classList.add('hidden');
            if (areaProgresso) areaProgresso.classList.add('hidden');
            if (mensagemErro) mensagemErro.classList.add('hidden');
            if (previaItens) previaItens.classList.add('hidden');
            if (btnConfirmar) btnConfirmar.disabled = true;
            const infoLote = document.getElementById('info-lote-import');
            if (infoLote) infoLote.classList.add('hidden');
            itensImportados = [];
        }

        if (btnBaixarModelo) {
            btnBaixarModelo.addEventListener('click', function() {
                const dados = [
                    ["Descricao", "Unidade", "Quantidade"],
                    ["Caneta Azul", "UN", 50]
                ];
                const ws = XLSX.utils.aoa_to_sheet(dados);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Modelo");
                XLSX.writeFile(wb, "modelo_importacao_itens.xlsx");
            });
        }

        if (arquivoInput) {
            arquivoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;
                if (file.size > 10 * 1024 * 1024) {
                    mostrarErro('Arquivo muito grande. Máximo 10MB.');
                    return;
                }
                if (nomeArquivoSpan) {
                    nomeArquivoSpan.textContent = `Arquivo selecionado: ${file.name}`;
                    nomeArquivoSpan.classList.remove('hidden');
                }
                enviarArquivo(file);
            });
        }

        function enviarArquivo(file) {
            if (areaProgresso) areaProgresso.classList.remove('hidden');
            if (barraProgresso) barraProgresso.style.width = '30%';
            if (percentualProgresso) percentualProgresso.textContent = '30%';
            const formData = new FormData();
            formData.append('arquivo_excel', file);
            fetch('{{ route('admin.etps.importar-itens') }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: formData
                })
                .then(handleCsrfExpirado)
                .then(r => {
                    if (barraProgresso) barraProgresso.style.width = '70%';
                    if (percentualProgresso) percentualProgresso.textContent = '70%';
                    return r.json();
                })
                .then(data => {
                    if (barraProgresso) barraProgresso.style.width = '100%';
                    if (percentualProgresso) percentualProgresso.textContent = '100%';
                    setTimeout(() => {
                        if (data.success) {
                            itensImportados = data.itens;
                            mostrarPrevia(itensImportados);
                            if (btnConfirmar) btnConfirmar.disabled = false;
                        } else mostrarErro(data.message);
                        if (areaProgresso) areaProgresso.classList.add('hidden');
                    }, 500);
                })
                .catch(err => {
                    mostrarErro('Erro ao enviar arquivo: ' + err.message);
                    if (areaProgresso) areaProgresso.classList.add('hidden');
                });
        }

        function mostrarErro(msg) {
            if (mensagemErro) {
                mensagemErro.textContent = msg;
                mensagemErro.classList.remove('hidden');
            }
            if (previaItens) previaItens.classList.add('hidden');
        }

        function mostrarPrevia(itens) {
            if (!listaIitensImportados) return;
            listaIitensImportados.innerHTML = '';
            itens.forEach(item => {
                const li = document.createElement('li');
                li.className = 'flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 shadow-sm';
                li.dataset.itemId = item.item_id;
                const descSafe = item.descricao.replace(/"/g, '&quot;').replace(/</g, '&lt;');
                li.innerHTML = `
                    <span class="drag-handle text-gray-400 hover:text-gray-600 flex-shrink-0 text-lg leading-none select-none" title="Arrastar para reordenar">⠿</span>
                    <input type="text" class="item-desc-input flex-1 text-sm text-gray-800 border border-transparent hover:border-gray-300 focus:border-[#009496] focus:outline-none focus:ring-1 focus:ring-[#009496] rounded px-2 py-0.5 transition-colors bg-transparent" value="${descSafe}" title="Clique para editar a descrição">
                    <span class="text-xs text-gray-500 whitespace-nowrap flex-shrink-0">${item.quantidade} ${item.unidade}</span>
                    <button type="button" class="flex-shrink-0 text-red-400 hover:text-red-600 transition-colors font-bold leading-none" onclick="removerItemPrevia(this)" title="Remover">✕</button>`;
                listaIitensImportados.appendChild(li);
            });

            const countEl = document.getElementById('contador-itens-importados');
            if (countEl) countEl.textContent = `(${itens.length} ${itens.length === 1 ? 'item' : 'itens'})`;

            if (typeof Sortable !== 'undefined') {
                new Sortable(listaIitensImportados, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost'
                });
            }

            if (previaItens) previaItens.classList.remove('hidden');
        }

        window.removerItemPrevia = function(btn) {
            const li = btn.closest('li');
            if (!li) return;
            const id = parseInt(li.dataset.itemId);
            itensImportados = itensImportados.filter(i => i.item_id !== id);
            li.remove();
            const restantes = listaIitensImportados ? listaIitensImportados.querySelectorAll('li').length : 0;
            const countEl = document.getElementById('contador-itens-importados');
            if (countEl) countEl.textContent = `(${restantes} ${restantes === 1 ? 'item' : 'itens'})`;
            if (!restantes) {
                if (btnConfirmar) btnConfirmar.disabled = true;
                if (previaItens) previaItens.classList.add('hidden');
            }
        };

        function coletarItensAtualizados() {
            if (!listaIitensImportados) return itensImportados;
            const resultado = [];
            listaIitensImportados.querySelectorAll('li[data-item-id]').forEach(li => {
                const id = parseInt(li.dataset.itemId);
                const original = itensImportados.find(i => i.item_id === id);
                if (!original) return;
                const input = li.querySelector('.item-desc-input');
                const descricao = (input && input.value.trim()) ? input.value.trim() : original.descricao;
                resultado.push({ ...original, descricao });
            });
            return resultado;
        }

        if (btnConfirmar) {
            btnConfirmar.addEventListener('click', function() {
                const itensAtualizados = coletarItensAtualizados();
                if (!itensAtualizados.length) return;
                if (tipoContratacaoAtual === 'lote') importarItensComLote(itensAtualizados);
                else importarItensSemLote(itensAtualizados);
                fecharModal();
            });
        }

        function buildUnidadeSelectHtml(name, valorImportado) {
            const val = (valorImportado || 'UND').toUpperCase();
            const conhecidos = Object.keys(UNIDADES_MEDIDA);
            let opts = Object.entries(UNIDADES_MEDIDA).map(([v, l]) =>
                `<option value="${v}"${v === val ? ' selected' : ''}>${l}</option>`
            ).join('');
            if (!conhecidos.includes(val) && valorImportado) {
                opts = `<option value="${valorImportado}" selected>${valorImportado}</option>` + opts;
            }
            return `<select name="${name}" required class="rounded-lg border-gray-300 text-xs py-1.5 focus:border-[#009496] focus:ring-[#009496] w-32 bg-gray-50/50 hover:bg-gray-50 transition">${opts}</select>`;
        }

        function buildItemSelecionadoHtml(containerId, id, descricao, loteIndex, unidade, quantidade) {
            const namePrefix = (loteIndex !== null && loteIndex !== undefined)
                ? `lotes[${loteIndex}][itens]`
                : 'itens';
            const loteArg = (loteIndex !== null && loteIndex !== undefined) ? loteIndex : 'null';
            const descSafe = String(descricao).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;');
            return `
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:border-[#009496]/30 transition overflow-hidden" id="item-${containerId}-${id}">
                <div class="flex flex-col md:flex-row md:items-center justify-between p-4 gap-4 cursor-pointer" onclick="toggleAccordionItem('${containerId}-${id}')" title="Clique para expandir/recolher">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <i class="fas fa-grip-vertical text-gray-300 hover:text-gray-500 cursor-grab px-1 drag-handle" title="Arrastar para reordenar" onclick="event.stopPropagation()"></i>
                        <span class="item-numero inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#009496]/10 text-[#009496] text-xs font-bold flex-shrink-0 border border-[#009496]/20">0</span>
                        <div class="min-w-0 flex-1 flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <p class="desc-item-${id} text-sm font-semibold text-gray-800 leading-relaxed truncate" title="${descSafe}">${descSafe}</p>
                            </div>
                        </div>
                        <button type="button" class="text-gray-400 hover:text-[#009496] focus:outline-none flex-shrink-0 transition-transform duration-200" id="chevron-item-${containerId}-${id}">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0" onclick="event.stopPropagation()">
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Unidade</span>
                            ${buildUnidadeSelectHtml(`${namePrefix}[${id}][unidade]`, unidade)}
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Quantidade</span>
                            <input type="number" name="${namePrefix}[${id}][quantidade]" value="${quantidade}" placeholder="Qtd" min="1" required class="rounded-lg border-gray-300 text-xs py-1.5 focus:border-[#009496] focus:ring-[#009496] w-20 bg-gray-50/50 hover:bg-gray-50 transition">
                        </div>
                        <div class="flex items-end self-end pb-0.5 gap-2">
                            <input type="hidden" name="${namePrefix}[${id}][item_id]" value="${id}">
                            <button type="button" onclick="openModalItemQuickEdit(${id}, document.querySelector('#body-item-${containerId}-${id} .desc-item-${id}').textContent)" class="btn-edit-item-${id} inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:text-[#009496] hover:bg-[#009496]/10 transition" title="Editar Descrição">
                                <i class="fas fa-edit text-sm"></i>
                            </button>
                            <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:text-red-600 hover:bg-red-50 focus:outline-none transition" onclick="removerItemSelecionado(${id}, ${loteArg})" title="Excluir Item">
                                <i class="fas fa-trash-alt text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div id="body-item-${containerId}-${id}" class="hidden border-t border-gray-100 bg-gray-50/80 p-4">
                    <h6 class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Descrição Completa</h6>
                    <p class="desc-item-${id} text-sm text-gray-700 whitespace-pre-wrap">${descSafe}</p>
                </div>
            </div>`;
        }

        function importarItensSemLote(itens) {
            const containerId = 'itens-selecionados-sem-lote';
            const container = document.getElementById(containerId);
            if (!container) return;
            itens.forEach(item => {
                if (document.getElementById(`item-${containerId}-${item.item_id}`)) return;
                container.insertAdjacentHTML('beforeend',
                    buildItemSelecionadoHtml(containerId, item.item_id, item.descricao, null, item.unidade, item.quantidade));
                const cb = document.querySelector(`.item-checkbox[value="${item.item_id}"]:not([data-lote-index])`);
                if (cb) cb.checked = true;
            });
            renumerarItens(containerId);
            if (typeof initSortable === 'function') initSortable(containerId);
            agendarAutoSave();
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
            const containerId = `itens-selecionados-lote-${index}`;
            const container = document.getElementById(containerId);
            if (!container) return;
            itens.forEach(item => {
                if (document.getElementById(`item-${containerId}-${item.item_id}`)) return;
                container.insertAdjacentHTML('beforeend',
                    buildItemSelecionadoHtml(containerId, item.item_id, item.descricao, index, item.unidade, item.quantidade));
                const cb = document.querySelector(`.item-checkbox[value="${item.item_id}"][data-lote-index="${index}"]`);
                if (cb) cb.checked = true;
            });
            renumerarItens(containerId);
            if (typeof initSortable === 'function') initSortable(containerId);
            agendarAutoSave();
        }
    </script>

@endsection
