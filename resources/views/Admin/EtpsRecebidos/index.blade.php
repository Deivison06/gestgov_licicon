@extends('layouts.app')
@section('page-title', 'ETPs Recebidos')
@section('page-subtitle', 'Análise e aprovação de Estudos Técnicos Preliminares de todas as Secretarias')

@section('content')
<div class="py-8">
    @if (session('success'))
    <div class="p-4 mb-8 border border-green-200 shadow-sm rounded-2xl bg-gradient-to-r from-green-50 to-emerald-50">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif
    @if (session('error'))
    <div class="p-4 mb-8 border border-red-200 shadow-sm rounded-2xl bg-gradient-to-r from-red-50 to-red-100">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <!-- Filtros avançados -->
    <div class="mb-6 bg-white border border-gray-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center cursor-pointer" onclick="document.getElementById('filtrosBody').classList.toggle('hidden')">
            <div>
                <h4 class="text-lg font-semibold text-gray-800"><i class="fas fa-filter mr-2 text-[#009496]"></i> Filtros de Busca</h4>
                <p class="text-sm text-gray-500">Clique para expandir ou recolher os filtros</p>
            </div>
            <i class="fas fa-chevron-down text-gray-400"></i>
        </div>

        <form action="{{ route('admin.etps_recebidos.index') }}" method="GET" id="filtrosBody" class="p-6 {{ count(array_filter($filters)) > 0 ? '' : 'hidden' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Status -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent text-sm">
                        <option value="">Todos os Status</option>
                        <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="em_analise" {{ request('status') == 'em_analise' ? 'selected' : '' }}>Em Análise</option>
                        <option value="aprovado" {{ request('status') == 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                        <option value="recusado" {{ request('status') == 'recusado' ? 'selected' : '' }}>Recusado</option>
                        <option value="em_processo" {{ request('status') == 'em_processo' ? 'selected' : '' }}>Em Processo</option>
                    </select>
                </div>
                
                <!-- Prefeitura -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Prefeitura</label>
                    <select name="prefeitura_id" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-transparent text-sm">
                        <option value="">Todas as Prefeituras</option>
                        @foreach($prefeituras as $pref)
                        <option value="{{ $pref->id }}" {{ request('prefeitura_id') == $pref->id ? 'selected' : '' }}>{{ $pref->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Período -->
                <div class="lg:col-span-2">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Período de Solicitação</label>
                    <div class="flex space-x-2">
                        <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] text-sm">
                        <span class="flex items-center text-gray-400">a</span>
                        <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] text-sm">
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                @if(count(array_filter($filters)) > 0)
                    <a href="{{ route('admin.etps_recebidos.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all flex items-center">Limpar Filtros</a>
                @endif
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all flex items-center">
                    <i class="mr-2 fas fa-search"></i> Filtrar Resultados
                </button>
            </div>
        </form>
    </div>

    <!-- Tabela -->
    <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-100 shadow-sm rounded-2xl">
        <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <h3 class="text-xl font-semibold text-gray-800">
                Lista Global de ETPs
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full overflow-hidden divide-y divide-gray-200 rounded-lg shadow-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Nº ETP</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Prefeitura / Sec.</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Responsável</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Objeto</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Modo</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Modalidade</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-center text-gray-600 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($etps as $etp)
                    <tr class="transition-colors duration-200 hover:bg-gray-50/80">
                        <td class="px-4 py-3 font-mono text-sm font-bold text-gray-900 whitespace-nowrap">
                            <a href="{{ route('admin.etps_recebidos.show', $etp->id) }}" class="text-[#009496] hover:underline">
                                ETP-{{ str_pad($etp->id, 4, '0', STR_PAD_LEFT) }}/{{ $etp->created_at->format('y') }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div class="font-semibold">{{ $etp->prefeitura->nome ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $etp->secretaria->nome ?? 'N/A' }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $etp->servidor_responsavel ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 cursor-help" title="{{ $etp->objeto_licitacao }}">{{ str()->limit($etp->objeto_licitacao, 30) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 uppercase">
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded border border-gray-300">
                                {{ $etp->tipo_contratacao }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full
                                @if($etp->status === 'pendente') bg-yellow-100 text-yellow-800
                                @elseif($etp->status === 'em_analise') bg-blue-100 text-blue-800
                                @elseif($etp->status === 'aprovado') bg-green-100 text-green-800
                                @elseif($etp->status === 'em_processo') bg-purple-100 text-purple-800
                                @elseif($etp->status === 'recusado') bg-red-100 text-red-800
                                @endif border">
                                {{ ucfirst(str_replace('_', ' ', $etp->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $etp->modalidade }}</td>>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.etps_recebidos.show', $etp->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-white transition-colors duration-200 bg-[#062F43] rounded-md hover:bg-[#065f8b] focus:outline-none" title="Analisar ETP">
                                Analisar <i class="fas fa-arrow-right ml-1 relative top-[1px]"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center text-gray-500">
                            <p class="text-sm font-medium text-gray-700">Nenhum ETP encontrado para os filtros selecionados.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if ($etps->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $etps->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
