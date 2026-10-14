@extends('layouts.app')

@section('page-title', 'Editar Assinante')
@section('page-subtitle', $assinante->name)

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
                <h3 class="text-lg font-bold text-[#e6f0ee]">
                    <i class="fas fa-user-edit mr-2 text-[#2DC197]"></i> Editando: {{ $assinante->name }}
                </h3>
            </div>

            <form method="POST" action="{{ route('admin.assinantes.update', $assinante->id) }}"
                  class="p-8 space-y-6">
                @csrf @method('PUT')

                <h3 class="pb-3 mb-2 text-lg font-semibold text-[#e6f0ee] border-b border-[#24403c] flex items-center gap-2">
                    <i class="fas fa-id-card text-[#2DC197]"></i> Dados pessoais
                </h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-[#a2b9b5]">
                            Nome completo <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $assinante->name) }}" required
                               class="block w-full px-4 py-2.5 text-sm bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] transition-colors">
                    </div>

                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-[#a2b9b5]">
                            E-mail <span class="text-red-400">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $assinante->email) }}" required
                               class="block w-full px-4 py-2.5 text-sm bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] transition-colors">
                    </div>

                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-[#a2b9b5]">CPF</label>
                        <input type="text" name="cpf" value="{{ old('cpf', $assinante->cpf) }}" maxlength="18"
                               placeholder="000.000.000-00"
                               class="block w-full px-4 py-2.5 text-sm bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] placeholder:text-[#a2b9b5]/40 transition-colors">
                    </div>

                    <div></div>

                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-[#a2b9b5]">Nova senha</label>
                        <input type="password" name="password" minlength="8"
                               class="block w-full px-4 py-2.5 text-sm bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] transition-colors">
                        <p class="mt-1 text-xs text-[#a2b9b5]">Deixe em branco para manter a senha atual.</p>
                    </div>

                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-[#a2b9b5]">Confirmar nova senha</label>
                        <input type="password" name="password_confirmation" minlength="8"
                               class="block w-full px-4 py-2.5 text-sm bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] transition-colors">
                    </div>
                </div>

                <h3 class="pt-6 pb-3 mb-2 text-lg font-semibold text-[#e6f0ee] border-b border-[#24403c] flex items-center gap-2">
                    <i class="fas fa-building text-[#2DC197]"></i> Vínculo institucional
                </h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-[#a2b9b5]">
                            Prefeitura <span class="text-red-400">*</span>
                        </label>
                        <select name="prefeitura_id" required
                                class="block w-full px-4 py-2.5 text-sm bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] transition-colors">
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
                        <label class="block mb-1.5 text-sm font-semibold text-[#a2b9b5]">Unidade / Setor</label>
                        <select name="unidade_id"
                                class="block w-full px-4 py-2.5 text-sm bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] transition-colors">
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
                        <label class="block mb-1.5 text-sm font-semibold text-[#a2b9b5]">Número da Portaria</label>
                        <input type="text" name="numero_portaria"
                               value="{{ old('numero_portaria', $assinante->numero_portaria) }}" maxlength="50"
                               class="block w-full px-4 py-2.5 text-sm bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] transition-colors">
                    </div>

                    <div>
                        <label class="block mb-1.5 text-sm font-semibold text-[#a2b9b5]">Data da Portaria</label>
                        <input type="date" name="data_portaria"
                               value="{{ old('data_portaria', optional($assinante->data_portaria)->format('Y-m-d')) }}"
                               class="block w-full px-4 py-2.5 text-sm bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] transition-colors">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-[#24403c]">
                    <a href="{{ route('admin.assinantes.index') }}"
                       class="px-5 py-2.5 text-sm font-semibold text-[#e6f0ee] bg-[#10201e] border border-[#24403c] rounded-xl hover:bg-[#142825] hover:text-[#2DC197] transition-colors shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 text-sm font-bold text-[#041d1a] bg-[#2DC197] rounded-xl hover:bg-[#26aa84] transition-all shadow-sm hover:scale-105">
                        <i class="fas fa-save mr-1.5"></i> Salvar alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
