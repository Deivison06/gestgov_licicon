@extends('layouts.app')
@section('page-title', 'Gestão de Usuários')
@section('page-subtitle', 'Atualize as informações do usuário')

@section('title', 'Editar Usuário')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-[#e6f0ee]">Editar Usuário</h2>
        <p class="mt-1 text-sm text-[#a2b9b5]">Atualize as informações do usuário</p>
    </div>
    <a href="{{ route('admin.usuarios.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-[#e6f0ee] bg-[#10201e] border border-[#24403c] rounded-xl hover:bg-[#142825] hover:text-[#2DC197] transition-colors shadow-sm">
        <i class="fas fa-arrow-left"></i>
        Voltar
    </a>
</div>

<div class="overflow-hidden card !rounded-2xl">
    <div class="card-h">
        <h3 class="text-lg font-bold text-[#e6f0ee] flex items-center gap-2">
            <i class="fas fa-user-edit text-[#2DC197]"></i> Editando: {{ $usuario->name }}
        </h3>
    </div>

    <form action="{{ route('admin.usuarios.update', $usuario) }}" method="POST" class="px-6 py-6">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="p-4 mb-6 rounded-xl bg-red-950/60 border border-red-500/30">
                <p class="text-sm font-medium text-red-300">Por favor, corrija os erros abaixo:</p>
                <ul class="mt-2 text-sm text-red-400 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
            <div>
                <label for="name" class="block mb-2 text-sm font-semibold text-[#a2b9b5]">Nome completo <span class="text-red-400">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $usuario->name) }}"
                       class="w-full px-4 py-2.5 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] placeholder:text-[#a2b9b5]/40 transition-colors"
                       placeholder="Digite o nome completo" required>
            </div>

            <div>
                <label for="email" class="block mb-2 text-sm font-semibold text-[#a2b9b5]">Email <span class="text-red-400">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}"
                       class="w-full px-4 py-2.5 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] placeholder:text-[#a2b9b5]/40 transition-colors"
                       placeholder="Digite o email" required>
            </div>

            <!-- Campo de Prefeitura -->
            <div>
                <label for="prefeitura_id" class="block mb-2 text-sm font-semibold text-[#a2b9b5]">Prefeitura</label>
                <select name="prefeitura_id" id="prefeitura_id"
                        class="w-full px-4 py-2.5 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] transition-colors">
                    <option value="">Selecione uma prefeitura</option>
                    @foreach($prefeituras as $prefeitura)
                        <option value="{{ $prefeitura->id }}"
                            {{ old('prefeitura_id', $usuario->prefeitura_id) == $prefeitura->id ? 'selected' : '' }}>
                            {{ $prefeitura->nome }} - {{ $prefeitura->cidade }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-[#a2b9b5]" id="prefeitura-info"
                   style="{{ $usuario->hasRole('prefeitura') ? '' : 'display: none;' }}">
                   Obrigatório para usuários do tipo Prefeitura
                </p>
            </div>

            <div id="div_unidade_id" style="{{ $usuario->hasPermissionTo('fiscalizar contratos') ? '' : 'display: none;' }}">
                <label for="unidade_id" class="block mb-2 text-sm font-semibold text-[#a2b9b5]">Secretaria/Unidade Responsável</label>
                <select name="unidade_id" id="unidade_id"
                        class="w-full px-4 py-2.5 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] transition-colors">
                    <option value="">Todas as Secretarias (Fiscal Geral)</option>
                    @foreach($unidades as $unidade)
                        <option value="{{ $unidade->id }}" data-prefeitura="{{ $unidade->prefeitura_id }}"
                            {{ old('unidade_id', $usuario->unidade_id) == $unidade->id ? 'selected' : '' }}>
                            {{ $unidade->nome }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-[#a2b9b5]">Deixe em "Todas as Secretarias" para que o fiscal acompanhe contratos de todas as unidades da prefeitura</p>
            </div>

            <div>
                <label for="password" class="block mb-2 text-sm font-semibold text-[#a2b9b5]">Nova Senha (opcional)</label>
                <input type="password" name="password" id="password"
                       class="w-full px-4 py-2.5 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] placeholder:text-[#a2b9b5]/40 transition-colors"
                       placeholder="Deixe em branco para manter a atual">
            </div>

            <div>
                <label for="password_confirmation" class="block mb-2 text-sm font-semibold text-[#a2b9b5]">Confirmar Nova Senha</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="w-full px-4 py-2.5 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] placeholder:text-[#a2b9b5]/40 transition-colors"
                       placeholder="Confirme a nova senha">
            </div>
        </div>

        <div class="mb-6">
            <label class="block mb-3 text-sm font-semibold text-[#a2b9b5]">Permissões e Funções</label>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="p-5 bg-[#0d1b19] border border-[#24403c] rounded-xl">
                    <h4 class="mb-3 text-sm font-bold text-[#e6f0ee] border-b border-[#24403c] pb-2 flex items-center gap-2">
                        <i class="fas fa-user-tag text-[#2DC197]"></i> Funções (Roles)
                    </h4>
                    <div class="space-y-2">
                        @foreach($roles as $role)
                            <label for="role_{{ $role->id }}" class="flex items-center p-2 rounded-lg hover:bg-[#142825] cursor-pointer transition-colors">
                                <input type="radio" name="role" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                       class="h-4 w-4 text-[#2DC197] focus:ring-[#2DC197] border-[#24403c] bg-[#0a1514]"
                                       {{ ($usuario->roles->contains($role->id) || old('role') == $role->id) ? 'checked' : '' }}>
                                <span class="ml-2.5 text-sm text-[#e6f0ee] font-medium">
                                    {{ $role->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="p-5 bg-[#0d1b19] border border-[#24403c] rounded-xl">
                    <h4 class="mb-3 text-sm font-bold text-[#e6f0ee] border-b border-[#24403c] pb-2 flex items-center gap-2">
                        <i class="fas fa-key text-[#2DC197]"></i> Permissões Diretas
                    </h4>
                    @php
                        // Permissões diretas do usuário e permissões herdadas via role
                        $userDirectPermissions  = $usuario->getDirectPermissions()->pluck('id')->toArray();
                        $userViaRolePermissions = $usuario->getPermissionsViaRoles()->pluck('id')->toArray();
                    @endphp

                    <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                        @foreach($permissions as $permission)
                            @php
                                $isViaRole = in_array($permission->id, $userViaRolePermissions);
                                $isDirect  = in_array($permission->id, $userDirectPermissions);
                                $isChecked = $isDirect;
                            @endphp
                            <label for="permission_{{ $permission->id }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-[#142825] cursor-pointer transition-colors">
                                <div class="flex items-center">
                                    <input type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permission->id }}"
                                        id="permission_{{ $permission->id }}"
                                        {{ $isChecked ? 'checked' : '' }}
                                        class="h-4 w-4 text-[#2DC197] focus:ring-[#2DC197] border-[#24403c] bg-[#0a1514] rounded">
                                    <span class="ml-2.5 text-sm text-[#e6f0ee]">
                                        {{ $permission->name }}
                                    </span>
                                </div>
                                <div>
                                    @if($isViaRole && !$isDirect)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-500/15 text-blue-300 border border-blue-500/30">
                                            via role
                                        </span>
                                    @elseif($isViaRole && $isDirect)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-500/15 text-purple-300 border border-purple-500/30">
                                            role + direta
                                        </span>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-[#24403c]">
            <a href="{{ route('admin.usuarios.index') }}"
               class="px-5 py-2.5 text-sm font-semibold text-[#e6f0ee] bg-[#10201e] border border-[#24403c] rounded-xl hover:bg-[#142825] hover:text-[#2DC197] transition-colors shadow-sm">
                Cancelar
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-[#041d1a] bg-[#2DC197] rounded-xl hover:bg-[#26aa84] transition-all shadow-sm hover:scale-105">
                <i class="fas fa-save"></i>
                Atualizar Usuário
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleInputs = document.querySelectorAll('input[name="role"]');
    const prefeituraSelect = document.getElementById('prefeitura_id');
    const unidadeSelect = document.getElementById('unidade_id');
    const divUnidade = document.getElementById('div_unidade_id');
    const divPrefeitura = prefeituraSelect.closest('div');
    const unidadeOptions = Array.from(unidadeSelect.options);

    function getSelectedRoleName() {
        const selected = Array.from(roleInputs).find(input => input.checked);
        if (!selected) return '';
        const label = document.querySelector(`label[for="${selected.id}"]`);
        return label ? label.textContent.trim().toLowerCase() : '';
    }

    let isFirstLoad = true;

    function toggleInterface() {
        const roleName = getSelectedRoleName();
        const isAdmin = roleName.includes('diretor') || roleName.includes('gerente');

        if (isAdmin || roleName === '') {
            divPrefeitura.style.display = 'none';
            prefeituraSelect.value = '';
            prefeituraSelect.removeAttribute('required');
        } else {
            divPrefeitura.style.display = 'block';
            if (roleName.includes('prefeitura')) prefeituraSelect.setAttribute('required', 'required');
        }

        const fiscalCheck = Array.from(document.querySelectorAll('input[name="permissions[]"]'))
        .find(cb => {
            const label = document.querySelector(`label[for="${cb.id}"]`);
            return label && label.textContent.trim().toLowerCase().includes('fiscalizar contratos');
        });

        if (fiscalCheck && fiscalCheck.checked) {
            divUnidade.style.display = 'block';
            filterUnidades();
        } else {
            divUnidade.style.display = 'none';

            if (!isFirstLoad) {
                unidadeSelect.value = '';
            }
        }

        isFirstLoad = false;
    }

    function filterUnidades() {
        const prefeituraId = prefeituraSelect.value;
        const currentUnidade = unidadeSelect.value;
        unidadeSelect.innerHTML = '<option value="">Todas as Secretarias (Fiscal Geral)</option>';
        unidadeOptions.forEach(opt => {
            if (opt.value !== "" && opt.dataset.prefeitura === prefeituraId) {
                unidadeSelect.appendChild(opt);
            }
        });
        unidadeSelect.value = currentUnidade;
    }

    roleInputs.forEach(input => input.addEventListener('change', toggleInterface));
    prefeituraSelect.addEventListener('change', filterUnidades);
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
        cb.addEventListener('change', toggleInterface);
    });

    toggleInterface();
});
</script>
@endsection
