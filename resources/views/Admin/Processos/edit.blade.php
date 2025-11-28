@extends('layouts.app')
@section('page-title', 'Gestão de Processos')
@section('page-subtitle', 'Atualize os dados do processo')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
<div class="py-6">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm rounded-xl">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-700">Informações do Processo</h3>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.processos.update', $processo->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        {{-- PREFEITURA --}}
                        <div>
                            <label for="prefeitura_id" class="block text-sm font-medium text-gray-700">Prefeitura</label>
                            <select name="prefeitura_id" id="prefeitura_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                <option value="">Selecione a prefeitura</option>
                                @foreach ($prefeituras as $prefeitura)
                                <option value="{{ $prefeitura->id }}" {{ old('prefeitura_id', $processo->prefeitura_id) == $prefeitura->id ? 'selected' : '' }}>
                                    {{ $prefeitura->nome }}
                                </option>
                                @endforeach
                            </select>
                            @error('prefeitura_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- MODALIDADE --}}
                        <div>
                            <label for="modalidade" class="block text-sm font-medium text-gray-700">Modalidade</label>
                            <select name="modalidade" id="modalidade" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                <option value="">Selecione a modalidade</option>
                                @foreach (\App\Enums\ModalidadeEnum::cases() as $modalidade)
                                <option value="{{ $modalidade->value }}" {{ old('modalidade', $processo->modalidade?->value ?? $processo->modalidade) == $modalidade->value ? 'selected' : '' }}>
                                    {{ $modalidade->getDisplayName() }}
                                </option>
                                @endforeach
                            </select>
                            @error('modalidade')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nº PROCESSO --}}
                        <div>
                            <label for="numero_processo" class="block text-sm font-medium text-gray-700">Nº do Processo</label>
                            <input type="text" name="numero_processo" id="numero_processo" value="{{ old('numero_processo', $processo->numero_processo) }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                            @error('numero_processo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nº PROCEDIMENTO --}}
                        <div>
                            <label for="numero_procedimento" class="block text-sm font-medium text-gray-700">Nº do Procedimento</label>
                            <input type="text" name="numero_procedimento" id="numero_procedimento" value="{{ old('numero_procedimento', $processo->numero_procedimento) }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                            @error('numero_procedimento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- TIPO DE PROCEDIMENTO --}}
                        <div id="tipo_procedimento_wrapper">
                            <label for="tipo_procedimento" class="block mb-1 text-sm font-medium text-gray-700">
                                Tipo de Procedimento
                            </label>
                            <select name="tipo_procedimento" id="tipo_procedimento" class="block w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-[#009496] focus:border-[#009496] sm:text-sm">
                                <option value="">Selecione o tipo de procedimento</option>
                                @foreach (\App\Enums\TipoProcedimentoEnum::cases() as $enum)
                                <option value="{{ $enum->value }}" {{ old('tipo_procedimento', $processo->tipo_procedimento?->value ?? '') == $enum->value ? 'selected' : '' }}>
                                    {{ $enum->getDisplayName() }}
                                </option>
                                @endforeach
                            </select>
                            @error('tipo_procedimento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- TIPO DE CONTRATAÇÃO --}}
                        <div id="tipo_contratacao_wrapper">
                            <label for="tipo_contratacao" class="block mb-1 text-sm font-medium text-gray-700">
                                Tipo de Contratação
                            </label>
                            <select name="tipo_contratacao" id="tipo_contratacao" class="block w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-[#009496] focus:border-[#009496] sm:text-sm">
                                <option value="">Selecione o tipo de contratação</option>
                                @foreach (\App\Enums\TipoContratacaoEnum::cases() as $enum)
                                <option value="{{ $enum->value }}" {{ old('tipo_contratacao', $processo->tipo_contratacao?->value ?? '') == $enum->value ? 'selected' : '' }}>
                                    {{ $enum->getDisplayName() }}
                                </option>
                                @endforeach
                            </select>
                            @error('tipo_contratacao')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- RESPONSÁVEL POR NUMERAR O PROCESSO --}}
                        <div class="md:col-span-2">
                            <h4 class="mb-4 text-sm font-semibold text-gray-700">
                                Responsável por Numerar o Processo
                            </h4>

                            <div class="flex flex-col gap-4 md:flex-row">
                                {{-- Unidade --}}
                                <div class="w-full md:w-1/3">
                                    <select name="unidade_numeracao" id="unidade_numeracao" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                        <option value="">Selecione a unidade</option>
                                        @foreach ($prefeituras as $prefeitura)
                                        @foreach ($prefeitura->unidades as $unidade)
                                        <option value="{{ $unidade->nome }}" data-responsavel="{{ $unidade->servidor_responsavel }}" data-portaria="{{ $unidade->numero_portaria }}" {{ old('unidade_numeracao', $processo->unidade_numeracao) == $unidade->nome ? 'selected' : '' }}>
                                            {{ $unidade->nome }}
                                        </option>
                                        @endforeach
                                        @endforeach
                                    </select>
                                    @error('unidade_numeracao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Responsável --}}
                                <div class="w-full md:w-1/3">
                                    <input type="text" name="responsavel_numeracao" id="responsavel_numeracao" value="{{ old('responsavel_numeracao', $processo->responsavel_numeracao) }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496] bg-gray-50">
                                    @error('responsavel_numeracao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Nº Portaria --}}
                                <div class="w-full md:w-1/3">
                                    <input type="text" name="portaria_numeracao" id="portaria_numeracao" value="{{ old('portaria_numeracao', $processo->portaria_numeracao) }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496] bg-gray-50">
                                    @error('portaria_numeracao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- OBJETO --}}
                        <div class="md:col-span-2">
                            <textarea name="objeto" id="objeto" rows="4" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">{{ old('objeto', $processo->objeto) }}</textarea>
                            @error('objeto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- BOTÕES --}}
                    <div class="flex justify-end mt-6 space-x-4">
                        <a href="{{ route('admin.processos.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Cancelar
                        </a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-[#062F43] rounded-md hover:bg-[#244853]">
                            Atualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const unidadesPorPrefeitura = {!! $prefeituras->mapWithKeys(function($prefeitura) {
            return [
                $prefeitura->id => $prefeitura->unidades->map(function($unidade) {
                    return [
                        'id' => $unidade->id,
                        'nome' => $unidade->nome,
                        'responsavel' => $unidade->servidor_responsavel,
                        'portaria' => $unidade->numero_portaria,
                    ];
                })
            ];
        })->toJson() !!};

        const prefeituraSelect = document.getElementById('prefeitura_id');
        const unidadeSelect = document.getElementById('unidade_numeracao');
        const responsavelInput = document.getElementById('responsavel_numeracao');
        const portariaInput = document.getElementById('portaria_numeracao');

        function carregarUnidades() {
            const prefeituraId = prefeituraSelect.value;

            unidadeSelect.innerHTML = '<option value="">Selecione a unidade</option>';
            responsavelInput.value = '';
            portariaInput.value = '';

            if (prefeituraId && unidadesPorPrefeitura[prefeituraId]) {
                unidadesPorPrefeitura[prefeituraId].forEach(unidade => {
                    const option = document.createElement('option');
                    option.value = unidade.nome;
                    option.textContent = unidade.nome;
                    option.dataset.responsavel = unidade.responsavel;
                    option.dataset.portaria = unidade.portaria;
                    unidadeSelect.appendChild(option);
                });
            }
        }

        prefeituraSelect.addEventListener('change', carregarUnidades);

        unidadeSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            responsavelInput.value = opt.dataset.responsavel ?? '';
            portariaInput.value = opt.dataset.portaria ?? '';
        });

        // Carrega automático quando está editando
        if (prefeituraSelect.value) {
            carregarUnidades();

            const unidadeSalva = "{{ old('unidade_numeracao', $processo->unidade_numeracao) }}";

            if (unidadeSalva) {
                setTimeout(() => {
                    unidadeSelect.value = unidadeSalva;
                    unidadeSelect.dispatchEvent(new Event('change'));
                }, 100);
            }
        }
    });
</script>

@endsection
