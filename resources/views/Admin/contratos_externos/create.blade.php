@extends('layouts.app')
@section('page-title', 'Cadastro de Novo Contrato')
@section('page-subtitle', 'Preencha os dados do contrato, vigência e da empresa contratada')

@section('content')

<style>
.border-red-500 {
    border-color: #ef4444 !important;
}
.ring-red-200 {
    --tw-ring-color: rgba(254, 202, 202, 0.5);
}
.select-required:invalid {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}
</style>

<div class="overflow-hidden bg-white shadow-sm rounded-xl">
    {{-- Debug Information
    @if(config('app.debug'))
        <div class="p-3 mb-4 text-xs bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="font-semibold text-yellow-800">DEBUG INFORMATION</div>
            <div class="grid grid-cols-2 gap-2 mt-1">
                <div><span class="font-medium">Prefeitura selecionada:</span> {{ old('prefeitura_id') ?? 'Nenhuma' }}</div>
                <div><span class="font-medium">Secretaria antiga:</span> {{ old('unidade_id') ?? 'Nenhuma' }}</div>
                <div><span class="font-medium">Usuário ID:</span> {{ auth()->user()->id }}</div>
                <div><span class="font-medium">Usuário Prefeitura ID:</span> {{ auth()->user()->prefeitura_id ?? 'null' }}</div>
                <div><span class="font-medium">Tipo Usuário:</span> {{ $isPrefeituraUser ? 'Prefeitura' : 'Admin' }}</div>
            </div>
        </div>
    @endif --}}

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="p-4 mb-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center">
                <i class="mr-2 text-red-500 fas fa-exclamation-triangle"></i>
                <h3 class="font-semibold text-red-800">Erros no Formulário</h3>
            </div>
            <ul class="mt-2 ml-5 text-sm text-red-700 list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="px-6 py-6" action="{{ route('admin.contratos.store') }}" method="POST" enctype="multipart/form-data" id="formContrato">
        @csrf

        {{-- SEÇÃO 1: DADOS DO CONTRATO --}}
        <div class="pb-6 border-b border-gray-100">
            <h3 class="flex items-center gap-2 text-lg font-medium text-gray-700">
                <i class="fas fa-file-signature text-[#009496]"></i> Dados do Contrato
            </h3>
            
            {{-- PREFEITURA --}}
            <div class="mt-4">
                @php
                    $isPrefeituraUser = auth()->user()->hasRole('prefeitura') && auth()->user()->prefeitura_id;
                @endphp
                
                <label for="prefeitura_id" class="block text-sm font-medium text-gray-700">Prefeitura</label>
                
                @if($isPrefeituraUser)
                    <input type="hidden" name="prefeitura_id" value="{{ auth()->user()->prefeitura_id }}" id="hidden_prefeitura_id">
                    <select disabled class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496] bg-gray-50">
                        <option value="{{ $prefeituras->first()->id }}" selected>
                            {{ $prefeituras->first()->nome }}
                        </option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">
                        <i class="mr-1 text-blue-500 fas fa-info-circle"></i>
                        Você está criando um contrato para sua própria prefeitura
                    </p>
                @else
                    <select name="prefeitura_id" id="prefeitura_id" 
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]"
                            required>
                        <option value="">Selecione a prefeitura</option>
                        @foreach ($prefeituras as $prefeitura)
                        <option value="{{ $prefeitura->id }}" {{ old('prefeitura_id') == $prefeitura->id ? 'selected' : '' }}>
                            {{ $prefeitura->nome }}
                        </option>
                        @endforeach
                    </select>
                @endif
                
                @error('prefeitura_id')
                    <p class="mt-1 text-sm text-red-600">
                        <i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Linha 1: Identificação --}}
            <div class="grid grid-cols-1 gap-6 mt-6 md:grid-cols-2">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        <span class="text-red-500">*</span> Nº Processo
                    </label>
                    <input required 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                           type="text" 
                           name="numero_processo" 
                           value="{{ old('numero_processo') }}" 
                           placeholder="Ex: 123/2024">
                    @error('numero_processo')
                        <p class="mt-1 text-sm text-red-600">
                            <i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Nº Contrato</label>
                    <input class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                           type="text" 
                           name="numero_contrato" 
                           value="{{ old('numero_contrato') }}" 
                           placeholder="Ex: 001/2024">
                    @error('numero_contrato')
                        <p class="mt-1 text-sm text-red-600">
                            <i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            {{-- Linha 2: Modalidade e Secretaria --}}
            <div class="grid grid-cols-1 gap-6 mt-6 md:grid-cols-2">
                {{-- MODALIDADE --}}
                <div>
                    <label for="modalidade" class="block text-sm font-medium text-gray-700">Modalidade</label>
                    <select name="modalidade" 
                            id="modalidade" 
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                        <option value="">Selecione a modalidade</option>
                        @foreach (\App\Enums\ModalidadeEnum::cases() as $modalidade)
                        <option value="{{ $modalidade->value }}" {{ old('modalidade') == $modalidade->value ? 'selected' : '' }}>
                            {{ $modalidade->getDisplayName() }}
                        </option>
                        @endforeach
                    </select>
                    @error('modalidade')
                        <p class="mt-1 text-sm text-red-600">
                            <i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- SECRETARIA --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        <span class="text-red-500">*</span> Secretaria / Órgão Contratante
                    </label>
                    
                    <select required
                            name="unidade_id"
                            id="unidade_id"
                            data-old="{{ old('unidade_id') ?? '' }}"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg
                                focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors
                                select-required">
                        <option value="">Selecione a secretaria</option>
                        {{-- Para usuários prefeitura, as opções são carregadas diretamente --}}
                        @if($isPrefeituraUser && $secretarias->isNotEmpty())
                            @foreach($secretarias as $secretaria)
                                <option value="{{ $secretaria->id }}" 
                                        {{ old('unidade_id') == $secretaria->id ? 'selected' : '' }}>
                                    {{ $secretaria->nome }}
                                </option>
                            @endforeach
                        @endif
                    </select>

                    @error('unidade_id')
                        <p class="mt-1 text-sm text-red-600">
                            <i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            {{-- Linha 3: TIPO DE CONTRATO --}}
            <div class="mt-6">
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Tipo de Contrato
                </label>
                <select required 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                        name="tipo_contrato">
                    <option value="">Selecione</option>
                    <option value="Compras" {{ old('tipo_contrato') == 'Compras' ? 'selected' : '' }}>Compras</option>
                    <option value="Serviço" {{ old('tipo_contrato') == 'Serviço' ? 'selected' : '' }}>Serviço</option>
                </select>
                @error('tipo_contrato')
                    <p class="mt-1 text-sm text-red-600">
                        <i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Objeto --}}
            <div class="mt-6">
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Objeto do Contrato
                </label>
                <textarea required 
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                          rows="3" 
                          name="objeto" 
                          placeholder="Descrição detalhada do objeto...">{{ old('objeto') }}</textarea>
                @error('objeto')
                    <p class="mt-1 text-sm text-red-600">
                        <i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        {{-- SEÇÃO 2: VIGÊNCIA E VALORES --}}
        <div class="py-6 border-b border-gray-100">
            <h3 class="flex items-center gap-2 text-lg font-medium text-gray-700">
                <i class="fas fa-calendar-alt text-[#009496]"></i> Vigência e Valores
            </h3>
            <div class="grid grid-cols-1 gap-6 mt-4 md:grid-cols-3">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        <span class="text-red-500">*</span> Valor Total (R$)
                    </label>
                    <input required 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors money-mask"
                           type="text" 
                           name="valor_total" 
                           value="{{ old('valor_total') }}" 
                           placeholder="R$ 0,00">
                    @error('valor_total')
                        <p class="mt-1 text-sm text-red-600">
                            <i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}
                        </p>
                    @enderror
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Data Início</label>
                    <input class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                           type="date" 
                           name="data_inicio" 
                           value="{{ old('data_inicio') }}">
                    @error('data_inicio')
                        <p class="mt-1 text-sm text-red-600">
                            <i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        <span class="text-red-500">*</span> Data Finalização
                    </label>
                    <input required 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                           type="date" 
                           name="data_finalizacao" 
                           value="{{ old('data_finalizacao') }}">
                    @error('data_finalizacao')
                        <p class="mt-1 text-sm text-red-600">
                            <i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            {{-- Data de Assinatura --}}
            <div class="mt-6">
                <label class="block mb-2 text-sm font-medium text-gray-700">Data de Assinatura</label>
                <input class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                       type="date" 
                       name="data_assinatura" 
                       value="{{ old('data_assinatura') }}">
                @error('data_assinatura')
                    <p class="mt-1 text-sm text-red-600">
                        <i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}
                    </p>
                @enderror
            </div>
            
            {{-- Upload do Arquivo PDF do Contrato --}}
            <div class="mt-6">
                <label class="block mb-2 text-sm font-medium text-gray-700">Arquivo do Contrato (PDF)</label>
                <div class="flex items-center gap-4">
                    <input type="file" 
                           name="arquivo_contrato" 
                           id="arquivo_contrato" 
                           class="block w-full px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#009496] file:text-white hover:file:bg-[#244853] transition-colors cursor-pointer focus:ring-2 focus:ring-[#009496] focus:border-[#009496]"
                           accept=".pdf">
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    <i class="mr-1 fas fa-info-circle"></i>
                    Apenas arquivos PDF, tamanho máximo: 5MB
                </p>
                @error('arquivo_contrato')
                    <p class="mt-1 text-sm text-red-600">
                        <i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        {{-- SEÇÃO 3: EMPRESA --}}
        <div class="pt-6 space-y-6">
            <h3 class="flex items-center gap-2 text-lg font-medium text-gray-700">
                <i class="fas fa-building text-[#009496]"></i> Informações da Empresa
            </h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        <span class="text-red-500">*</span> Razão Social
                    </label>
                    <input required 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                           type="text" 
                           name="empresa[razao_social]" 
                           value="{{ old('empresa.razao_social') }}" 
                           placeholder="Razão Social">
                    @error('empresa.razao_social')
                        <p class="mt-1 text-sm text-red-600">
                            <i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        <span class="text-red-500">*</span> CNPJ
                    </label>
                    <input required 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors cnpj-mask"
                           type="text" 
                           name="empresa[cnpj]" 
                           value="{{ old('empresa.cnpj') }}" 
                           placeholder="00.000.000/0000-00">
                    @error('empresa.cnpj')
                        <p class="mt-1 text-sm text-red-600">
                            <i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Representante</label>
                    <input class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                           type="text" 
                           name="empresa[representante]" 
                           value="{{ old('empresa.representante') }}" 
                           placeholder="Nome do representante">
                    @error('empresa.representante')
                        <p class="mt-1 text-sm text-red-600">
                            <i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        <span class="text-red-500">*</span> Endereço
                    </label>
                    <input required 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                           type="text" 
                           name="empresa[endereco]" 
                           value="{{ old('empresa.endereco') }}" 
                           placeholder="Endereço completo">
                    @error('empresa.endereco')
                        <p class="mt-1 text-sm text-red-600">
                            <i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- BOTÕES DE AÇÃO --}}
        <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-gray-100">
            <a href="{{ route('admin.contratos.index') }}"
               class="flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors bg-gray-100 rounded-lg hover:bg-gray-200 shadow-sm">
                <i class="mr-2 fas fa-times"></i>
                Cancelar
            </a>
            <button type="submit"
                    class="flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-white transition-colors bg-[#009496] rounded-lg hover:bg-[#244853] shadow-sm">
                <i class="mr-2 fas fa-save"></i>
                Salvar Contrato
            </button>
        </div>
    </form>
</div>

{{-- MASCARAS DE INPUT --}}
<script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.6/dist/inputmask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.6/dist/jquery.inputmask.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('🔧 Inicializando formulário de contrato...');
    
    const unidadeSelect = document.getElementById('unidade_id');
    const oldUnidade = unidadeSelect.dataset.old || '';
    const form = document.getElementById('formContrato');
    
    // Aplicar máscaras
    if (typeof Inputmask !== 'undefined') {
        // Máscara para valores monetários
        Inputmask('currency', {
            radixPoint: ',',
            groupSeparator: '.',
            prefix: 'R$ ',
            placeholder: '0,00',
            numericInput: true,
            rightAlign: false,
            autoUnmask: true
        }).mask(document.querySelector('.money-mask'));
        
        // Máscara para CNPJ
        Inputmask('99.999.999/9999-99', {
            placeholder: '__.___.___/____-__'
        }).mask(document.querySelector('.cnpj-mask'));
    }
    
    // ============================================
    // 🎯 LÓGICA PARA CARREGAR SECRETARIAS
    // ============================================
    
    @if(!$isPrefeituraUser)
        // Para usuários ADMIN: Carregamento dinâmico baseado na prefeitura
        const prefeituraSelect = document.getElementById('prefeitura_id');
        const unidadesPorPrefeitura = @json($secretarias->groupBy('prefeitura_id')->map(fn($u) => $u->map(fn($s) => ['id' => $s->id, 'nome' => $s->nome])));
        
        console.log('👤 Usuário ADMIN detectado');
        console.log('Unidades por prefeitura:', unidadesPorPrefeitura);
        
        function carregarUnidades(prefeituraId) {
            console.log('🔄 Carregando unidades para prefeitura:', prefeituraId);
            
            // Limpa o select mantendo apenas a primeira opção
            while (unidadeSelect.options.length > 1) {
                unidadeSelect.remove(1);
            }
            
            if (!prefeituraId || !unidadesPorPrefeitura[prefeituraId]) {
                console.log('⚠️ Nenhuma unidade encontrada para esta prefeitura');
                return;
            }
            
            // Adiciona as opções da prefeitura selecionada
            unidadesPorPrefeitura[prefeituraId].forEach(unidade => {
                const option = document.createElement('option');
                option.value = unidade.id;
                option.textContent = unidade.nome;
                unidadeSelect.appendChild(option);
            });
            
            // Restaura valor antigo (caso de erro de validação)
            if (oldUnidade) {
                const optionExists = Array.from(unidadeSelect.options)
                    .some(option => option.value === oldUnidade);
                
                if (optionExists) {
                    unidadeSelect.value = oldUnidade;
                    console.log('✅ Valor antigo restaurado:', oldUnidade);
                }
            }
            
            console.log(`✅ ${unidadesPorPrefeitura[prefeituraId].length} unidades carregadas`);
        }
        
        if (prefeituraSelect) {
            prefeituraSelect.addEventListener('change', function () {
                carregarUnidades(this.value);
            });
            
            // Carrega automaticamente se já tiver prefeitura selecionada
            if (prefeituraSelect.value) {
                console.log('🏛️ Prefeitura já selecionada:', prefeituraSelect.value);
                carregarUnidades(prefeituraSelect.value);
            }
        }
    @else
        // Para usuários PREFEITURA: Unidades já carregadas no Blade
        console.log('👤 Usuário PREFEITURA detectado');
        console.log('🏛️ Prefeitura ID:', @json(auth()->user()->prefeitura_id));
        console.log('📋 Unidades disponíveis:', @json($secretarias->pluck('nome')));
        
        // Apenas restaura o valor antigo se existir
        if (oldUnidade) {
            setTimeout(() => {
                unidadeSelect.value = oldUnidade;
                console.log('✅ Valor antigo restaurado para usuário prefeitura:', oldUnidade);
            }, 100);
        }
    @endif
    
    // ============================================
    // 🚫 VALIDAÇÃO NO SUBMIT
    // ============================================
    
    form.addEventListener('submit', function (e) {
        console.log('📤 Validando envio do formulário...');
        console.log('🔍 Valor da secretaria:', unidadeSelect.value);
        
        if (!unidadeSelect.value) {
            e.preventDefault();
            
            // Mensagem de erro
            Swal.fire({
                icon: 'error',
                title: 'Campo Obrigatório',
                text: 'Por favor, selecione uma secretaria antes de salvar o contrato.',
                confirmButtonColor: '#009496',
            });
            
            // Destaca o campo
            unidadeSelect.focus();
            unidadeSelect.classList.add('border-red-500', 'ring-2', 'ring-red-200');
            
            // Scroll para o campo
            unidadeSelect.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            
            console.log('❌ Envio bloqueado: secretaria não selecionada');
            return false;
        }
        
        // Validação adicional de datas
        const dataInicio = document.querySelector('input[name="data_inicio"]').value;
        const dataFinalizacao = document.querySelector('input[name="data_finalizacao"]').value;
        
        if (dataInicio && dataFinalizacao) {
            const inicio = new Date(dataInicio);
            const fim = new Date(dataFinalizacao);
            
            if (inicio > fim) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Datas Inválidas',
                    text: 'A data de início não pode ser posterior à data de finalização.',
                    confirmButtonColor: '#009496',
                });
                console.log('❌ Envio bloqueado: datas inválidas');
                return false;
            }
        }
        
        console.log('✅ Validação passou, enviando formulário...');
        return true;
    });
    
    // Remove destaque de erro quando o usuário selecionar uma opção
    unidadeSelect.addEventListener('change', function() {
        this.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
        console.log('🎨 Estilo de erro removido');
    });
    
    // ============================================
    // 📋 VALIDAÇÃO EM TEMPO REAL
    // ============================================
    
    // Valida CNPJ em tempo real
    const cnpjInput = document.querySelector('input[name="empresa[cnpj]"]');
    if (cnpjInput) {
        cnpjInput.addEventListener('blur', function() {
            const cnpj = this.value.replace(/\D/g, '');
            if (cnpj.length === 14 && !validarCNPJ(cnpj)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'CNPJ Inválido',
                    text: 'O CNPJ digitado não é válido. Verifique os números.',
                    confirmButtonColor: '#009496',
                });
                this.focus();
            }
        });
    }
    
    // Função para validar CNPJ
    function validarCNPJ(cnpj) {
        cnpj = cnpj.replace(/[^\d]+/g,'');
        
        if(cnpj == '') return false;
        if (cnpj.length != 14) return false;
        
        // Elimina CNPJs invalidos conhecidos
        if (cnpj == "00000000000000" || 
            cnpj == "11111111111111" || 
            cnpj == "22222222222222" || 
            cnpj == "33333333333333" || 
            cnpj == "44444444444444" || 
            cnpj == "55555555555555" || 
            cnpj == "66666666666666" || 
            cnpj == "77777777777777" || 
            cnpj == "88888888888888" || 
            cnpj == "99999999999999")
            return false;
            
        // Valida DVs
        let tamanho = cnpj.length - 2
        let numeros = cnpj.substring(0,tamanho);
        let digitos = cnpj.substring(tamanho);
        let soma = 0;
        let pos = tamanho - 7;
        
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        
        let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos.charAt(0)) return false;
        
        tamanho = tamanho + 1;
        numeros = cnpj.substring(0,tamanho);
        soma = 0;
        pos = tamanho - 7;
        
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        
        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos.charAt(1)) return false;
        
        return true;
    }
    
    console.log('✅ Formulário inicializado com sucesso!');
});
</script>

@endsection