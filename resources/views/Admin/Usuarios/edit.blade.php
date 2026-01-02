@extends('layouts.app')
@section('page-title', 'Gestão de Usuários')
@section('page-subtitle', 'Atualize as informações do usuário')

@section('title', 'Editar Usuário')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Editar Usuário</h2>
        <p class="mt-1 text-sm text-gray-500">Atualize as informações do usuário</p>
    </div>
    <a href="{{ route('admin.usuarios.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors shadow-sm">
        <i class="fas fa-arrow-left"></i>
        Voltar
    </a>
</div>

<div class="overflow-hidden bg-white shadow-sm rounded-xl">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
        <h3 class="text-lg font-medium text-gray-700">Editando: {{ $usuario->name }}</h3>
    </div>

    <form action="{{ route('admin.usuarios.update', $usuario) }}" method="POST" class="px-6 py-6">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="p-4 mb-6 rounded-lg bg-red-50">
                <p class="text-sm font-medium text-red-800">Por favor, corrija os erros abaixo:</p>
                <ul class="mt-2 text-sm text-red-600 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
            <div>
                <label for="name" class="block mb-2 text-sm font-medium text-gray-700">Nome completo</label>
                <input type="text" name="name" id="name" value="{{ old('name', $usuario->name) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                       placeholder="Digite o nome completo" required>
            </div>

            <div>
                <label for="email" class="block mb-2 text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                       placeholder="Digite o email" required>
            </div>

            <div>
                <label for="cpf" class="block mb-2 text-sm font-medium text-gray-700">CPF</label>
                <input type="text" name="cpf" id="cpf" value="{{ old('cpf', $usuario->cpf) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                       placeholder="Digite o CPF">
            </div>

            <!-- Campo de Prefeitura -->
            <div>
                <label for="prefeitura_id" class="block mb-2 text-sm font-medium text-gray-700">Prefeitura</label>
                <select name="prefeitura_id" id="prefeitura_id"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors">
                    <option value="">Selecione uma prefeitura</option>
                    @foreach($prefeituras as $prefeitura)
                        <option value="{{ $prefeitura->id }}" 
                            {{ old('prefeitura_id', $usuario->prefeitura_id) == $prefeitura->id ? 'selected' : '' }}>
                            {{ $prefeitura->nome }} - {{ $prefeitura->cidade }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-gray-500" id="prefeitura-info" 
                   style="{{ $usuario->hasRole('prefeitura') ? '' : 'display: none;' }}">
                   Obrigatório para usuários do tipo Prefeitura
                </p>
            </div>

            <div>
                <label for="password" class="block mb-2 text-sm font-medium text-gray-700">Nova Senha (opcional)</label>
                <input type="password" name="password" id="password"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                       placeholder="Deixe em branco para manter a atual">
            </div>

            <div>
                <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-700">Confirmar Nova Senha</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                       placeholder="Confirme a nova senha">
            </div>
        </div>

        <div class="mb-6">
            <label class="block mb-2 text-sm font-medium text-gray-700">Permissões</label>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="p-4 border border-gray-200 rounded-lg">
                    <h4 class="mb-3 font-medium text-gray-700">Funções</h4>
                    @foreach($roles as $role)
                        <div class="flex items-center mb-2">
                            <input type="radio" name="role" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                   class="h-4 w-4 text-[#009496] focus:ring-[#009496] border-gray-300"
                                   {{ ($usuario->roles->contains($role->id) || old('role') == $role->id) ? 'checked' : '' }}>
                            <label for="role_{{ $role->id }}" class="block ml-2 text-sm text-gray-700">
                                {{ $role->name }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="p-4 border border-gray-200 rounded-lg">
                    <h4 class="mb-3 font-medium text-gray-700">Permissões Diretas</h4>
                    @php
                        // Obter todas as permissões do usuário (incluindo herdadas)
                        $userAllPermissions = $usuario->getAllPermissions()->pluck('id')->toArray();
                    @endphp
                    
                    @foreach($permissions as $permission)
                        <div class="flex items-center mb-2">
                            <input type="checkbox" 
                                name="permissions[]" 
                                value="{{ $permission->id }}" 
                                id="permission_{{ $permission->id }}"
                                {{ in_array($permission->id, $userAllPermissions) ? 'checked' : '' }}
                                {{ $usuario->hasRole('prefeitura') && $permission->name == 'contratos' ? 'checked disabled' : '' }}
                                class="h-4 w-4 text-[#009496] focus:ring-[#009496] border-gray-300 rounded">
                            <label for="permission_{{ $permission->id }}" class="block ml-2 text-sm text-gray-700">
                                {{ $permission->name }}
                                @if($usuario->hasRole('prefeitura') && $permission->name == 'contratos')
                                    <span class="text-xs text-gray-500">(herdada da função Prefeitura)</span>
                                @endif
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.usuarios.index') }}"
               class="px-4 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors shadow-sm">
                Cancelar
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-[#009496] rounded-lg hover:bg-[#244853] transition-colors shadow-sm">
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
    const prefeituraInfo = document.getElementById('prefeitura-info');
    
    function togglePrefeituraRequired() {
        const selectedRole = Array.from(roleInputs).find(input => input.checked);
        if (selectedRole) {
            const roleName = selectedRole.closest('label').textContent.trim();
            if (roleName === 'prefeitura') {
                prefeituraSelect.setAttribute('required', 'required');
                prefeituraInfo.style.display = 'block';
            } else {
                prefeituraSelect.removeAttribute('required');
                prefeituraInfo.style.display = 'none';
            }
        }
    }
    
    roleInputs.forEach(input => {
        input.addEventListener('change', togglePrefeituraRequired);
    });
    
    // Inicializar
    togglePrefeituraRequired();
});
</script>
@endsection