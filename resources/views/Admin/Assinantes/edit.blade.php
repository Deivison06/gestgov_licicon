@extends('layouts.app')

@section('page-title', 'Editar Assinante')
@section('page-subtitle', $assinante->name)

@section('content')
    <div class="max-w-4xl mx-auto">

        @if ($errors->any())
            <div class="p-4 mb-6 rounded-lg bg-red-50 border border-red-200">
                <p class="font-medium text-red-800 mb-2">Corrija os erros abaixo:</p>
                <ul class="text-sm text-red-700 list-disc list-inside">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.assinantes.update', $assinante->id) }}"
              class="p-8 bg-white rounded-xl shadow-sm space-y-6">
            @csrf @method('PUT')

            <h3 class="pb-3 mb-2 text-lg font-semibold text-gray-800 border-b border-gray-200">
                Dados pessoais
            </h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Nome completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $assinante->name) }}" required
                           class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496]">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        E-mail <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $assinante->email) }}" required
                           class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496]">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">CPF</label>
                    <input type="text" name="cpf" value="{{ old('cpf', $assinante->cpf) }}" maxlength="18"
                           placeholder="000.000.000-00"
                           class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496]">
                </div>

                <div></div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Nova senha</label>
                    <input type="password" name="password" minlength="8"
                           class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496]">
                    <p class="mt-1 text-xs text-gray-500">Deixe em branco para manter a senha atual.</p>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Confirmar nova senha</label>
                    <input type="password" name="password_confirmation" minlength="8"
                           class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496]">
                </div>
            </div>

            <h3 class="pt-6 pb-3 mb-2 text-lg font-semibold text-gray-800 border-b border-gray-200">
                Vínculo institucional
            </h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Prefeitura <span class="text-red-500">*</span>
                    </label>
                    <select name="prefeitura_id" required
                            class="block w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496]">
                        <option value="">Selecione...</option>
                        @foreach ($prefeituras as $p)
                            <option value="{{ $p->id }}"
                                    {{ old('prefeitura_id', $assinante->prefeitura_id) == $p->id ? 'selected' : '' }}>
                                {{ $p->nome ?? $p->cidade }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Unidade / Setor</label>
                    <select name="unidade_id"
                            class="block w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496]">
                        <option value="">— Nenhuma —</option>
                        @foreach ($unidades as $u)
                            <option value="{{ $u->id }}"
                                    {{ old('unidade_id', $assinante->unidade_id) == $u->id ? 'selected' : '' }}>
                                {{ $u->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Número da Portaria</label>
                    <input type="text" name="numero_portaria"
                           value="{{ old('numero_portaria', $assinante->numero_portaria) }}" maxlength="50"
                           class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496]">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Data da Portaria</label>
                    <input type="date" name="data_portaria"
                           value="{{ old('data_portaria', optional($assinante->data_portaria)->format('Y-m-d')) }}"
                           class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496]">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.assinantes.index') }}"
                   class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-6 py-2 text-sm font-semibold text-white bg-[#009496] rounded-md hover:bg-[#007779]">
                    Salvar alterações
                </button>
            </div>
        </form>
    </div>
@endsection
