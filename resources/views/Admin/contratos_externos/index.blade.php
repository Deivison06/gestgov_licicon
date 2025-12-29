@extends('layouts.app')

@section('page-title', 'Gestão de Contratos')
@section('page-subtitle', 'Gerencie os contratos e processos licitatórios')

@section('content')

{{-- Mensagem de Sucesso --}}
@if(session('success'))
<div class="flex items-center p-4 mb-6 text-green-700 border border-green-200 rounded-lg bg-green-50">
    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    {{ session('success') }}
</div>
@endif

{{-- Filtros --}}
<div class="p-6 mb-6 bg-white border border-gray-100 shadow-sm rounded-2xl">
    <h3 class="mb-4 text-lg font-semibold text-gray-900">Filtrar Contratos</h3>
    <form method="GET" action="{{ route('admin.contratos.index') }}" class="flex flex-col gap-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="prefeitura_id" class="block mb-1 text-sm font-medium text-gray-700">
                    Prefeitura
                </label>
                <select name="prefeitura_id" id="prefeitura_id" class="w-full px-4 py-2 transition-colors duration-200 border border-gray-300 rounded-lg focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="">Todas as Prefeituras</option>
                    @foreach($prefeituras as $prefeitura)
                        <option value="{{ $prefeitura->id }}" {{ request('prefeitura_id') == $prefeitura->id ? 'selected' : '' }}>
                            {{ $prefeitura->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filtro por Empresa --}}
            <div>
                <label for="empresa_id" class="block mb-1 text-sm font-medium text-gray-700">
                    Empresa Contratada
                </label>
                <select name="empresa_id" id="empresa_id" class="w-full px-4 py-2 transition-colors duration-200 border border-gray-300 rounded-lg focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="">Todas as Empresas</option>
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                            {{ $empresa->razao_social }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Botões --}}
        <div class="flex flex-shrink-0 gap-2">
            <button type="submit" class="px-4 py-2 text-white transition-colors duration-200 rounded-lg bg-cyan-600 hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 whitespace-nowrap">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
            <a href="{{ route('admin.contratos.index') }}" class="px-4 py-2 text-center text-gray-700 transition-colors duration-200 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 whitespace-nowrap">
                <i class="fas fa-times mr-1"></i> Limpar
            </a>
        </div>
    </form>
</div>

{{-- Card Principal --}}
<div class="overflow-hidden bg-white shadow-sm rounded-xl">

    <div class="px-6 py-4 border-b border-gray-100 bg-[#dafafa] flex items-center justify-between">
        <h3 class="text-2xl font-bold text-gray-700">Lista de Contratos</h3>
        <a href="{{ route('admin.contratos.create') }}" class="inline-flex items-center justify-center gap-3 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-[#062F43] to-[#07405c] rounded-xl hover:from-[#07405c] hover:to-[#062F43] hover:shadow-lg hover:scale-105">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Novo Contrato
        </a>
    </div>

    <div class="overflow-x-auto">
        <div class="inline-block min-w-full align-middle">
            <table class="min-w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-white text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        @if(auth()->user()->hasRole('admin'))
                        <th class="px-6 py-4">Prefeitura</th>
                        @endif
                        <th class="px-6 py-4">Empresa</th>
                        <th class="px-6 py-4">Contratante</th>
                        <th class="px-6 py-4">Modalidade / Tipo</th>
                        <th class="px-6 py-4">Processo / Contrato</th>
                        <th class="px-6 py-4 text-center">Vigência</th>
                        <th class="px-6 py-4 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($contratos as $contrato)
                        <tr class="hover:bg-gray-50 transition-colors duration-150 group">
                            {{-- Prefeitura (só mostra para admin) --}}
                            @if(auth()->user()->hasRole('admin'))
                            <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium text-gray-900 max-w-[150px] truncate" title="{{ $contrato->prefeitura->nome ?? 'N/A' }}">
                                    {{ $contrato->prefeitura->nome ?? '-' }}
                                </div>
                            </td>
                            @endif

                            {{-- Empresa --}}
                            <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium text-gray-900 max-w-[200px] truncate" title="{{ $contrato->empresa->razao_social ?? 'N/A' }}">
                                    {{ $contrato->empresa->razao_social ?? 'Empresa não vinculada' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    CNPJ: {{ $contrato->empresa->cnpj_formatado ?? '-' }}
                                </div>
                            </td>

                            {{-- Contratante (Secretaria) --}}
                            <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm text-gray-900">
                                <div class="max-w-[180px] truncate" title="{{ $contrato->secretaria->nome ?? '' }}">
                                    {{ $contrato->secretaria->nome ?? 'Não Informado' }}
                                </div>
                            </td>

                            {{-- Modalidade e Tipo --}}
                            <td class="px-6 pt-5 pb-3 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs font-semibold text-gray-700 uppercase">
                                        {{ $contrato->modalidade ? $contrato->modalidade->getDisplayName() : 'N/A' }}
                                    </span>
                                    {{-- Badge do Tipo de Contrato --}}
                                    <span class="inline-flex w-fit items-center px-2 py-0.5 rounded text-xs font-medium {{ $contrato->tipo_contrato == 'Serviço' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $contrato->tipo_contrato }}
                                    </span>
                                </div>
                            </td>

                            {{-- Números --}}
                            <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-bold text-gray-800">Proc: {{ $contrato->numero_processo }}</div>
                                @if($contrato->numero_contrato)
                                    <div class="text-xs text-gray-500">Contr: {{ $contrato->numero_contrato }}</div>
                                @endif
                            </td>

                            {{-- Status e Vigência --}}
                            <td class="px-6 pt-5 pb-3 whitespace-nowrap text-center">
                                @php
                                    // Definição de Cores Simples baseada no Status
                                    $statusColors = [
                                        'VIGENTE' => 'bg-green-100 text-green-800',
                                        'VENCIDO' => 'bg-red-100 text-red-800',
                                        'PENDENTE' => 'bg-yellow-100 text-yellow-800',
                                    ];
                                    $cor = $statusColors[$contrato->situacao] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cor }}">
                                    {{ $contrato->situacao }}
                                </span>
                                <div class="text-xs text-gray-500 mt-1">
                                    Fim: {{ $contrato->data_finalizacao ? $contrato->data_finalizacao->format('d/m/Y') : '-' }}
                                </div>
                            </td>

                            {{-- Ações --}}
                            <td class="px-6 pt-5 pb-3 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('admin.contratos.edit', $contrato->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white transition-colors duration-200 bg-amber-500 rounded-lg hover:bg-amber-600 shadow-sm">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>

                                    <form action="{{ route('admin.contratos.destroy', $contrato->id) }}" id="delete-form{{$contrato->id}}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                onclick="confirmDelete{{$contrato->id}}()"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white transition-colors duration-200 bg-red-600 rounded-lg hover:bg-red-700 shadow-sm">
                                            <i class="fas fa-trash-alt"></i> Excluir
                                        </button>
                                    </form>
                                </div>

                                <script>
                                    function confirmDelete{{$contrato->id}}() {
                                        Swal.fire({
                                            title: 'Excluir Contrato?',
                                            text: "Você excluirá este contrato! Esta ação não pode ser desfeita.",
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#d33',
                                            cancelButtonColor: '#3085d6',
                                            confirmButtonText: 'Sim, excluir!',
                                            cancelButtonText: 'Cancelar',
                                            reverseButtons: true
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                document.getElementById('delete-form{{$contrato->id}}').submit();
                                            }
                                        });
                                    }
                                </script>
                            </td>
                        </tr>

                        {{-- Linha do Objeto --}}
                        <tr class="border-b border-gray-200">
                            @php
                                $colspan = auth()->user()->hasRole('admin') ? 7 : 6;
                            @endphp
                            <td colspan="{{ $colspan }}" class="bg-[#F8FAFC] px-6 py-3 text-sm text-gray-600 leading-relaxed whitespace-normal">
                                <div class="flex items-start gap-2">
                                    <i class="fas fa-info-circle text-gray-400 mt-0.5 flex-shrink-0"></i>
                                    <div class="min-w-0 break-words">
                                        <span class="font-bold text-gray-800 text-xs uppercase mr-1">Objeto:</span>
                                        <span class="italic">{{ $contrato->objeto }}</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            @php
                                $colspan = auth()->user()->hasRole('admin') ? 7 : 6;
                            @endphp
                            <td colspan="{{ $colspan }}" class="px-6 py-10 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-file-contract text-4xl text-gray-300 mb-3"></i>
                                    <p class="font-medium">Nenhum contrato encontrado.</p>
                                    <p class="text-sm mt-1">Clique em "Novo Contrato" para começar.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginação --}}
    @if($contratos->hasPages())
    <div class="px-4 py-4 border-t border-gray-200 bg-gray-50 sm:px-6">
        {{ $contratos->appends(['prefeitura_id' => request('prefeitura_id'), 'empresa_id' => request('empresa_id')])->links() }}
    </div>
    @endif
</div>

@endsection