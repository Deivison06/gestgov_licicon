@extends('layouts.app')

@section('page-title', 'Assinantes')
@section('page-subtitle', 'Usuários autorizados a assinar documentos digitalmente')

@section('content')
    {{-- Botões topo --}}
    <div class="flex flex-wrap items-center justify-between mb-8 gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.assinantes.importar-csv') }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M16 12l-4-4m0 0L8 12m4-4v12"/>
                </svg>
                Importar CSV
            </a>
        </div>

        <a href="{{ route('admin.assinantes.create') }}"
           class="inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-[#052323] to-[#052323] rounded-xl hover:shadow-lg hover:scale-105">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Novo Assinante
        </a>
    </div>

    {{-- Mensagens flash --}}
    @if (session('success'))
        <div class="p-4 mb-6 rounded-lg bg-green-50 border border-green-200">
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 mb-6 rounded-lg bg-red-50 border border-red-200">
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    @if (session('csv_erros') && count(session('csv_erros')) > 0)
        <details class="p-4 mb-6 rounded-lg bg-amber-50 border border-amber-200">
            <summary class="text-sm font-medium text-amber-800 cursor-pointer">
                {{ count(session('csv_erros')) }} erro(s) na importação — clique para ver
            </summary>
            <ul class="mt-3 text-xs text-amber-900 list-disc list-inside space-y-1">
                @foreach (session('csv_erros') as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </details>
    @endif

    {{-- Filtros --}}
    <div class="p-6 mb-6 bg-white rounded-xl shadow-sm">
        <h3 class="mb-4 text-lg font-medium text-gray-700">Filtros</h3>

        <form method="GET" action="{{ route('admin.assinantes.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Buscar</label>
                    <input type="text" name="search" value="{{ $filtros['search'] }}"
                           placeholder="Nome, e-mail ou nº portaria"
                           class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496] focus:border-[#009496]">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Prefeitura</label>
                    <select name="prefeitura_id"
                            class="block w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496]">
                        <option value="">Todas</option>
                        @foreach ($prefeituras as $p)
                            <option value="{{ $p->id }}" {{ $filtros['prefeitura_id'] == $p->id ? 'selected' : '' }}>
                                {{ $p->nome ?? $p->cidade }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Status</label>
                    <select name="status"
                            class="block w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496]">
                        <option value="todos"  {{ $filtros['status'] === 'todos' ? 'selected' : '' }}>Todos</option>
                        <option value="ativo"  {{ $filtros['status'] === 'ativo' ? 'selected' : '' }}>Ativos</option>
                        <option value="inativo" {{ $filtros['status'] === 'inativo' ? 'selected' : '' }}>Inativos</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="px-5 py-2 text-sm font-semibold text-white bg-[#009496] rounded-md hover:bg-[#007779]">
                    Filtrar
                </button>
                <a href="{{ route('admin.assinantes.index') }}"
                   class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    {{-- Tabela --}}
    <div class="overflow-hidden bg-white rounded-xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase tracking-wider">E-mail</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase tracking-wider">Prefeitura</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase tracking-wider">Unidade</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase tracking-wider">Portaria</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse ($assinantes as $a)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $a->name }}</div>
                                @if ($a->cpf)
                                    <div class="text-xs text-gray-500">CPF: {{ $a->cpf }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700">{{ $a->email }}</td>
                            <td class="px-6 py-3 text-sm text-gray-700">
                                {{ optional($a->prefeitura)->nome ?? optional($a->prefeitura)->cidade ?? '—' }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700">
                                {{ optional($a->unidade)->nome ?? '—' }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700">
                                @if ($a->numero_portaria)
                                    <span class="font-medium">{{ $a->numero_portaria }}</span>
                                    @if ($a->data_portaria)
                                        <div class="text-xs text-gray-500">{{ $a->data_portaria->format('d/m/Y') }}</div>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.assinantes.edit', $a->id) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ route('admin.assinantes.destroy', $a->id) }}"
                                          onsubmit="return confirm('Desativar este assinante? Assinaturas antigas serão preservadas.');">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-md hover:bg-red-100">
                                            Desativar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                Nenhum assinante encontrado com os filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($assinantes->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $assinantes->links() }}
            </div>
        @endif
    </div>
@endsection
