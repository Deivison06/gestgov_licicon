@extends('layouts.app')
@section('page-title', 'Editar Ocorrência')
@section('page-subtitle', 'Nº ' . $ocorrencia->numero_ocorrencia)

@section('content')

@php
    $bloqueado = $ocorrencia->status->value !== 'rascunho';
@endphp

<style>
    .campo-variavel { transition: all 0.3s ease; }
    .campo-oculto { display: none !important; }
    .readonly-field { background-color: #f9fafb; border: 1px solid #e5e7eb; color: #6b7280; cursor: default; }
</style>

<div class="overflow-hidden bg-white shadow-sm rounded-xl">

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

    @if($bloqueado)
        <div class="mx-6 mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-sm text-yellow-800">
                <i class="mr-1 fas fa-lock"></i>
                Esta ocorrência já foi <strong>registrada</strong> — os dados do fato ficam travados a partir daqui.
                Só a <strong>situação</strong> pode ser ajustada nesta tela. Resposta da empresa e Atesto de Correção
                são tratados na tela de detalhes.
            </p>
        </div>
    @endif

    <form class="px-6 py-6" action="{{ route('admin.ocorrencias.update', $ocorrencia->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="status" id="status_input" value="{{ $bloqueado ? 'registrada' : old('status', 'rascunho') }}">
        <input type="hidden" name="fiscalizavel_id" value="{{ $ocorrencia->fiscalizavel_id }}">
        <input type="hidden" name="fiscalizavel_type" value="{{ $ocorrencia->fiscalizavel_type }}">

        {{-- Dados do Contrato (fixo, não editável) --}}
        <div class="py-6 border-b border-gray-100">
            <h3 class="flex items-center gap-2 text-lg font-medium text-gray-700">
                <i class="fas fa-file-contract text-[#009496]"></i> Dados do Contrato
            </h3>
            <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Nº Contrato</label>
                    <input type="text" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" readonly
                           value="{{ $ocorrencia->contrato_info['numero_contrato'] }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Razão Social</label>
                    <input type="text" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" readonly
                           value="{{ $ocorrencia->contrato_info['razao_social'] }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">CNPJ</label>
                    <input type="text" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" readonly
                           value="{{ $ocorrencia->contrato_info['cnpj'] }}">
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-500">Objeto</label>
                    <textarea class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" rows="2" readonly>{{ $ocorrencia->contrato_info['objeto'] }}</textarea>
                </div>
            </div>
        </div>

        {{-- Dados da Ocorrência --}}
        <div class="py-6 border-b border-gray-100">
            <h3 class="flex items-center gap-2 text-lg font-medium text-gray-700">
                <i class="fas fa-triangle-exclamation text-[#009496]"></i> Dados da Ocorrência
            </h3>

            <div class="grid grid-cols-1 gap-6 mt-4 md:grid-cols-3">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        <span class="text-red-500">*</span> Número da Ocorrência
                    </label>
                    <input type="text" name="numero_ocorrencia" required {{ $bloqueado ? 'readonly' : '' }}
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors {{ $bloqueado ? 'readonly-field' : '' }}"
                           value="{{ old('numero_ocorrencia', $ocorrencia->numero_ocorrencia) }}">
                    @error('numero_ocorrencia')
                        <p class="mt-1 text-sm text-red-600"><i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        <span class="text-red-500">*</span> Data da Ocorrência
                    </label>
                    <input type="date" name="data_ocorrencia" required {{ $bloqueado ? 'readonly' : '' }}
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors {{ $bloqueado ? 'readonly-field' : '' }}"
                           value="{{ old('data_ocorrencia', optional($ocorrencia->data_ocorrencia)->format('Y-m-d')) }}">
                    @error('data_ocorrencia')
                        <p class="mt-1 text-sm text-red-600"><i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Local</label>
                    <input type="text" name="local" {{ $bloqueado ? 'readonly' : '' }}
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors {{ $bloqueado ? 'readonly-field' : '' }}"
                           value="{{ old('local', $ocorrencia->local) }}">
                </div>
            </div>

            <div class="mt-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Descrição do Fato
                </label>
                <textarea name="descricao_fato" rows="4" required {{ $bloqueado ? 'readonly' : '' }}
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors {{ $bloqueado ? 'readonly-field' : '' }}">{{ old('descricao_fato', $ocorrencia->descricao_fato) }}</textarea>
                @error('descricao_fato')
                    <p class="mt-1 text-sm text-red-600"><i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}</p>
                @enderror
            </div>

            @unless($bloqueado)
                <div class="mt-4">
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        <i class="mr-1 fas fa-paperclip text-[#009496]"></i> Anexar mais Fotografias ou Documentos (PDF)
                    </label>
                    <input type="file" name="anexos_fato[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp"
                           class="block w-full px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#009496] file:text-white hover:file:bg-[#244853] transition-colors cursor-pointer focus:ring-2 focus:ring-[#009496] focus:border-[#009496]">
                </div>
            @endunless

            <div class="mt-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">Obrigação Descumprida</label>
                <textarea name="obrigacao_descumprida" rows="3" {{ $bloqueado ? 'readonly' : '' }}
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors {{ $bloqueado ? 'readonly-field' : '' }}">{{ old('obrigacao_descumprida', $ocorrencia->obrigacao_descumprida) }}</textarea>
            </div>

            <div class="mt-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">Prazo para Resposta / Solução</label>
                <input type="text" name="prazo_resposta" {{ $bloqueado ? 'readonly' : '' }}
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors {{ $bloqueado ? 'readonly-field' : '' }}"
                       value="{{ old('prazo_resposta', $ocorrencia->prazo_resposta) }}">
            </div>

            <div class="p-4 mt-4 border border-gray-200 rounded-lg bg-gray-50 {{ $bloqueado ? 'opacity-60' : '' }}">
                <p class="mb-3 text-sm font-medium text-gray-700">Meio de Comprovação</p>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach(\App\Models\Ocorrencia::TIPOS_COMPROVACAO as $chave => $rotulo)
                        <label class="flex items-start gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="tipo_comprovacao[{{ $chave }}]" value="1" {{ $bloqueado ? 'disabled' : '' }}
                                   class="mt-0.5 rounded text-[#009496] focus:ring-[#009496]"
                                   {{ old('tipo_comprovacao.'.$chave, data_get($ocorrencia->tipo_comprovacao, $chave)) ? 'checked' : '' }}
                                   {{ $chave === 'outros' ? 'id=tipo_comprovacao_outros_check' : '' }}>
                            {{ $rotulo }}
                        </label>
                    @endforeach
                </div>
                @if($bloqueado)
                    {{-- Reenvia os valores travados via hidden, já que os checkboxes ficam disabled --}}
                    @foreach(\App\Models\Ocorrencia::TIPOS_COMPROVACAO as $chave => $rotulo)
                        @if(data_get($ocorrencia->tipo_comprovacao, $chave))
                            <input type="hidden" name="tipo_comprovacao[{{ $chave }}]" value="1">
                        @endif
                    @endforeach
                    <input type="hidden" name="tipo_comprovacao_outro" value="{{ $ocorrencia->tipo_comprovacao_outro }}">
                @else
                    <div class="mt-3 campo-variavel {{ old('tipo_comprovacao.outros', data_get($ocorrencia->tipo_comprovacao, 'outros')) ? '' : 'campo-oculto' }}" id="campo_outros_descricao">
                        <input type="text" name="tipo_comprovacao_outro"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                               value="{{ old('tipo_comprovacao_outro', $ocorrencia->tipo_comprovacao_outro) }}">
                    </div>
                @endif
            </div>

            <div class="mt-6">
                <label class="block mb-2 text-sm font-medium text-gray-700">Situação</label>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    @foreach(\App\Enums\SituacaoOcorrenciaEnum::cases() as $situacao)
                        <label class="flex items-center gap-2 p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-[#009496] transition-all has-[:checked]:border-[#009496] has-[:checked]:bg-[#f0fdfa]">
                            <input type="radio" name="situacao" value="{{ $situacao->value }}"
                                   class="text-[#009496] focus:ring-[#009496]"
                                   {{ old('situacao', $ocorrencia->situacao?->value) === $situacao->value ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">{{ $situacao->getDisplayName() }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6">
            <a href="{{ route('admin.ocorrencias.show', $ocorrencia->id) }}"
                class="flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors bg-gray-100 rounded-lg hover:bg-gray-200 shadow-sm">
                <i class="mr-2 fas fa-times"></i> Cancelar
            </a>
            @if($bloqueado)
                <button type="submit"
                        class="flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-white transition-colors bg-[#009496] rounded-lg hover:bg-[#244853] shadow-sm">
                    <i class="mr-2 fas fa-save"></i> Salvar Alterações
                </button>
            @else
                <button type="submit" onclick="document.getElementById('status_input').value = 'rascunho';"
                        class="flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm">
                    <i class="mr-2 fas fa-file-pen"></i> Manter Rascunho
                </button>
                <button type="submit" onclick="document.getElementById('status_input').value = 'registrada';"
                        class="flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-white transition-colors bg-[#009496] rounded-lg hover:bg-[#244853] shadow-sm">
                    <i class="mr-2 fas fa-save"></i> Registrar Ocorrência
                </button>
            @endif
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkOutros = document.getElementById('tipo_comprovacao_outros_check');
    const campoOutros = document.getElementById('campo_outros_descricao');
    if (checkOutros && campoOutros) {
        checkOutros.addEventListener('change', function () {
            campoOutros.classList.toggle('campo-oculto', !this.checked);
        });
    }
});
</script>

@endsection
