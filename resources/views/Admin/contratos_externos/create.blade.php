@extends('layouts.app')
@section('page-title', 'Cadastro de Novo Contrato')
@section('page-subtitle', 'Preencha os dados do contrato, vigência e da empresa contratada')

@section('content')

<div class="overflow-hidden bg-white shadow-sm rounded-xl">

    <form class="px-6 py-6" action="{{ route('admin.contratos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- SEÇÃO 1: DADOS DO CONTRATO --}}
        <div class="pb-6 border-b border-gray-100">
            <h3 class="flex items-center gap-2 text-lg font-medium text-gray-700">
                <i class="fas fa-file-signature text-[#009496]"></i> Dados do Contrato
            </h3>
            
            {{-- PREFEITURA --}}
            <div>
                <label for="prefeitura_id" class="block text-sm font-medium text-gray-700">Prefeitura</label>
                <select name="prefeitura_id" id="prefeitura_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                    <option value="">Selecione a prefeitura</option>
                    @foreach ($prefeituras as $prefeitura)
                    <option value="{{ $prefeitura->id }}" {{ old('prefeitura_id') == $prefeitura->id ? 'selected' : '' }}>
                        {{ $prefeitura->nome }}
                    </option>
                    @endforeach
                </select>
                @error('prefeitura_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Linha 1: Identificação --}}
            <div class="grid grid-cols-1 gap-6 mt-4 md:grid-cols-2">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Nº Processo *</label>
                    <input required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                            type="text" name="numero_processo" value="{{ old('numero_processo') }}" placeholder="Ex: 123/2024">
                    @error('numero_processo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Nº Contrato</label>
                    <input class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                            type="text" name="numero_contrato" value="{{ old('numero_contrato') }}" placeholder="Ex: 001/2024">
                    @error('numero_contrato')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Linha 2: Modalidade e Secretaria --}}
            <div class="grid grid-cols-1 gap-6 mt-6 md:grid-cols-2">
                {{-- MODALIDADE --}}
                <div>
                    <label for="modalidade" class="block text-sm font-medium text-gray-700">Modalidade</label>
                    <select name="modalidade" id="modalidade" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                        <option value="">Selecione a modalidade</option>
                        @foreach (\App\Enums\ModalidadeEnum::cases() as $modalidade)
                        <option value="{{ $modalidade->value }}" {{ old('modalidade') == $modalidade->value ? 'selected' : '' }}>
                            {{ $modalidade->getDisplayName() }}
                        </option>
                        @endforeach
                    </select>
                    @error('modalidade')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Secretaria / Órgão Contratante *</label>
                    <select required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                            name="unidade_id" id="unidade_id">
                        <option value="">Primeiro selecione a prefeitura</option>
                        {{-- As opções serão carregadas via JavaScript --}}
                    </select>
                    @error('unidade_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Linha 3: TIPO DE CONTRATO --}}
            <div class="mt-6">
                <label class="block mb-2 text-sm font-medium text-gray-700">Tipo de Contrato *</label>
                <select required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                        name="tipo_contrato">
                    <option value="">Selecione</option>
                    <option value="Compras" {{ old('tipo_contrato') == 'Compras' ? 'selected' : '' }}>Compras</option>
                    <option value="Serviço" {{ old('tipo_contrato') == 'Serviço' ? 'selected' : '' }}>Serviço</option>
                </select>
                @error('tipo_contrato')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Objeto --}}
            <div class="mt-6">
                <label class="block mb-2 text-sm font-medium text-gray-700">Objeto do Contrato *</label>
                <textarea required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                            rows="3" name="objeto" placeholder="Descrição detalhada do objeto...">{{ old('objeto') }}</textarea>
                @error('objeto')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
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
                    <label class="block mb-2 text-sm font-medium text-gray-700">Valor Total (R$) *</label>
                    <input required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors money-mask"
                            type="text" name="valor_total" value="{{ old('valor_total') }}" placeholder="R$ 0,00">
                    @error('valor_total')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Data Início</label>
                    <input class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                            type="date" name="data_inicio" value="{{ old('data_inicio') }}">
                    @error('data_inicio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Data Finalização *</label>
                    <input required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                            type="date" name="data_finalizacao" value="{{ old('data_finalizacao') }}">
                    @error('data_finalizacao')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            
        </div>

        {{-- SEÇÃO 3: EMPRESA --}}
        <div class="pt-6 space-y-6">
            <h3 class="flex items-center gap-2 text-lg font-medium text-gray-700">
                <i class="fas fa-building text-[#009496]"></i> Informações da Empresa
            </h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Razão Social *</label>
                    <input required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                            type="text" name="empresa[razao_social]" value="{{ old('empresa.razao_social') }}" placeholder="Razão Social">
                    @error('empresa.razao_social')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">CNPJ *</label>
                    <input required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                            type="text" name="empresa[cnpj]" value="{{ old('empresa.cnpj') }}" placeholder="00.000.000/0000-00">
                    @error('empresa.cnpj')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Representante</label>
                    <input class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                            type="text" name="empresa[representante]" value="{{ old('empresa.representante') }}" placeholder="Nome do representante">
                    @error('empresa.representante')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Endereço *</label>
                    <input required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                            type="text" name="empresa[endereco]" value="{{ old('empresa.endereco') }}" placeholder="Endereço completo">
                    @error('empresa.endereco')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 mt-6">
            <a href="{{ route('admin.contratos.index') }}"
                class="px-4 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors shadow-sm">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-[#009496] rounded-lg hover:bg-[#244853] transition-colors shadow-sm">
                <i class="fas fa-save"></i>
                Salvar Contrato
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const prefeituraSelect = document.getElementById('prefeitura_id');
        const unidadeSelect = document.getElementById('unidade_id');
        
        // Dados das unidades vindo do PHP
        const unidadesPorPrefeitura = {!! $secretarias->groupBy('prefeitura_id')->map(function($unidades) {
            return $unidades->map(function($unidade) {
                return [
                    'id' => $unidade->id,
                    'nome' => $unidade->nome,
                ];
            });
        })->toJson() !!};

        // Função para carregar unidades baseadas na prefeitura selecionada
        function carregarUnidades() {
            const prefeituraId = prefeituraSelect.value;
            
            // Limpar o select de unidades
            unidadeSelect.innerHTML = '<option value="">Primeiro selecione a prefeitura</option>';

            if (prefeituraId && unidadesPorPrefeitura[prefeituraId]) {
                // Adicionar opções das unidades da prefeitura selecionada
                unidadesPorPrefeitura[prefeituraId].forEach(unidade => {
                    const option = document.createElement('option');
                    option.value = unidade.id;
                    option.textContent = unidade.nome;
                    unidadeSelect.appendChild(option);
                });
                
                // Restaurar valor antigo se existir
                const oldValue = "{{ old('unidade_id') }}";
                if (oldValue) {
                    unidadeSelect.value = oldValue;
                }
            }
        }

        // Adicionar evento de change na prefeitura
        prefeituraSelect.addEventListener('change', carregarUnidades);

        // Executar carregamento inicial caso já tenha uma prefeitura selecionada
        if (prefeituraSelect.value) {
            carregarUnidades();
        }

        // Máscara de Moeda
        const moneyInput = document.querySelector('.money-mask');
        if (moneyInput) {
            moneyInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = (value / 100).toFixed(2) + '';
                value = value.replace(".", ",");
                value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
                e.target.value = 'R$ ' + value;
            });
        }

        // Máscara CNPJ
        const cnpjInput = document.querySelector('input[name="empresa[cnpj]"]');
        if (cnpjInput) {
            cnpjInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length <= 14) {
                    value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                    value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                    value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                    value = value.replace(/(\d{4})(\d)/, '$1-$2');
                }
                e.target.value = value;
            });
        }
    });
</script>

@endsection