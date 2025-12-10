@extends('layouts.app')
@section('page-title', 'Gestão de Usuários')
@section('page-subtitle', 'Gerencie os usuários e suas permissões')

@section('content')
    <!-- Botão Novo Processo -->
    <div class="flex justify-end mb-8">
        <a href="{{ route('admin.usuarios.create') }}"
            class="inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-[#052323] to-[#052323] rounded-xl hover:shadow-lg hover:scale-105">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
            </svg> Novo Usuário </a>
    </div>

    @if (session('success'))
        <div class="p-4 mb-6 rounded-lg bg-green-50">
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <div class="overflow-hidden bg-white shadow-sm rounded-xl">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-700">Usuários Cadastrados</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Nome</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Roles</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Permissões</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                        <tr>
                            <td class="px-6 py-4">{{ $user->name }}</td>
                            <td class="px-6 py-4">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                @foreach($user->roles as $role)
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-[#009496]/20 text-[#244853]">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-6 py-4">
                                @foreach($user->permissions_list as $permission)
                                    <span class="px-2 py-1 text-xs font-medium text-white bg-[#062F43] rounded">
                                        {{ $permission }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.usuarios.edit', $user) }}" class="text-yellow-600 hover:underline">Editar</a>
                                <form action="{{ route('admin.usuarios.destroy', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Excluir usuário?')" class="text-red-600 hover:underline">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
