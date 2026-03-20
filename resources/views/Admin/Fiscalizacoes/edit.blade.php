@extends('layouts.app')
@section('page-title', 'Editar Fiscalização')
@section('page-subtitle', 'Fiscalização Nº ' . $fiscalizacao->numero_fiscalizacao)

@section('content')

{{-- Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .campo-variavel { transition: all 0.3s ease; }
    .campo-oculto { display: none !important; }
    .readonly-field {
        background-color: #f9fafb;
        border-color: #e5e7eb;
        color: #6b7280;
        cursor: default;
    }
    .radio-conclusao:checked + label {
        border-color: #009496;
        background-color: #f0fdfa;
        box-shadow: 0 0 0 2px rgba(0, 148, 150, 0.2);
    }
</style>

@php
    $info = $fiscalizacao->contrato_info;
@endphp

<div class="overflow-hidden bg-white shadow-sm rounded-xl">

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="p-4 mb-4 bg-red-50 border border-red-200 rounded-lg mx-6 mt-6">
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

    <form class="px-6 py-6" action="{{ route('admin.fiscalizacoes.update', $fiscalizacao->id) }}" method="POST" id="formFiscalizacao">
        @csrf
        @method('PUT')

        {{-- Hidden fields para o polimorfismo (não alterável na edição) --}}
        <input type="hidden" name="fiscalizavel_id" value="{{ $fiscalizacao->fiscalizavel_id }}">
        <input type="hidden" name="fiscalizavel_type" value="{{ $fiscalizacao->fiscalizavel_type }}">

        {{-- ============================================================ --}}
        {{-- SEÇÃO 1: DADOS DO CONTRATO (READONLY)                       --}}
        {{-- ============================================================ --}}
        <div class="pb-6 border-b border-gray-100">
            <h3 class="flex items-center gap-2 text-lg font-medium text-gray-700">
                <i class="fas fa-file-contract text-[#009496]"></i> Contrato Vinculado
                <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                    {{ $info['origem'] }}
                </span>
            </h3>

            <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Secretaria</label>
                    <input type="text" value="{{ $info['secretaria'] }}" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Nº Contrato</label>
                    <input type="text" value="{{ $info['numero_contrato'] }}" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Nº Processo</label>
                    <input type="text" value="{{ $info['numero_processo'] }}" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" readonly>
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-500">Objeto</label>
                    <textarea class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" rows="2" readonly>{{ $info['objeto'] }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Razão Social</label>
                    <input type="text" value="{{ $info['razao_social'] }}" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">CNPJ</label>
                    <input type="text" value="{{ $info['cnpj'] }}" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" readonly>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- SEÇÃO 2: DADOS DA FISCALIZAÇÃO                              --}}
        {{-- ============================================================ --}}
        <div class="py-6 border-b border-gray-100">
            <h3 class="flex items-center gap-2 text-lg font-medium text-gray-700">
                <i class="fas fa-clipboard-check text-[#009496]"></i> Dados da Fiscalização
            </h3>

            <div class="grid grid-cols-1 gap-6 mt-4 md:grid-cols-3">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        <span class="text-red-500">*</span> Tipo de Contrato
                    </label>
                    <select name="tipo_contrato" id="tipo_contrato" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">
                        <option value="">Selecione o tipo</option>
                        @foreach($tiposFiscalizacao as $tipo)
                            <option value="{{ $tipo->value }}"
                                {{ old('tipo_contrato', $fiscalizacao->tipo_contrato?->value) == $tipo->value ? 'selected' : '' }}>
                                {{ $tipo->getDisplayName() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        <span class="text-red-500">*</span> Data da Fiscalização
                    </label>
                    <input type="date" name="data_fiscalizacao" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                           value="{{ old('data_fiscalizacao', $fiscalizacao->data_fiscalizacao?->format('Y-m-d')) }}">
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        <span class="text-red-500">*</span> Número da Fiscalização
                    </label>
                    <input type="text" name="numero_fiscalizacao" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                           value="{{ old('numero_fiscalizacao', $fiscalizacao->numero_fiscalizacao) }}">
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- SEÇÃO 3: CAMPOS DE AVALIAÇÃO (Dinâmicos por tipo)           --}}
        {{-- ============================================================ --}}
        <div class="py-6 border-b border-gray-100" id="secao_avaliacao">
            <h3 class="flex items-center gap-2 text-lg font-medium text-gray-700">
                <i class="fas fa-tasks text-[#009496]"></i> Avaliação da Execução
            </h3>

            <div class="mt-4 campo-variavel" id="campo_metodologia">
                <label class="block mb-2 text-sm font-medium text-gray-700">Metodologia Aplicada na Fiscalização</label>
                <textarea name="metodologia_fiscalizacao" rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">{{ old('metodologia_fiscalizacao', $fiscalizacao->metodologia_fiscalizacao) }}</textarea>
            </div>

            <div class="mt-4 campo-variavel">
                <label class="block mb-2 text-sm font-medium text-gray-700" id="label_execucao_objeto">Execução do Objeto</label>
                <textarea name="execucao_objeto" rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">{{ old('execucao_objeto', $fiscalizacao->execucao_objeto) }}</textarea>
            </div>

            <div class="mt-4 campo-variavel">
                <label class="block mb-2 text-sm font-medium text-gray-700" id="label_qualidade_entregas">Qualidade das Entregas</label>
                <textarea name="qualidade_entregas" rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">{{ old('qualidade_entregas', $fiscalizacao->qualidade_entregas) }}</textarea>
            </div>

            <div class="mt-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">Pontualidade / Cumprimento dos Prazos</label>
                <textarea name="pontualidade_prazos" rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">{{ old('pontualidade_prazos', $fiscalizacao->pontualidade_prazos) }}</textarea>
            </div>

            <div class="mt-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">A Empresa tem apresentado comprovação de Regularidade Fiscal e Trabalhista?</label>
                <textarea name="regularidade_fiscal_trabalhista" rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">{{ old('regularidade_fiscal_trabalhista', $fiscalizacao->regularidade_fiscal_trabalhista) }}</textarea>
            </div>

            <div class="mt-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">Comunicação e Atendimento</label>
                <textarea name="comunicacao_atendimento" rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">{{ old('comunicacao_atendimento', $fiscalizacao->comunicacao_atendimento) }}</textarea>
            </div>

            <div class="mt-4 campo-variavel">
                <label class="block mb-2 text-sm font-medium text-gray-700" id="label_observacoes_servidor">Observações</label>
                <textarea name="observacoes_servidor" rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">{{ old('observacoes_servidor', $fiscalizacao->observacoes_servidor) }}</textarea>
            </div>

            <div class="mt-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">Durante a vigência do contrato foi observado alguma irregularidade?</label>
                <textarea name="irregularidade_observada" rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">{{ old('irregularidade_observada', $fiscalizacao->irregularidade_observada) }}</textarea>
            </div>

            <div class="mt-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">Recomendações a serem adotadas pelo Gestor</label>
                <textarea name="recomendacoes_gestor" rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">{{ old('recomendacoes_gestor', $fiscalizacao->recomendacoes_gestor) }}</textarea>
            </div>

            <div class="mt-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">Recomendações a serem adotadas pela Empresa</label>
                <textarea name="recomendacoes_empresa" rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">{{ old('recomendacoes_empresa', $fiscalizacao->recomendacoes_empresa) }}</textarea>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- SEÇÃO 4: CONCLUSÃO DO FISCAL                                --}}
        {{-- ============================================================ --}}
        <div class="py-6 border-b border-gray-100" id="secao_conclusao">
            <h3 class="flex items-center gap-2 text-lg font-medium text-gray-700">
                <i class="fas fa-gavel text-[#009496]"></i> Conclusão do Fiscal
            </h3>
            <p class="mt-1 text-sm text-gray-500">Selecione a conclusão que melhor descreve a situação do contrato</p>

            <div class="mt-4 space-y-3">
                @foreach($conclusoes as $conclusao)
                    <div class="flex items-start">
                        <input type="radio" name="conclusao_fiscal" id="conclusao_{{ $conclusao->value }}"
                               value="{{ $conclusao->value }}"
                               class="radio-conclusao sr-only"
                               {{ old('conclusao_fiscal', $fiscalizacao->conclusao_fiscal?->value) == $conclusao->value ? 'checked' : '' }}>
                        <label for="conclusao_{{ $conclusao->value }}"
                               class="flex-1 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-[#009496] hover:bg-gray-50 transition-all">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold
                                    @if($conclusao->value === 1) bg-green-100 text-green-700
                                    @elseif($conclusao->value === 2) bg-yellow-100 text-yellow-700
                                    @else bg-red-100 text-red-700
                                    @endif">
                                    {{ $conclusao->value }}
                                </span>
                                <span class="text-sm text-gray-700 leading-relaxed">{{ $conclusao->getTextoCompleto() }}</span>
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>

            @error('conclusao_fiscal')
                <p class="mt-2 text-sm text-red-600"><i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}</p>
            @enderror
        </div>

        {{-- ============================================================ --}}
        {{-- BOTÕES DE AÇÃO                                              --}}
        {{-- ============================================================ --}}
        <div class="flex justify-end gap-3 pt-6">
            <a href="{{ route('admin.fiscalizacoes.index') }}"
               class="flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors bg-gray-100 rounded-lg hover:bg-gray-200 shadow-sm">
                <i class="mr-2 fas fa-times"></i>
                Cancelar
            </a>
            <button type="submit"
                    class="flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-white transition-colors bg-[#009496] rounded-lg hover:bg-[#244853] shadow-sm">
                <i class="mr-2 fas fa-save"></i>
                Salvar Alterações
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('🔧 Inicializando formulário de edição de fiscalização...');

    const labelsMap = {
        compras: {
            execucao_objeto: 'Execução Física do Objeto',
            qualidade_entregas: 'Qualidade dos Produtos entregues',
            observacoes_servidor: 'Observações indicadas por servidor próximo a execução',
            mostrar_metodologia: false
        },
        servicos: {
            execucao_objeto: 'Execução do Objeto',
            qualidade_entregas: 'Qualidade dos serviços Prestados',
            observacoes_servidor: 'Observações indicadas por servidor próximo a execução',
            mostrar_metodologia: true
        },
        obras: {
            execucao_objeto: 'Execução do Objeto',
            qualidade_entregas: 'Qualidade dos serviços Executados',
            observacoes_servidor: 'Observações indicadas por servidor Fiscal de Engenharia',
            mostrar_metodologia: true
        }
    };

    const tipoSelect = document.getElementById('tipo_contrato');
    const campoMetodologia = document.getElementById('campo_metodologia');

    function atualizarFormulario(tipo) {
        if (!tipo || !labelsMap[tipo]) return;

        var config = labelsMap[tipo];

        document.getElementById('label_execucao_objeto').textContent = config.execucao_objeto;
        document.getElementById('label_qualidade_entregas').textContent = config.qualidade_entregas;
        document.getElementById('label_observacoes_servidor').textContent = config.observacoes_servidor;

        if (config.mostrar_metodologia) {
            campoMetodologia.classList.remove('campo-oculto');
        } else {
            campoMetodologia.classList.add('campo-oculto');
        }
    }

    tipoSelect.addEventListener('change', function () {
        atualizarFormulario(this.value);
    });

    // Inicializar com valor atual
    if (tipoSelect.value) {
        atualizarFormulario(tipoSelect.value);
    }

    console.log('✅ Formulário de edição inicializado!');
});
</script>

@endsection
