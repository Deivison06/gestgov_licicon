@extends('layouts.app')
@section('page-title', 'Planos de Contratação Anual (PCA)')
@section('page-subtitle', 'Gerencie os Planos de Contratação Anual do sistema')

@section('content')
<div class="py-8">
    <div class="flex justify-end mb-8">
        <a href="{{ route('admin.pcas.create') }}" class="inline-flex items-center gap-3 px-6 py-3 text-sm font-bold text-[#041d1a] transition-all duration-200 bg-[#2DC197] rounded-xl hover:bg-[#26aa84] hover:shadow-lg hover:scale-105">
            <i class="fas fa-plus"></i>
            Novo PCA
        </a>
    </div>

    @if (session('success'))
    <div class="p-4 mb-4 border border-[#184e40] shadow-sm rounded-2xl bg-[#0d2a23] text-[#2DC197]">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-3"></i>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    </div>
    @endif
    @if (session('error'))
    <div class="p-4 mb-4 border border-[#5a2328] shadow-sm rounded-2xl bg-[#2a1315] text-[#ff7b72]">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-3"></i>
            <p class="text-sm font-medium">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <div class="overflow-hidden card !rounded-2xl mb-8">
        <div class="card-h">
            <h3 class="text-lg font-bold text-[#e6f0ee]">Filtros de Busca</h3>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.pcas.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-3">
                    <label for="search" class="block text-sm font-medium text-[#a2b9b5] mb-1">Pesquisar</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Nº PCA, Exercício ou Unidade" class="w-full px-4 py-2 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-lg focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] outline-none">
                </div>
                <div class="md:col-span-2">
                    <label for="exercicio" class="block text-sm font-medium text-[#a2b9b5] mb-1">Exercício</label>
                    <input type="text" id="exercicio" name="exercicio" value="{{ request('exercicio') }}" placeholder="Ex: 2026" class="w-full px-4 py-2 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-lg focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] outline-none">
                </div>
                <div class="md:col-span-2">
                    <label for="status" class="block text-sm font-medium text-[#a2b9b5] mb-1">Status</label>
                    <select id="status" name="status" class="w-full px-4 py-2 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-lg focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] outline-none">
                        <option value="">Todos</option>
                        <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="em_analise" {{ request('status') == 'em_analise' ? 'selected' : '' }}>Em Análise</option>
                        <option value="aprovado" {{ request('status') == 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                        <option value="recusado" {{ request('status') == 'recusado' ? 'selected' : '' }}>Recusado</option>
                    </select>
                </div>
                @if(!$isPrefeituraUser)
                <div class="md:col-span-3">
                    <label for="prefeitura_id" class="block text-sm font-medium text-[#a2b9b5] mb-1">Prefeitura</label>
                    <select id="prefeitura_id" name="prefeitura_id" class="w-full px-4 py-2 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-lg focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] outline-none">
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
                    <button type="submit" class="w-full px-4 py-2 text-[#041d1a] font-bold bg-[#2DC197] rounded-lg hover:bg-[#26aa84] transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    @if(request()->anyFilled(['search', 'exercicio', 'status', 'prefeitura_id']))
                    <a href="{{ route('admin.pcas.index') }}" class="px-4 py-2 text-[#a2b9b5] bg-[#142825] border border-[#24403c] rounded-lg hover:bg-[#1a332f] hover:text-[#e6f0ee] transition-colors flex items-center justify-center" title="Limpar Filtros">
                        <i class="fas fa-times"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="overflow-hidden transition-all duration-300 card !rounded-2xl">
        <div class="card-h">
            <h3 class="text-xl font-bold text-[#e6f0ee]">Lista de PCAs</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full overflow-hidden divide-y divide-[#24403c] rounded-lg shadow-sm">
                <thead class="bg-[#0a1514]">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-[#a2b9b5] uppercase">Número PCA</th>
                        @if(!$isPrefeituraUser)
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-[#a2b9b5] uppercase">Prefeitura</th>
                        @endif
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-[#a2b9b5] uppercase">Exercício</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-[#a2b9b5] uppercase">Itens Estimados</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-[#a2b9b5] uppercase">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-[#a2b9b5] uppercase">Data Criação</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-center text-[#a2b9b5] uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-[#10201e] divide-y divide-[#1a302d]">
                    @forelse($pcas as $pca)
                        <tr class="transition-colors duration-200 hover:bg-[#142825]">
                            <td class="px-4 py-3 font-semibold text-[#e6f0ee] whitespace-nowrap">{{ $pca->numero_pca ?? 'S/N' }}</td>
                            @if(!$isPrefeituraUser)
                            <td class="px-4 py-3 text-sm text-[#c2d6d3]">{{ $pca->prefeitura->nome ?? 'N/I' }}</td>
                            @endif
                            <td class="px-4 py-3 text-sm text-[#e6f0ee]">{{ $pca->exercicio }}</td>
                            <td class="px-4 py-3 text-sm text-[#c2d6d3]">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-[#0a1514] text-[#a2b9b5] border border-[#24403c] mb-1">
                                    {{ $pca->itens->count() }} itens
                                </span>
                                <br>
                                <span class="text-xs text-emerald-400 font-semibold">R$ {{ number_format($pca->itens->sum('valor_estimado'), 2, ',', '.') }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($pca->status === 'pendente') bg-amber-900/40 text-amber-300 border border-amber-500/30
                                    @elseif($pca->status === 'em_analise') bg-blue-900/40 text-blue-300 border border-blue-500/30
                                    @elseif($pca->status === 'aprovado') bg-emerald-900/40 text-emerald-300 border border-emerald-500/30
                                    @elseif($pca->status === 'recusado') bg-red-900/40 text-red-300 border border-red-500/30
                                    @else bg-[#142825] text-[#a2b9b5] border border-[#24403c] @endif">
                                    {{ ucfirst(str_replace('_', ' ', $pca->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-[#a2b9b5] whitespace-nowrap">
                                {{ $pca->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('admin.pcas.show', $pca->id) }}" class="inline-flex items-center justify-center w-8 h-8 text-[#2DC197] transition-colors duration-200 rounded-md hover:bg-[#142825] hover:text-[#5eead4]" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <a href="{{ route('admin.pcas.pdf', $pca->id) }}" class="inline-flex items-center justify-center w-8 h-8 text-red-400 transition-colors duration-200 rounded-md hover:bg-[#142825] hover:text-red-300" title="Baixar PDF" target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>

                                    @if(in_array($pca->status, ['pendente', 'em_analise']))
                                        <a href="{{ route('admin.pcas.edit', $pca->id) }}" class="inline-flex items-center justify-center w-8 h-8 text-amber-400 transition-colors duration-200 rounded-md hover:bg-[#142825] hover:text-amber-300" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <form action="{{ route('admin.pcas.destroy', $pca->id) }}" method="POST" class="inline d-inline m-0 p-0" onsubmit="return confirm('Tem certeza que deseja excluir este PCA? Todos os itens vinculados também serão apagados.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 text-red-400 transition-colors duration-200 rounded-md hover:bg-[#142825] hover:text-red-300" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isPrefeituraUser ? '6' : '7' }}" class="px-6 py-10 text-center text-[#a2b9b5]">
                                <i class="fas fa-inbox text-3xl mb-3 text-[#24403c] d-block block"></i>
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
