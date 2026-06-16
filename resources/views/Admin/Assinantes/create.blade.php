@extends('layouts.app')

@section('page-title', 'Novo Assinante')
@section('page-subtitle', 'Cadastrar usuário com permissão de assinatura digital')

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

        <form method="POST" action="{{ route('admin.assinantes.store') }}"
              class="p-8 bg-white rounded-xl shadow-sm space-y-6">
            @csrf

            <h3 class="pb-3 mb-2 text-lg font-semibold text-gray-800 border-b border-gray-200">
                Dados pessoais
            </h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Nome completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496] focus:border-[#009496]">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        E-mail <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496] focus:border-[#009496]">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">CPF</label>
                    <input type="text" name="cpf" value="{{ old('cpf') }}" maxlength="18"
                           placeholder="000.000.000-00"
                           class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496]">
                </div>

                <div></div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Senha <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" required minlength="8"
                           class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496]">
                    <p class="mt-1 text-xs text-gray-500">Mínimo 8 caracteres.</p>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Confirmar senha <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" required minlength="8"
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
                            <option value="{{ $p->id }}" {{ old('prefeitura_id') == $p->id ? 'selected' : '' }}>
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
                                    data-prefeitura="{{ $u->prefeitura_id }}"
                                    {{ old('unidade_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->nome }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Opcional — pode ficar vazio.</p>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Número da Portaria</label>
                    <input type="text" name="numero_portaria" value="{{ old('numero_portaria') }}" maxlength="50"
                           placeholder="Ex.: 002/2025"
                           class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496]">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Data da Portaria</label>
                    <input type="date" name="data_portaria" value="{{ old('data_portaria') }}"
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
                    Salvar
                </button>
            </div>
        </form>
    </div>

    {{-- Filtro dinâmico de unidades por prefeitura --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selPref = document.querySelector('select[name="prefeitura_id"]');
            const selUnid = document.querySelector('select[name="unidade_id"]');
            if (!selPref || !selUnid) return;

            function filtrarUnidades() {
                const prefId = selPref.value;
                Array.from(selUnid.options).forEach(opt => {
                    if (!opt.value) return; // mantém "— Nenhuma —"
                    const matchPref = opt.dataset.prefeitura;
                    opt.hidden = (prefId && matchPref !== prefId);
                });
                // se a unidade selecionada agora está oculta, limpa
                if (selUnid.selectedOptions[0]?.hidden) {
                    selUnid.value = '';
                }
            }

            selPref.addEventListener('change', filtrarUnidades);
            filtrarUnidades();
        });
    </script>
@endsection
