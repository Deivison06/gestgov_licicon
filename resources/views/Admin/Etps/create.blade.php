@extends('layouts.app')
@section('page-title', 'Solicitar ETP')
@section('page-subtitle', 'Siga os passos para criar um novo Estudo Técnico Preliminar')

@section('content')
<div class="py-8">
    <div class="mb-4">
        <a href="{{ route('admin.etps.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar
        </a>
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
        <form action="{{ route('admin.etps.store') }}" method="POST" enctype="multipart/form-data" id="etpForm">
            @csrf
            
            <!-- PROGRESS BAR STEPS -->
            <div class="w-full mb-8 relative">
                <div class="absolute w-full h-1 bg-gray-200 rounded-full top-5"></div>
                <div id="progress-bar" class="absolute w-1/3 h-1 bg-[#009496] rounded-full top-5 transition-all duration-300"></div>
                <div class="flex justify-between mx-auto items-center relative z-10">
                    <!-- Step 1 Drop -->
                    <div class="text-center w-1/3">
                        <div class="w-10 h-10 mx-auto bg-[#009496] rounded-full text-white flex items-center justify-center font-bold border-4 border-white shadow-sm" id="indicator-1">1</div>
                        <p class="mt-2 text-sm font-medium text-[#009496]" id="label-1">Dados Iniciais</p>
                    </div>
                    <!-- Step 2 Drop -->
                    <div class="text-center w-1/3">
                        <div class="w-10 h-10 mx-auto bg-gray-200 rounded-full text-gray-500 flex items-center justify-center font-bold border-4 border-white shadow-sm transition-colors duration-300" id="indicator-2">2</div>
                        <p class="mt-2 text-sm font-medium text-gray-400 transition-colors duration-300" id="label-2">Objeto</p>
                    </div>
                    <!-- Step 3 Drop -->
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
                        <select name="secretaria_id" id="secretaria_id" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent" required>
                            <option value="">Selecione a Secretaria...</option>
                            @foreach($secretarias as $sec)
                                <option value="{{ $sec->id }}" {{ old('secretaria_id') == $sec->id ? 'selected' : '' }}>{{ $sec->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Responsável pela Solicitação *</label>
                        <select name="responsavel_id" id="responsavel_id" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent" required>
                            <option value="">Selecione o Responsável...</option>
                            @foreach($responsaveis as $resp)
                                <option value="{{ $resp->id }}" {{ old('responsavel_id') == $resp->id ? 'selected' : '' }}>{{ $resp->name }}</option>
                            @endforeach
                        </select>
                    </div>
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
                    <textarea name="objeto_licitacao" id="objeto_licitacao" rows="5" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent resize-y" required>{{ old('objeto_licitacao') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Descreva detalhadamente o que será adquirido ou contratado.</p>
                </div>
                <div class="mt-8 flex justify-between">
                    <button type="button" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all" onclick="prevStep(1)"><i class="fas fa-arrow-left mr-2"></i> Voltar</button>
                    <button type="button" class="px-6 py-2.5 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all" onclick="nextStep(3)">Próximo Passo <i class="fas fa-arrow-right ml-2"></i></button>
                </div>
            </div>

            <!-- PASSO 3 -->
            <div id="step-3" class="step-content hidden">
                <h4 class="text-lg font-semibold text-gray-800 mb-6 border-b pb-2">Passo 3: Tipo de Contratação</h4>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de contratação *</label>
                    <div class="flex items-center space-x-6">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" class="form-radio text-[#009496] focus:ring-[#009496] w-5 h-5 border-gray-300" name="tipo_contratacao" value="item" {{ old('tipo_contratacao') == 'item' ? 'checked' : '' }} onchange="toggleLoteFields()" required>
                            <span class="ml-2 text-gray-700">Por Item</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer border-l pl-6 border-gray-200">
                            <input type="radio" class="form-radio text-[#009496] focus:ring-[#009496] w-5 h-5 border-gray-300" name="tipo_contratacao" value="lote" {{ old('tipo_contratacao') == 'lote' ? 'checked' : '' }} onchange="toggleLoteFields()">
                            <span class="ml-2 text-gray-700">Por Lote</span>
                        </label>
                    </div>
                </div>

                <div id="campos-lote" class="{{ old('tipo_contratacao') == 'lote' ? 'block' : 'hidden' }} p-6 bg-gray-50 border border-gray-200 rounded-xl mb-6">
                    <h5 class="font-medium text-gray-800 mb-4"><i class="fas fa-boxes mr-2 text-[#009496]"></i> Detalhes do Lote</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                         <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nome do lote *</label>
                            <input type="text" name="nome_lote" id="nome_lote" value="{{ old('nome_lote') }}" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Prazo de entrega *</label>
                            <input type="text" name="prazo_entrega" id="prazo_entrega" value="{{ old('prazo_entrega') }}" placeholder="Ex: 30 dias após emissão da nota" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Selecione os Produtos (Itens) *</label>
                        <select name="itens_ids[]" id="itens_ids" multiple class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent" style="height: 120px;">
                            @foreach($itens as $item)
                                <option value="{{ $item->id }}" {{ (collect(old('itens_ids'))->contains($item->id)) ? 'selected':'' }}>{{ $item->descricao_item }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Pressione e segure CTRL (ou CMD) para selecionar múltiplos itens.</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload da cotação do fornecedor local</label>
                        <input type="file" name="cotacao_path" id="cotacao_path" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#009496]/10 file:text-[#009496] hover:file:bg-[#009496]/20">
                        <p class="mt-1 text-xs text-gray-500">Formatos aceitos: PDF, Imagens e Documentos Word. Máx: 10MB.</p>
                    </div>
                </div>

                <div class="mt-8 flex justify-between">
                    <button type="button" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all" onclick="prevStep(2)"><i class="fas fa-arrow-left mr-2"></i> Voltar</button>
                    <button type="submit" class="px-8 py-3 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all shadow-md"><i class="fas fa-check-circle mr-2"></i> Concluir ETP</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleLoteFields() {
        const isLote = document.querySelector('input[name="tipo_contratacao"]:checked').value === 'lote';
        const camposLote = document.getElementById('campos-lote');
        const requiredFields = ['nome_lote', 'prazo_entrega'];
        
        if (isLote) {
            camposLote.classList.remove('hidden');
            requiredFields.forEach(id => document.getElementById(id).setAttribute('required', 'required'));
        } else {
            camposLote.classList.add('hidden');
            requiredFields.forEach(id => {
                document.getElementById(id).removeAttribute('required');
                document.getElementById(id).value = ''; // clear when hidden
            });
            document.getElementById('itens_ids').selectedIndex = -1; // clear selection
        }
    }

    function nextStep(step) {
        if (step === 2) {
            if(!document.getElementById('secretaria_id').value || !document.getElementById('responsavel_id').value) {
                alert('Preencha os campos obrigatórios primeiro.');
                return;
            }
        }
        if (step === 3) {
            if(!document.getElementById('objeto_licitacao').value.trim()) {
                alert('Preencha o objeto detalhadamente primeiro.');
                return;
            }
        }

        document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('step-' + step).classList.remove('hidden');
        updateProgress(step);
    }

    function prevStep(step) {
        document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('step-' + step).classList.remove('hidden');
        updateProgress(step);
    }

    function updateProgress(step) {
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
    }
    
    document.addEventListener("DOMContentLoaded", function() {
        if(document.querySelector('input[name="tipo_contratacao"]:checked')) {
            toggleLoteFields();
        }
    });
</script>
@endsection
