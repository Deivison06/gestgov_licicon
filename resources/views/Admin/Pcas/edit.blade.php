@extends('layouts.app')
@section('page-title', 'Editar Plano de Contratação Anual (PCA)')
@section('page-subtitle', 'Editando PCA: ' . ($pca->numero_pca ?? $pca->id))

@section('content')
<div class="py-6 min-h-screen" x-data="pcaForm()">
    
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Editar PCA: {{ $pca->numero_pca ?? $pca->id }}</h2>
        <a href="{{ route('admin.pcas.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 mb-6 border border-red-200 shadow-sm rounded-xl bg-red-50">
            <h3 class="text-sm font-semibold text-red-800 mb-2">Ops! Encontramos alguns problemas:</h3>
            <ul class="list-disc list-inside text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
        <div class="p-6 md:p-8">
            
            <!-- Progress Bar -->
            <div class="max-w-4xl mx-auto mb-10">
                <div class="relative">
                    <div class="overflow-hidden h-2 text-xs flex rounded-full bg-gray-200">
                        <div :style="`width: ${(step - 1) * 50}%`" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-[#0596A2] transition-all duration-500 ease-in-out"></div>
                    </div>
                    <div class="absolute top-0 left-0 -mt-3 transform -translate-x-1/2">
                        <div class="w-8 h-8 mx-auto bg-white border-2 rounded-full text-lg flex items-center justify-center font-bold relative"
                             :class="step >= 1 ? 'border-[#0596A2] text-[#0596A2]' : 'border-gray-300 text-gray-400'">
                            1
                        </div>
                    </div>
                    <div class="absolute top-0 left-1/2 -mt-3 transform -translate-x-1/2">
                        <div class="w-8 h-8 mx-auto bg-white border-2 rounded-full text-lg flex items-center justify-center font-bold relative"
                             :class="step >= 2 ? 'border-[#0596A2] text-[#0596A2]' : 'border-gray-300 text-gray-400'">
                            2
                        </div>
                    </div>
                    <div class="absolute top-0 right-0 -mt-3 transform px-1 translate-x-1/2">
                        <div class="w-8 h-8 mx-auto bg-white border-2 rounded-full text-lg flex items-center justify-center font-bold"
                             :class="step >= 3 ? 'border-[#0596A2] text-[#0596A2]' : 'border-gray-300 text-gray-400'">
                            3
                        </div>
                    </div>
                </div>
                <div class="flex justify-between mt-3 text-sm">
                    <span :class="step >= 1 ? 'font-bold text-[#0596A2]' : 'text-gray-400 font-medium'">Dados Iniciais</span>
                    <span :class="step >= 2 ? 'font-bold text-[#0596A2]' : 'text-gray-400 font-medium'" class="text-center">Equipe de Elaboração</span>
                    <span :class="step >= 3 ? 'font-bold text-[#0596A2]' : 'text-gray-400 font-medium'" class="text-right">Itens de Contratação</span>
                </div>
            </div>

            <form action="{{ route('admin.pcas.update', $pca->id) }}" method="POST" id="pcaForm" @submit.prevent="submitForm">
                @csrf
                @method('PUT')
                
                <input type="hidden" name="status" x-model="status">

                <!-- STEP 1: DADOS GERAIS E EQUIPE DE ELABORAÇÃO -->
                <div x-show="step === 1" x-transition.opacity.duration.300ms>
                    <h3 class="text-xl font-bold text-gray-800 mb-6">Dados Gerais do Plano</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        @if(!$isPrefeituraUser)
                        <div class="md:col-span-2">
                            <label for="prefeitura_id" class="block text-sm font-medium text-gray-700 mb-1">Prefeitura <span class="text-red-500">*</span></label>
                            <select name="prefeitura_id" id="prefeitura_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none" required x-model="prefeitura_id" @change="loadSecretarias()">
                                <option value="">Selecione a Prefeitura</option>
                                @foreach($prefeituras as $pref)
                                    <option value="{{ $pref->id }}">{{ $pref->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prefeitura <span class="text-red-500">*</span></label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed" value="{{ $pca->prefeitura->nome ?? '' }}" readonly disabled>
                            <input type="hidden" name="prefeitura_id" value="{{ $pca->prefeitura_id }}">
                        </div>
                        @endif

                        <div class="md:col-span-1">
                            <label for="numero_pca" class="block text-sm font-medium text-gray-700 mb-1">Número do PCA (Opcional)</label>
                            <input type="text" id="numero_pca" name="numero_pca" value="{{ old('numero_pca', $pca->numero_pca) }}" placeholder="Ex: 002/26" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none">
                        </div>

                        <div class="md:col-span-1">
                            <label for="exercicio" class="block text-sm font-medium text-gray-700 mb-1">Exercício <span class="text-red-500">*</span></label>
                            <input type="text" id="exercicio" name="exercicio" required value="{{ old('exercicio', $pca->exercicio) }}" placeholder="Ex: 2026" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none">
                        </div>

                        <div class="md:col-span-2">
                            <label for="periodo_elaboracao_inicio" class="block text-sm font-medium text-gray-700 mb-1">Início Elaboração</label>
                            <input type="date" id="periodo_elaboracao_inicio" name="periodo_elaboracao_inicio" value="{{ old('periodo_elaboracao_inicio', $pca->periodo_elaboracao_inicio ? $pca->periodo_elaboracao_inicio->format('Y-m-d') : '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none">
                        </div>

                        <div class="md:col-span-2">
                            <label for="periodo_elaboracao_fim" class="block text-sm font-medium text-gray-700 mb-1">Fim Elaboração</label>
                            <input type="date" id="periodo_elaboracao_fim" name="periodo_elaboracao_fim" value="{{ old('periodo_elaboracao_fim', $pca->periodo_elaboracao_fim ? $pca->periodo_elaboracao_fim->format('Y-m-d') : '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none">
                        </div>
                    </div>

                    <div class="flex justify-end mt-8 border-t border-gray-200 pt-6">
                        <button type="button" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-white bg-[#0596A2] rounded-lg hover:bg-[#047a84] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0596A2]" @click="nextStep(2)">
                            Próximo Passo <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: EQUIPE DE ELABORAÇÃO -->
                <div x-show="step === 2" x-transition.opacity.duration.300ms style="display: none;">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Equipe de Elaboração</h3>
                    <p class="text-sm text-gray-500 mb-6">Adicione os responsáveis que farão parte da equipe do plano.</p>

                    <div class="overflow-x-auto bg-gray-50 rounded-lg border border-gray-200">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-1/3">Unidade <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-1/4">Responsável <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-1/6">Nº Portaria</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-1/6">Data Portaria</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <template x-for="(membro, index) in equipe" :key="membro.ui_id">
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-4 py-3">
                                            <select :name="'equipe_elaboracao['+index+'][unidade_id]'" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-[#0596A2] focus:border-[#0596A2] outline-none" x-model="membro.unidade_id" @change="updateResponsavel(index)" required>
                                                <option value="">Selecione...</option>
                                                @foreach($secretarias as $sec)
                                                    <option value="{{ $sec->id }}">{{ $sec->nome }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" :name="'equipe_elaboracao['+index+'][responsavel]'" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-[#0596A2] focus:border-[#0596A2] outline-none" x-model="membro.responsavel" required placeholder="Nome do Responsável">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" :name="'equipe_elaboracao['+index+'][numero_portaria]'" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-[#0596A2] focus:border-[#0596A2] outline-none" x-model="membro.numero_portaria" placeholder="Ex: 123/2026">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="date" :name="'equipe_elaboracao['+index+'][data_portaria]'" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-[#0596A2] focus:border-[#0596A2] outline-none" x-model="membro.data_portaria">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button type="button" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded-md transition-colors" @click="removeMembro(index)" title="Remover" x-show="equipe.length > 1">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <div class="px-4 py-3 bg-white border-t border-gray-100">
                            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-[#0596A2] bg-white border border-[#0596A2] rounded-lg hover:bg-[#0596A2] hover:text-white transition-colors" @click="addMembro()">
                                <i class="fas fa-plus"></i> Adicionar Membro
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-8 border-t border-gray-200 pt-6">
                        <button type="button" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0596A2]" @click="step = 1">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </button>
                        <button type="button" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-white bg-[#0596A2] rounded-lg hover:bg-[#047a84] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0596A2]" @click="nextStep(3)">
                            Próximo Passo <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- STEP 3: DETALHAMENTO DO PLANO (ITENS) -->
                <div x-show="step === 3" x-transition.opacity.duration.300ms style="display: none;">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Detalhamento do Plano</h3>
                    <p class="text-sm text-gray-500 mb-6">Adicione os itens de contratação estimados para o exercício.</p>

                    <!-- Card list container -->
                    <div class="space-y-6 mb-6">
                        <template x-for="(item, index) in itens" :key="item.ui_id">
                            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all relative">
                                <input type="hidden" :name="'itens['+index+'][id]'" x-model="item.id">
                                
                                <!-- Header of the Card -->
                                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex items-center justify-center w-7 h-7 bg-[#0596A2] text-white rounded-full text-xs font-bold shadow-sm" x-text="index + 1"></span>
                                        <h4 class="font-bold text-gray-850 text-base">Item de Contratação</h4>
                                    </div>
                                    <button type="button" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100/70 px-3.5 py-2 rounded-xl text-xs font-semibold transition-colors flex items-center gap-2" @click="removeItem(index)" title="Remover Item">
                                        <i class="fas fa-trash-alt text-xs"></i> Remover
                                    </button>
                                </div>

                                <!-- Grid of inputs -->
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-5">
                                    <!-- Unidade -->
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Unidade Requisitante <span class="text-red-500">*</span></label>
                                        <select :name="'itens['+index+'][unidade_requisitante_id]'" class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none transition-all" x-model="item.unidade_requisitante_id" required>
                                            <option value="">Selecione...</option>
                                            @foreach($secretarias as $sec)
                                                <option value="{{ $sec->id }}">{{ $sec->nome }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Modalidade -->
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Modalidade</label>
                                        <select :name="'itens['+index+'][modalidade]'" class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none transition-all" x-model="item.modalidade">
                                            <option value="">Selecione...</option>
                                            @foreach($modalidades as $mod)
                                                <option value="{{ $mod }}">{{ $mod }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Valor Estimado -->
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Valor Estimado <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <span class="absolute left-3.5 top-2.5 text-sm text-gray-400 font-semibold">R$</span>
                                            <input type="number" step="0.01" min="0" :name="'itens['+index+'][valor_estimado]'" class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none font-semibold text-green-700 transition-all" x-model="item.valor_estimado" required placeholder="0,00">
                                        </div>
                                    </div>

                                    <!-- Prioridade -->
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Grau de Prioridade <span class="text-red-500">*</span></label>
                                        <select :name="'itens['+index+'][grau_prioridade]'" class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none transition-all" x-model="item.grau_prioridade" required>
                                            <option value="alto">Alto</option>
                                            <option value="medio">Médio</option>
                                            <option value="baixo">Baixo</option>
                                        </select>
                                    </div>

                                    <!-- Data Início -->
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Início Providências</label>
                                        <input type="date" :name="'itens['+index+'][data_inicio_providencias]'" class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none transition-all" x-model="item.data_inicio_providencias">
                                    </div>

                                    <!-- Data Conclusão -->
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Conclusão Desejada</label>
                                        <input type="date" :name="'itens['+index+'][data_desejada_conclusao]'" class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none transition-all" x-model="item.data_desejada_conclusao">
                                    </div>

                                    <!-- Prorrogação -->
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Prorrogável?</label>
                                        <select :name="'itens['+index+'][prorrogacao_contrato]'" class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none transition-all" x-model="item.prorrogacao_contrato">
                                            <option value="0" :selected="item.prorrogacao_contrato == false || item.prorrogacao_contrato == '0'">Não</option>
                                            <option value="1" :selected="item.prorrogacao_contrato == true || item.prorrogacao_contrato == '1'">Sim</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Objeto (Full width bottom row) -->
                                <div class="border-t border-gray-100 pt-5">
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Descrição do Objeto<span class="text-red-500">*</span></label>
                                    <textarea rows="3" :name="'itens['+index+'][descricao_classe_grupo]'" class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none resize-y min-h-[90px] transition-all" x-model="item.descricao_classe_grupo" required placeholder="Descreva o objeto de contratação detalhadamente..."></textarea>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Total Estimado Geral Summary Card & Add Item Button -->
                    <div class="flex flex-col md:flex-row justify-between items-center bg-gray-50 border border-gray-250/70 rounded-2xl p-5 md:p-6 mb-8 gap-4 shadow-sm">
                        <div class="flex flex-wrap items-center gap-3">
                            <button type="button" class="inline-flex items-center gap-2 px-5 py-3 text-sm font-semibold text-white bg-emerald-600 border border-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm active:scale-95" @click="addItem()">
                                <i class="fas fa-plus"></i> Adicionar Item
                            </button>
                            <div class="text-xs text-gray-500 font-bold bg-white border border-gray-200 px-3 py-2 rounded-xl">
                                Total de itens: <span class="text-[#0596A2]" x-text="itens.length"></span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5">Total Estimado Geral</span>
                            <span class="text-2xl md:text-3xl font-black text-green-700 tracking-tight">
                                R$ <span x-text="formatCurrency(totalValue)"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex items-center justify-between mt-10 pt-6 border-t border-gray-200">
                        <button type="button" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0596A2]" @click="step = 2">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </button>
                        <div class="flex gap-3">
                            <button type="button" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 shadow-sm transition-colors" @click="saveDraft()">
                                <i class="fas fa-save"></i> Salvar Rascunho
                            </button>
                            <button type="button" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-md transition-all hover:scale-105" @click="saveFinal()">
                                <i class="fas fa-check-circle"></i> Concluir Plano
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        const existingEquipe = {!! $equipeElaboracaoJson !!};
        let formattedEquipe = Array.isArray(existingEquipe) && existingEquipe.length > 0 
            ? existingEquipe.map(item => ({...item, unidade_id: item.unidade_id ? String(item.unidade_id) : '', ui_id: `e_${Math.random()}`}))
            : [{ ui_id: Date.now(), unidade_id: '', responsavel: '', numero_portaria: '', data_portaria: '' }];

        const existingItens = {!! json_encode($pca->itens->map(function($i) {
            return [
                'id' => $i->id,
                'unidade_requisitante_id' => $i->unidade_requisitante_id ? (string)$i->unidade_requisitante_id : '',
                'modalidade' => $i->modalidade,
                'descricao_classe_grupo' => $i->descricao_classe_grupo,
                'valor_estimado' => $i->valor_estimado,
                'grau_prioridade' => $i->grau_prioridade,
                'data_inicio_providencias' => $i->data_inicio_providencias ? $i->data_inicio_providencias->format('Y-m-d') : '',
                'data_desejada_conclusao' => $i->data_desejada_conclusao ? $i->data_desejada_conclusao->format('Y-m-d') : '',
                'prorrogacao_contrato' => $i->prorrogacao_contrato ? '1' : '0'
            ];
        })) !!};

        let formattedItens = Array.isArray(existingItens) && existingItens.length > 0
            ? existingItens.map(item => ({...item, unidade_requisitante_id: item.unidade_requisitante_id ? String(item.unidade_requisitante_id) : '', ui_id: `i_${Math.random()}`}))
            : [{ ui_id: Date.now() + 1, id: '', unidade_requisitante_id: '', modalidade: '', descricao_classe_grupo: '', valor_estimado: '', grau_prioridade: 'medio', data_inicio_providencias: '', data_desejada_conclusao: '', prorrogacao_contrato: '0' }];

        Alpine.data('pcaForm', () => ({
            step: 1,
            prefeitura_id: '{{ $pca->prefeitura_id }}',
            secretariasList: @json($secretarias),
            
            equipe: formattedEquipe,
            itens: formattedItens,

            status: '{{ $pca->status }}',

            get totalValue() {
                return this.itens.reduce((total, item) => {
                    const val = parseFloat(item.valor_estimado) || 0;
                    return total + val;
                }, 0);
            },

            formatCurrency(value) {
                return value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            async loadSecretarias() {
                @if(!$isPrefeituraUser)
                if (!this.prefeitura_id) {
                    this.secretariasList = [];
                    return;
                }
                try {
                    const todasSecretarias = @json(App\Models\Unidade::orderBy('nome')->get());
                    this.secretariasList = todasSecretarias.filter(s => s.prefeitura_id == this.prefeitura_id);
                } catch (error) {
                    console.error("Erro ao carregar secretarias", error);
                }
                @endif
            },

            addMembro() {
                this.equipe.push({ ui_id: Date.now(), unidade_id: '', responsavel: '', numero_portaria: '', data_portaria: '' });
            },

            updateResponsavel(index) {
                const unidadeId = this.equipe[index].unidade_id;
                if (!unidadeId) {
                    this.equipe[index].responsavel = '';
                    return;
                }
                const unidade = this.secretariasList.find(s => s.id == unidadeId);
                if (unidade && unidade.servidor_responsavel) {
                    this.equipe[index].responsavel = unidade.servidor_responsavel;
                } else {
                    this.equipe[index].responsavel = '';
                }
            },

            removeMembro(index) {
                if(this.equipe.length > 1) {
                    this.equipe.splice(index, 1);
                }
            },

            addItem() {
                this.itens.push({ ui_id: Date.now(), id: '', unidade_requisitante_id: '', modalidade: '', descricao_classe_grupo: '', valor_estimado: '', grau_prioridade: 'medio', data_inicio_providencias: '', data_desejada_conclusao: '', prorrogacao_contrato: '0' });
            },

            removeItem(index) {
                this.itens.splice(index, 1);
            },

            nextStep(targetStep) {
                // Se estiver avançando, valida o passo atual
                if (targetStep > this.step) {
                    const stepDiv = document.querySelector(`div[x-show="step === ${this.step}"]`);
                    if (stepDiv) {
                        const requiredFields = stepDiv.querySelectorAll('[required]');
                        let isValid = true;
                        
                        for (let field of requiredFields) {
                            if (!field.checkValidity()) {
                                field.reportValidity();
                                isValid = false;
                                break; 
                            }
                        }

                        if (!isValid) return;
                    }

                    if (this.step === 2 && this.equipe.length === 0) {
                        alert('Adicione ao menos 1 membro na equipe de elaboração.');
                        return;
                    }
                }
                
                window.scrollTo({ top: 0, behavior: 'smooth' });
                this.step = targetStep;
            },

            saveDraft() {
                this.status = 'pendente';
                const form = document.getElementById('pcaForm');
                form.setAttribute('novalidate', 'novalidate');
                form.submit();
            },

            saveFinal() {
                // Valida o passo 3
                const stepDiv = document.querySelector(`div[x-show="step === 3"]`);
                if (stepDiv) {
                    const requiredFields = stepDiv.querySelectorAll('[required]');
                    let isValid = true;
                    
                    for (let field of requiredFields) {
                        if (!field.checkValidity()) {
                            field.reportValidity();
                            isValid = false;
                            break;
                        }
                    }

                    if (!isValid) return;
                }
                
                if(!confirm('Tem certeza que deseja enviar para análise? Você não poderá editá-lo até a aprovação.')) {
                    return;
                }

                this.status = 'em_analise';
                const form = document.getElementById('pcaForm');
                form.setAttribute('novalidate', 'novalidate');
                form.submit();
            },

            init() {
            }
        }));
    });
</script>
@endsection
