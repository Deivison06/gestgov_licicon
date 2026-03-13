@extends('layouts.app')
@section('page-title', 'Planos de Contratação Anual (PCA)')
@section('page-subtitle', 'Gerencie os Planos de Contratação Anual do sistema')

@section('content')
<div class="py-8">
    <div class="flex justify-end mb-8">
        <a href="{{ route('admin.pcas.create') }}" class="inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-[#052323] to-[#052323] rounded-xl hover:shadow-lg hover:scale-105">
            <i class="fas fa-plus"></i>
            Novo PCA
        </a>
    </div>

    @if (session('success'))
    <div class="p-4 mb-4 border border-green-200 shadow-sm rounded-2xl bg-gradient-to-r from-green-50 to-emerald-50">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-600 mr-3"></i>
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif
    @if (session('error'))
    <div class="p-4 mb-4 border border-red-200 shadow-sm rounded-2xl bg-gradient-to-r from-red-50 to-red-100">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl mb-8">
        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Filtros de Busca</h3>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.pcas.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-3">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Pesquisar</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Nº PCA, Exercício ou Unidade" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none">
                </div>
                <div class="md:col-span-2">
                    <label for="exercicio" class="block text-sm font-medium text-gray-700 mb-1">Exercício</label>
                    <input type="text" id="exercicio" name="exercicio" value="{{ request('exercicio') }}" placeholder="Ex: 2026" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none">
                </div>
                <div class="md:col-span-2">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none bg-white">
                        <option value="">Todos</option>
                        <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="em_analise" {{ request('status') == 'em_analise' ? 'selected' : '' }}>Em Análise</option>
                        <option value="aprovado" {{ request('status') == 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                        <option value="recusado" {{ request('status') == 'recusado' ? 'selected' : '' }}>Recusado</option>
                    </select>
                </div>
                @if(!$isPrefeituraUser)
                <div class="md:col-span-3">
                    <label for="prefeitura_id" class="block text-sm font-medium text-gray-700 mb-1">Prefeitura</label>
                    <select id="prefeitura_id" name="prefeitura_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0596A2] focus:border-[#0596A2] outline-none bg-white">
                        <option value="">Todas</option>
                        @foreach($prefeituras as $pref)
                            <option value="{{ $pref->id }}" {{ request('prefeitura_id') == $pref->id ? 'selected' : '' }}>
                                {{ $pref->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="w-full px-4 py-2 text-white bg-[#0596A2] rounded-lg hover:bg-[#047a84] transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    @if(request()->anyFilled(['search', 'exercicio', 'status', 'prefeitura_id']))
                    <a href="{{ route('admin.pcas.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 transition-colors flex items-center justify-center" title="Limpar Filtros">
                        <i class="fas fa-times"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-100 shadow-sm rounded-2xl">
        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-xl font-semibold text-gray-800">Lista de PCAs</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full overflow-hidden divide-y divide-gray-200 rounded-lg shadow-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Número PCA</th>
                        @if(!$isPrefeituraUser)
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Prefeitura</th>
                        @endif
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Exercício</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Itens Estimados</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Data Criação</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-center text-gray-600 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pcas as $pca)
                        <tr class="transition-colors duration-200 hover:bg-gray-50/80">
                            <td class="px-4 py-3 font-semibold text-gray-900 whitespace-nowrap">{{ $pca->numero_pca ?? 'S/N' }}</td>
                            @if(!$isPrefeituraUser)
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $pca->prefeitura->nome ?? 'N/I' }}</td>
                            @endif
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $pca->exercicio }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mb-1">
                                    {{ $pca->itens->count() }} itens
                                </span>
                                <br>
                                <span class="text-xs text-green-600 font-semibold">R$ {{ number_format($pca->itens->sum('valor_estimado'), 2, ',', '.') }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($pca->status === 'pendente') bg-yellow-100 text-yellow-800
                                    @elseif($pca->status === 'em_analise') bg-blue-100 text-blue-800
                                    @elseif($pca->status === 'aprovado') bg-green-100 text-green-800
                                    @elseif($pca->status === 'recusado') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $pca->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                                {{ $pca->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('admin.pcas.show', $pca->id) }}" class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 transition-colors duration-200 rounded-md hover:bg-indigo-100" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <a href="{{ route('admin.pcas.pdf', $pca->id) }}" class="inline-flex items-center justify-center w-8 h-8 text-gray-600 transition-colors duration-200 rounded-md hover:bg-gray-200" title="Baixar PDF" target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>

                                    @if(in_array($pca->status, ['pendente', 'em_analise']))
                                        <a href="{{ route('admin.pcas.edit', $pca->id) }}" class="inline-flex items-center justify-center w-8 h-8 text-yellow-600 transition-colors duration-200 rounded-md hover:bg-yellow-100" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <form action="{{ route('admin.pcas.destroy', $pca->id) }}" method="POST" class="inline d-inline m-0 p-0" onsubmit="return confirm('Tem certeza que deseja excluir este PCA? Todos os itens vinculados também serão apagados.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 text-red-600 transition-colors duration-200 rounded-md hover:bg-red-100" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isPrefeituraUser ? '6' : '7' }}" class="px-6 py-10 text-center text-gray-500">
                                <i class="fas fa-inbox text-3xl mb-3 text-gray-300 d-block block"></i>
                                <p class="text-sm">Nenhum PCA encontrado.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pcas->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $pcas->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
