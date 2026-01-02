@extends('layouts.app')
@section('page-title', 'Gestão de Usuários')
@section('page-subtitle', 'Gerencie os usuários e suas permissões')

@section('content')
    <!-- Botão Novo Processo -->
    <div class="flex items-center justify-between mb-8">
        <div></div> <!-- Espaçador -->
        <a href="{{ route('admin.usuarios.create') }}"
            class="inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-[#052323] to-[#052323] rounded-xl hover:shadow-lg hover:scale-105">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
            </svg> Novo Usuário 
        </a>
    </div>

    @if (session('success'))
        <div class="p-4 mb-6 rounded-lg bg-green-50">
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Filtros -->
    <div class="p-6 mb-6 bg-white rounded-xl shadow-sm">
        <h3 class="mb-4 text-lg font-medium text-gray-700">Filtros</h3>
        
        <form method="GET" action="{{ route('admin.usuarios.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <!-- Busca -->
                <div>
                    <label for="search" class="block mb-2 text-sm font-medium text-gray-700">Buscar</label>
                    <input type="text" 
                           name="search" 
                           id="search" 
                           value="{{ $filters['search'] ?? '' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496]"
                           placeholder="Nome, Email ou CPF">
                </div>

                <!-- Filtro por Função -->
                <div>
                    <label for="role" class="block mb-2 text-sm font-medium text-gray-700">Função</label>
                    <select name="role" 
                            id="role" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496]">
                        <option value="">Todas as funções</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ ($filters['role'] ?? '') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Prefeitura -->
                <div>
                    <label for="prefeitura_id" class="block mb-2 text-sm font-medium text-gray-700">Prefeitura</label>
                    <select name="prefeitura_id" 
                            id="prefeitura_id" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496]">
                        <option value="">Todas as prefeituras</option>
                        @foreach($prefeituras as $prefeitura)
                            <option value="{{ $prefeitura->id }}" {{ ($filters['prefeitura_id'] ?? '') == $prefeitura->id ? 'selected' : '' }}>
                                {{ $prefeitura->nome }} - {{ $prefeitura->cidade }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Botões dos Filtros -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.usuarios.index') }}" 
                   class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Limpar Filtros
                </a>
                <button type="submit" 
                        class="px-4 py-2 text-sm font-semibold text-white bg-[#009496] rounded-lg hover:bg-[#244853]">
                    Aplicar Filtros
                </button>
            </div>
        </form>
    </div>

    {{-- <!-- Estatísticas -->
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
        <div class="p-4 bg-white rounded-xl shadow-sm">
            <div class="text-sm font-medium text-gray-500">Total de Usuários</div>
            <div class="mt-2 text-2xl font-bold text-gray-800">{{ $users->total() }}</div>
        </div>
        <div class="p-4 bg-white rounded-xl shadow-sm">
            <div class="text-sm font-medium text-gray-500">Usuários Prefeitura</div>
            <div class="mt-2 text-2xl font-bold text-[#009496]">
                {{ $users->where('prefeitura_id', '!=', null)->count() }}
            </div>
        </div>
        <div class="p-4 bg-white rounded-xl shadow-sm">
            <div class="text-sm font-medium text-gray-500">Por Página</div>
            <div class="mt-2 text-2xl font-bold text-gray-800">{{ $users->perPage() }}</div>
        </div>
        <div class="p-4 bg-white rounded-xl shadow-sm">
            <div class="text-sm font-medium text-gray-500">Página Atual</div>
            <div class="mt-2 text-2xl font-bold text-gray-800">{{ $users->currentPage() }}</div>
        </div>
    </div> --}}

    <!-- Tabela de Usuários -->
    <div class="overflow-hidden bg-white shadow-sm rounded-xl">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-700">Usuários Cadastrados</h3>
                <span class="text-sm text-gray-500">
                    Mostrando {{ $users->firstItem() }} - {{ $users->lastItem() }} de {{ $users->total() }} usuários
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Nome</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Email/CPF</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Função</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Prefeitura</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Permissões</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $user->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                @if($user->cpf)
                                    <div class="text-xs text-gray-500">CPF: {{ $user->cpf }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @foreach($user->roles as $role)
                                    <span class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-[#009496]/20 text-[#244853] mb-1">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-6 py-4">
                                @if($user->prefeitura)
                                    <span class="px-2 py-1 text-xs font-medium text-white bg-[#062F43] rounded">
                                        {{ $user->prefeitura->nome }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-500">Não atribuída</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($user->permissions_list->count() > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->permissions_list as $permission)
                                            <span class="px-2 py-1 text-xs font-medium text-white bg-gray-600 rounded">
                                                {{ $permission }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-500">Nenhuma permissão direta</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.usuarios.edit', $user) }}" 
                                       class="inline-flex items-center gap-1 px-3 py-1 text-sm text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </a>
                                    <form action="{{ route('admin.usuarios.destroy', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                onclick="confirmDelete('{{ $user->name }}', this.form)"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-sm text-red-600 bg-red-50 rounded-lg hover:bg-red-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Excluir
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 2.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                                <p class="mt-4">Nenhum usuário encontrado</p>
                                @if(!empty($filters['search']) || !empty($filters['role']) || !empty($filters['prefeitura_id']))
                                    <p class="mt-2 text-sm text-gray-400">Tente alterar os filtros</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Mostrando {{ $users->firstItem() }} a {{ $users->lastItem() }} de {{ $users->total() }} resultados
                    </div>
                    <div class="flex gap-2">
                        <!-- Primeira página -->
                        @if($users->onFirstPage())
                            <span class="px-3 py-1 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                &laquo;
                            </span>
                        @else
                            <a href="{{ $users->url(1) . '&' . http_build_query(request()->except('page')) }}" 
                               class="px-3 py-1 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                &laquo;
                            </a>
                        @endif

                        <!-- Página anterior -->
                        @if($users->onFirstPage())
                            <span class="px-3 py-1 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                &lsaquo;
                            </span>
                        @else
                            <a href="{{ $users->previousPageUrl() . '&' . http_build_query(request()->except('page')) }}" 
                               class="px-3 py-1 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                &lsaquo;
                            </a>
                        @endif

                        <!-- Números de páginas -->
                        @foreach($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                            @if($page == $users->currentPage())
                                <span class="px-3 py-1 text-sm text-white bg-[#009496] rounded-lg">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url . '&' . http_build_query(request()->except('page')) }}" 
                                   class="px-3 py-1 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach

                        <!-- Próxima página -->
                        @if($users->hasMorePages())
                            <a href="{{ $users->nextPageUrl() . '&' . http_build_query(request()->except('page')) }}" 
                               class="px-3 py-1 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                &rsaquo;
                            </a>
                        @else
                            <span class="px-3 py-1 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                &rsaquo;
                            </span>
                        @endif

                        <!-- Última página -->
                        @if($users->hasMorePages())
                            <a href="{{ $users->url($users->lastPage()) . '&' . http_build_query(request()->except('page')) }}" 
                               class="px-3 py-1 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                &raquo;
                            </a>
                        @else
                            <span class="px-3 py-1 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                &raquo;
                            </span>
                        @endif
                    </div>

                    <!-- Seletor de itens por página -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-700">Itens por página:</span>
                        <select onchange="changePerPage(this.value)" 
                                class="px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496]">
                            <option value="10" {{ $users->perPage() == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $users->perPage() == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $users->perPage() == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $users->perPage() == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
function confirmDelete(userName, form) {
    if (confirm(`Tem certeza que deseja excluir o usuário "${userName}"? Esta ação não pode ser desfeita.`)) {
        form.submit();
    }
}

function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.delete('page'); // Voltar para primeira página
    window.location.href = url.toString();
}

// Mantém os filtros ao mudar de página
document.querySelectorAll('.pagination a').forEach(link => {
    const url = new URL(link.href);
    const params = new URLSearchParams(window.location.search);
    
    // Copia todos os parâmetros exceto 'page'
    params.forEach((value, key) => {
        if (key !== 'page' && !url.searchParams.has(key)) {
            url.searchParams.set(key, value);
        }
    });
    
    link.href = url.toString();
});
</script>
@endpush