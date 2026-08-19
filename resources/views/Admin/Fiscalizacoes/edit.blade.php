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

    <form class="px-6 py-6" action="{{ route('admin.fiscalizacoes.update', $fiscalizacao->id) }}" method="POST" id="formFiscalizacao" enctype="multipart/form-data">
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

            {{-- Relatório Fotográfico --}}
            <div class="mt-6">
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    <i class="mr-1 fas fa-camera text-[#009496]"></i>
                    {{ $fiscalizacao->relatorio_fotografico ? 'Substituir Relatório Fotográfico' : 'Anexar Relatório Fotográfico' }}
                </label>

                @if($fiscalizacao->relatorio_fotografico)
                    <div class="mb-3">
                        <a href="{{ asset($fiscalizacao->relatorio_fotografico) }}" target="_blank">
                            <img src="{{ asset($fiscalizacao->relatorio_fotografico) }}" alt="Relatório Fotográfico"
                                 class="h-32 rounded-lg border border-gray-200 object-cover hover:opacity-90 transition-opacity">
                        </a>
                    </div>
                @endif

                <input type="file" name="relatorio_fotografico" id="relatorio_fotografico" accept="image/*"
                       class="block w-full px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#009496] file:text-white hover:file:bg-[#244853] transition-colors cursor-pointer focus:ring-2 focus:ring-[#009496] focus:border-[#009496]">
                <p class="mt-2 text-xs text-gray-500">
                    <i class="mr-1 fas fa-info-circle"></i> Formatos aceitos: JPEG, PNG ou WEBP, tamanho máximo: 5MB.
                    @if($fiscalizacao->relatorio_fotografico) Deixe em branco para manter a imagem atual. @endif
                </p>
                @error('relatorio_fotografico')
                    <p class="mt-1 text-sm text-red-600"><i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- SEÇÃO 3: CAMPOS DE AVALIAÇÃO (Dinâmicos por tipo)           --}}
        {{-- ============================================================ --}}
        <div class="py-6 border-b border-gray-100" id="secao_avaliacao">
            <h3 class="flex items-center gap-2 text-lg font-medium text-gray-700">
                <i class="fas fa-tasks text-[#009496]"></i> Avaliação da Execução
            </h3>

            {{-- Checklist de Verificação (Compras e Serviços) --}}
            <div class="p-4 mt-4 border border-gray-200 rounded-lg campo-variavel bg-gray-50" id="secao_checklist">
                <p class="mb-3 text-sm font-medium text-gray-700">Checklist de Verificação</p>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach(\App\Models\Fiscalizacao::CHECKLIST_ITENS as $chave => $rotulo)
                        <label class="flex items-start gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="checklist_fiscalizacao[{{ $chave }}]" value="1"
                                   class="mt-0.5 rounded text-[#009496] focus:ring-[#009496]"
                                   {{ old('checklist_fiscalizacao.'.$chave, data_get($fiscalizacao->checklist_fiscalizacao, $chave)) ? 'checked' : '' }}>
                            {{ $rotulo }}
                        </label>
                    @endforeach
                </div>
            </div>

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

            {{-- Ocorrências --}}
            <div class="mt-4">
                <label class="block mb-2 text-sm font-medium text-gray-700" id="label_ocorrencias">Durante a vigência do contrato foi observado alguma irregularidade?</label>

                {{-- Toggle Houve/Não houve ocorrência (Compras e Serviços) --}}
                <div class="flex flex-wrap gap-4 mb-3 campo-variavel campo-oculto" id="campo_toggle_ocorrencia">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="houve_ocorrencia" value="0" id="houve_ocorrencia_nao"
                               class="text-[#009496] focus:ring-[#009496]" disabled
                               {{ old('houve_ocorrencia', $fiscalizacao->houve_ocorrencia) === false ? 'checked' : '' }}>
                        Não houve ocorrências
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="houve_ocorrencia" value="1" id="houve_ocorrencia_sim"
                               class="text-[#009496] focus:ring-[#009496]" disabled
                               {{ old('houve_ocorrencia', $fiscalizacao->houve_ocorrencia) === true ? 'checked' : '' }}>
                        Houve ocorrências, conforme descrição abaixo
                    </label>
                </div>

                <div id="campo_detalhes_ocorrencia">
                    <textarea name="irregularidade_observada" id="irregularidade_observada" rows="4"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">{{ old('irregularidade_observada', $fiscalizacao->irregularidade_observada) }}</textarea>

                    {{-- Providências adotadas (Compras e Serviços) --}}
                    <div class="mt-3 campo-variavel campo-oculto" id="campo_providencias_adotadas">
                        <label class="block mb-2 text-sm font-medium text-gray-700">Providências adotadas</label>
                        <textarea name="providencias_adotadas" rows="3"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">{{ old('providencias_adotadas', $fiscalizacao->providencias_adotadas) }}</textarea>
                    </div>
                </div>
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

                {{-- Toggle Sim/Não (Compras e Serviços) --}}
                <div class="flex max-w-xs gap-3 campo-variavel campo-oculto" id="campo_regularidade_simnao">
                    <label class="flex-1">
                        <input type="radio" name="regularidade_fiscal_trabalhista" value="Sim" id="regularidade_sim"
                               class="sr-only peer" disabled
                               {{ old('regularidade_fiscal_trabalhista', $fiscalizacao->regularidade_fiscal_trabalhista) === 'Sim' ? 'checked' : '' }}>
                        <span class="block p-2.5 font-medium text-center text-gray-600 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700">Sim</span>
                    </label>
                    <label class="flex-1">
                        <input type="radio" name="regularidade_fiscal_trabalhista" value="Não" id="regularidade_nao"
                               class="sr-only peer" disabled
                               {{ old('regularidade_fiscal_trabalhista', $fiscalizacao->regularidade_fiscal_trabalhista) === 'Não' ? 'checked' : '' }}>
                        <span class="block p-2.5 font-medium text-center text-gray-600 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700">Não</span>
                    </label>
                </div>

                {{-- Texto livre (Obras) --}}
                <textarea name="regularidade_fiscal_trabalhista" id="regularidade_textarea" rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">{{ old('regularidade_fiscal_trabalhista', $fiscalizacao->regularidade_fiscal_trabalhista) }}</textarea>
            </div>

            <div class="mt-4 campo-variavel campo-oculto" id="campo_comunicacao">
                <label class="block mb-2 text-sm font-medium text-gray-700">Comunicação e Atendimento</label>
                <textarea name="comunicacao_atendimento" id="comunicacao_atendimento" rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">{{ old('comunicacao_atendimento', $fiscalizacao->comunicacao_atendimento) }}</textarea>
            </div>

            <div class="mt-4 campo-variavel">
                <label class="block mb-2 text-sm font-medium text-gray-700" id="label_observacoes_servidor">Observações</label>
                <textarea name="observacoes_servidor" rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">{{ old('observacoes_servidor', $fiscalizacao->observacoes_servidor) }}</textarea>
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
            execucao_objeto: 'Execução no Período',
            qualidade_entregas: 'Qualidade dos Produtos entregues',
            observacoes_servidor: 'Observações indicadas por servidor próximo a execução',
            label_ocorrencias: 'Ocorrências',
            mostrar_metodologia: false,
            mostrar_comunicacao: false,
            mostrar_checklist: true,
            ocorrencia_estruturada: true
        },
        servicos: {
            execucao_objeto: 'Execução no Período',
            qualidade_entregas: 'Qualidade dos Serviços realizados',
            observacoes_servidor: 'Observações indicadas por servidor próximo a execução',
            label_ocorrencias: 'Ocorrências',
            mostrar_metodologia: false,
            mostrar_comunicacao: false,
            mostrar_checklist: true,
            ocorrencia_estruturada: true
        },
        obras: {
            execucao_objeto: 'Execução do Objeto',
            qualidade_entregas: 'Qualidade dos serviços Executados',
            observacoes_servidor: 'Observações indicadas por servidor Fiscal de Engenharia',
            label_ocorrencias: 'Durante a vigência do contrato foi observado alguma irregularidade?',
            mostrar_metodologia: true,
            mostrar_comunicacao: true,
            mostrar_checklist: false,
            ocorrencia_estruturada: false
        }
    };

    const tipoSelect = document.getElementById('tipo_contrato');
    const campoMetodologia = document.getElementById('campo_metodologia');
    const secaoChecklist = document.getElementById('secao_checklist');
    const campoComunicacao = document.getElementById('campo_comunicacao');
    const campoToggleOcorrencia = document.getElementById('campo_toggle_ocorrencia');
    const campoDetalhesOcorrencia = document.getElementById('campo_detalhes_ocorrencia');
    const campoProvidenciasAdotadas = document.getElementById('campo_providencias_adotadas');
    const campoRegularidadeSimNao = document.getElementById('campo_regularidade_simnao');
    const regularidadeTextarea = document.getElementById('regularidade_textarea');

    let ocorrenciaEstruturadaAtiva = false;

    function atualizarDetalhesOcorrencia() {
        if (!ocorrenciaEstruturadaAtiva) {
            campoDetalhesOcorrencia.style.display = 'block';
            campoProvidenciasAdotadas.classList.add('campo-oculto');
            return;
        }

        var marcado = document.querySelector('input[name="houve_ocorrencia"]:checked');
        var houve = !!marcado && marcado.value === '1';
        campoDetalhesOcorrencia.style.display = houve ? 'block' : 'none';
        campoProvidenciasAdotadas.classList.toggle('campo-oculto', !houve);
    }

    document.querySelectorAll('input[name="houve_ocorrencia"]').forEach(function (radio) {
        radio.addEventListener('change', atualizarDetalhesOcorrencia);
    });

    function atualizarFormulario(tipo) {
        if (!tipo || !labelsMap[tipo]) return;

        var config = labelsMap[tipo];

        document.getElementById('label_execucao_objeto').textContent = config.execucao_objeto;
        document.getElementById('label_qualidade_entregas').textContent = config.qualidade_entregas;
        document.getElementById('label_observacoes_servidor').textContent = config.observacoes_servidor;
        document.getElementById('label_ocorrencias').textContent = config.label_ocorrencias;

        campoMetodologia.classList.toggle('campo-oculto', !config.mostrar_metodologia);

        secaoChecklist.classList.toggle('campo-oculto', !config.mostrar_checklist);

        campoComunicacao.classList.toggle('campo-oculto', !config.mostrar_comunicacao);
        document.getElementById('comunicacao_atendimento').disabled = !config.mostrar_comunicacao;

        ocorrenciaEstruturadaAtiva = !!config.ocorrencia_estruturada;
        campoToggleOcorrencia.classList.toggle('campo-oculto', !ocorrenciaEstruturadaAtiva);
        document.querySelectorAll('input[name="houve_ocorrencia"]').forEach(function (radio) {
            radio.disabled = !ocorrenciaEstruturadaAtiva;
        });
        atualizarDetalhesOcorrencia();

        var estruturaSimNao = !!config.ocorrencia_estruturada;
        campoRegularidadeSimNao.classList.toggle('campo-oculto', !estruturaSimNao);
        document.querySelectorAll('input[name="regularidade_fiscal_trabalhista"][type="radio"]').forEach(function (radio) {
            radio.disabled = !estruturaSimNao;
        });
        regularidadeTextarea.classList.toggle('campo-oculto', estruturaSimNao);
        regularidadeTextarea.disabled = estruturaSimNao;
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
