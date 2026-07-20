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

    @if ($pendentesLancamento > 0)
    <a href="{{ route('admin.etps_recebidos.index', ['status' => 'pendente_lancamento']) }}"
        class="block p-4 mb-8 border border-orange-200 shadow-sm rounded-2xl bg-gradient-to-r from-orange-50 to-amber-50 hover:from-orange-100 hover:to-amber-100 transition">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <p class="text-sm font-medium text-orange-800">
                {{ $pendentesLancamento }} {{ $pendentesLancamento === 1 ? 'ETP aprovado está' : 'ETPs aprovados estão' }}
                pendente(s) de lançamento (aprovados, mas ainda não vinculados a um processo). Clique para ver.
            </p>
        </div>
    </a>
    @endif

    <!-- Filtros -->
    <div class="mb-6 bg-white border border-gray-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center cursor-pointer"
            onclick="document.getElementById('filtrosBody').classList.toggle('hidden')">

            <div>
                <h4 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-filter mr-2 text-[#009496]"></i>
                    Filtros de Busca
                </h4>
                <p class="text-sm text-gray-500">Clique para expandir ou recolher os filtros</p>
            </div>

            <i class="fas fa-chevron-down text-gray-400"></i>
        </div>

        <form action="{{ route('admin.etps_recebidos.index') }}" method="GET"
            id="filtrosBody"
            class="p-6 {{ count(array_filter($filters)) > 0 ? '' : 'hidden' }}">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <!-- Status -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Status</label>

                    <select name="status"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] text-sm">

                        <option value="">Todos os Status</option>

                        <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="em_analise" {{ request('status') == 'em_analise' ? 'selected' : '' }}>Em Análise</option>
                        <option value="aprovado" {{ request('status') == 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                        <option value="pendente_lancamento" {{ request('status') == 'pendente_lancamento' ? 'selected' : '' }}>Pendente de Lançamento</option>
                        <option value="recusado" {{ request('status') == 'recusado' ? 'selected' : '' }}>Recusado</option>
                        <option value="em_processo" {{ request('status') == 'em_processo' ? 'selected' : '' }}>Em Processo</option>
                        <option value="concluido" {{ request('status') == 'concluido' ? 'selected' : '' }}>Concluído</option>

                    </select>
                </div>

                <!-- Prefeitura -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Prefeitura</label>

                    <select name="prefeitura_id"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] text-sm">

                        <option value="">Todas as Prefeituras</option>

                        @foreach($prefeituras as $pref)
                        <option value="{{ $pref->id }}"
                            {{ request('prefeitura_id') == $pref->id ? 'selected' : '' }}>
                            {{ $pref->nome }}
                        </option>
                        @endforeach

                    </select>
                </div>

                <!-- Período -->
                <div class="lg:col-span-2">

                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Período de Solicitação
                    </label>

                    <div class="flex space-x-2">

                        <input type="date"
                            name="data_inicio"
                            value="{{ request('data_inicio') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] text-sm">

                        <span class="flex items-center text-gray-400">a</span>

                        <input type="date"
                            name="data_fim"
                            value="{{ request('data_fim') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] text-sm">

                    </div>
                </div>

            </div>

            <div class="mt-6 flex justify-end space-x-3">

                @if(count(array_filter($filters)) > 0)
                <a href="{{ route('admin.etps_recebidos.index') }}"
                    class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Limpar Filtros
                </a>
                @endif

                <button type="submit"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a]">

                    <i class="mr-2 fas fa-search"></i>
                    Filtrar Resultados

                </button>

            </div>
        </form>
    </div>


    <!-- TABELA -->
    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">

        <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <h3 class="text-xl font-semibold text-gray-800">
                Lista Global de ETPs
            </h3>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr>

                        <th class="px-4 py-3 text-xs font-semibold text-left text-gray-600 uppercase">
                            Nº ETP
                        </th>

                        <th class="px-4 py-3 text-xs font-semibold text-left text-gray-600 uppercase">
                            Prefeitura / Secretaria
                        </th>

                        <th class="px-4 py-3 text-xs font-semibold text-left text-gray-600 uppercase">
                            Responsável
                        </th>

                        <th class="px-4 py-3 text-xs font-semibold text-left text-gray-600 uppercase">
                            Modo
                        </th>

                        <th class="px-4 py-3 text-xs font-semibold text-left text-gray-600 uppercase">
                            Status
                        </th>

                        <th class="px-4 py-3 text-xs font-semibold text-left text-gray-600 uppercase">
                            Modalidade
                        </th>

                        <th class="px-4 py-3 text-xs font-semibold text-center text-gray-600 uppercase">
                            Ações
                        </th>

                    </tr>

                </thead>

                <tbody class="bg-white divide-y divide-gray-200">

                    @forelse($etps as $etp)

                    <!-- LINHA PRINCIPAL -->

                    <tr class="hover:bg-gray-50">

                        <td class="px-4 py-3 font-mono text-sm font-bold">

                            <a href="{{ route('admin.etps_recebidos.show', $etp->id) }}"
                                class="text-[#009496] hover:underline">

                                ETP-{{ str_pad($etp->id, 4, '0', STR_PAD_LEFT) }}/{{ $etp->created_at->format('y') }}

                            </a>

                        </td>

                        <td class="px-4 py-3 text-sm">

                            <div class="font-semibold">
                                {{ $etp->prefeitura->nome ?? 'N/A' }}
                            </div>

                            <div class="text-xs text-gray-500">
                                {{ $etp->secretaria->nome ?? 'N/A' }}
                            </div>

                        </td>

                        <td class="px-4 py-3 text-sm">
                            {{ $etp->servidor_responsavel ?? 'N/A' }}
                        </td>

                        <td class="px-4 py-3 text-sm uppercase">

                            <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded border">

                                {{ $etp->tipo_contratacao }}

                            </span>

                        </td>

                        <td class="px-4 py-3 text-sm">

                            <span class="px-2 py-1 text-xs font-semibold rounded-full

                                @if($etp->status === 'pendente') bg-yellow-100 text-yellow-800
                                @elseif($etp->status === 'em_analise') bg-blue-100 text-blue-800
                                @elseif($etp->status === 'aprovado') bg-green-100 text-green-800
                                @elseif($etp->status === 'em_processo') bg-purple-100 text-purple-800
                                @elseif($etp->status === 'concluido') bg-teal-100 text-teal-800
                                @elseif($etp->status === 'recusado') bg-red-100 text-red-800
                                @endif">

                                {{ ucfirst(str_replace('_', ' ', $etp->status)) }}

                            </span>

                            @if($etp->status === 'aprovado' && is_null($etp->processo_id))
                            <span class="ml-1 px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                Pendente de Lançamento
                            </span>
                            @endif

                        </td>

                        <td class="px-4 py-3 text-sm">
                            {{ $etp->modalidade }}
                        </td>

                        <td class="px-4 py-3 text-center space-y-1">

                            <a href="{{ route('admin.etps_recebidos.show', $etp->id) }}"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-[#062F43] rounded-md hover:bg-[#065f8b]">

                                Analisar
                                <i class="fas fa-arrow-right ml-1"></i>

                            </a>

                            @if($etp->status === 'aprovado' && is_null($etp->processo_id))

                            <a href="{{ route('admin.processos.create', ['etp_id' => $etp->id]) }}"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-orange-500 rounded-md hover:bg-orange-600">

                                <i class="mr-1 fas fa-plus"></i>
                                Criar Processo

                            </a>

                            <form action="{{ route('admin.etps_recebidos.status', $etp->id) }}" method="POST" class="inline"
                                onsubmit="return confirm('Confirmar conclusão manual deste ETP? Ele será encerrado sem ser vinculado a um processo.');">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="concluido">
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-teal-600 rounded-md hover:bg-teal-700">
                                    <i class="mr-1 fas fa-flag-checkered"></i>
                                    Concluir
                                </button>
                            </form>

                            @elseif(!is_null($etp->processo_id))

                            <a href="{{ route('admin.processos.show', $etp->processo_id) }}"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-[#009496] rounded-md hover:bg-[#007a7a]">

                                <i class="mr-1 fas fa-eye"></i>
                                Ver Processo

                            </a>

                            @endif

                        </td>

                    </tr>

                    <!-- LINHA DO OBJETO -->

                    <tr class="bg-gray-50">

                        <td colspan="7" class="px-6 py-3 text-sm text-gray-700">

                            <span class="font-semibold text-gray-600">
                                Objeto:
                            </span>

                            {{ $etp->objeto_licitacao }}

                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center text-gray-500">
                            Nenhum ETP encontrado
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if ($etps->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-center">
            {{ $etps->withQueryString()->links('pagination::tailwind') }}
        </div>
        @endif

    </div>

</div>
@endsection
