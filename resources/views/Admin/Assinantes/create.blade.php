@extends('layouts.app')

@section('page-title', 'Novo Assinante')
@section('page-subtitle', 'Cadastrar usuário com permissão de assinatura digital')

@section('content')
    <div class="py-6">

        @if ($errors->any())
            <div class="p-4 mb-6 rounded-xl bg-red-950/60 border border-red-500/30">
                <p class="font-medium text-red-300 mb-2">Corrija os erros abaixo:</p>
                <ul class="text-sm text-red-400 list-disc list-inside">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card !rounded-2xl overflow-hidden">
            <div class="card-h">
                <h3 class="text-lg font-bold text-[var(--ink)]">
                    <i class="fas fa-user-plus mr-2 text-[var(--mint)]"></i> Novo Assinante
                </h3>
            </div>

            <form method="POST" action="{{ route('admin.assinantes.store') }}"
                  class="p-8 space-y-6">
                @csrf

                <h3 class="pb-3 mb-2 text-lg font-semibold text-[var(--ink)] border-b border-[var(--line)] flex items-center gap-2">
                    <i class="fas fa-id-card text-[var(--mint)]"></i> Dados pessoais
                </h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">
                            Nome completo <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder:text-[var(--ink-2)]/40 transition-colors">
                    </div>

                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">
                            E-mail <span class="text-red-400">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder:text-[var(--ink-2)]/40 transition-colors">
                    </div>

                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">CPF</label>
                        <input type="text" name="cpf" value="{{ old('cpf') }}" maxlength="18"
                               placeholder="000.000.000-00"
                               class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder:text-[var(--ink-2)]/40 transition-colors">
                    </div>

                    <div></div>

                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">
                            Senha <span class="text-red-400">*</span>
                        </label>
                        <input type="password" name="password" required minlength="8"
                               class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                        <p class="mt-1 text-xs text-[var(--ink-2)]">Mínimo 8 caracteres.</p>
                    </div>

                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">
                            Confirmar senha <span class="text-red-400">*</span>
                        </label>
                        <input type="password" name="password_confirmation" required minlength="8"
                               class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                    </div>
                </div>

                <h3 class="pt-6 pb-3 mb-2 text-lg font-semibold text-[var(--ink)] border-b border-[var(--line)] flex items-center gap-2">
                    <i class="fas fa-building text-[var(--mint)]"></i> Vínculo institucional
                </h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">
                            Prefeitura <span class="text-red-400">*</span>
                        </label>
                        <select name="prefeitura_id" required
                                class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                            <option value="">Selecione...</option>
                            @foreach ($prefeituras as $p)
                                <option value="{{ $p->id }}" {{ old('prefeitura_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nome ?? $p->cidade }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Unidade / Setor</label>
                        <select name="unidade_id"
                                class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                            <option value="">— Nenhuma —</option>
                            @foreach ($unidades as $u)
                                <option value="{{ $u->id }}"
                                        data-prefeitura="{{ $u->prefeitura_id }}"
                                        {{ old('unidade_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->nome }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-[var(--ink-2)]">Opcional — pode ficar vazio.</p>
                    </div>

                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Número da Portaria</label>
                        <input type="text" name="numero_portaria" value="{{ old('numero_portaria') }}" maxlength="50"
                               placeholder="Ex.: 002/2025"
                               class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder:text-[var(--ink-2)]/40 transition-colors">
                    </div>

                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Data da Portaria</label>
                        <input type="date" name="data_portaria" value="{{ old('data_portaria') }}"
                               class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-[var(--line)]">
                    <a href="{{ route('admin.assinantes.index') }}"
                       class="px-5 py-2.5 text-sm font-semibold text-[var(--ink)] bg-[var(--card)] border border-[var(--line)] rounded-xl hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] transition-colors shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-sm hover:scale-105">
                        <i class="fas fa-save mr-1.5"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
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
