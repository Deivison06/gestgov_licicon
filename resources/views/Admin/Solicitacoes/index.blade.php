@extends('layouts.app')
@section('page-title', 'Solicitações Internas')
@section('page-subtitle', 'Comunicação formal entre prefeituras e a administração')

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

    <div class="flex justify-end mb-6">
        <a href="{{ route('admin.solicitacoes.create') }}" class="inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-[#052323] to-[#052323] rounded-xl hover:shadow-lg hover:scale-105">
            <i class="fas fa-plus"></i> Nova Solicitação
        </a>
    </div>

    @php
        $grouped = $solicitacoes->groupBy('prefeitura_id');
    @endphp

    @forelse($grouped as $prefeituraId => $tickets)
        @php $prefeitura = $tickets->first()->prefeitura; @endphp
        
        <div class="mb-10">
            @if(!auth()->user()->hasRole('prefeitura'))
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-indigo-100 rounded-lg text-indigo-700">
                        <i class="fas fa-building text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 uppercase tracking-tight">{{ $prefeitura->nome }}</h2>
                        <span class="text-xs text-gray-500">{{ $tickets->count() }} solicitações nesta listagem</span>
                    </div>
                </div>
            @endif

            <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-100 shadow-sm rounded-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full overflow-hidden divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Status</th>
                                <th class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Assunto</th>
                                <th class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Tipo</th>
                                <th class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Solicitante</th>
                                <th class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Última Atualização</th>
                                <th class="px-6 py-4 text-xs font-semibold tracking-wider text-center text-gray-600 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($tickets as $solicitacao)
                            <tr class="transition-colors duration-200 hover:bg-gray-50/80">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-full
                                        @if($solicitacao->status === 'aberta') bg-blue-100 text-blue-700
                                        @elseif($solicitacao->status === 'recebida') bg-green-100 text-green-700
                                        @elseif($solicitacao->status === 'aguardando_resposta') bg-yellow-100 text-yellow-700
                                        @elseif($solicitacao->status === 'finalizada') bg-gray-100 text-gray-700
                                        @endif shadow-sm border border-black/5 uppercase">
                                        {{ str_replace('_', ' ', $solicitacao->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ $solicitacao->assunto }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600 capitalize">
                                        @if($solicitacao->tipo === 'correcao')
                                            Correção
                                        @elseif($solicitacao->tipo === 'reclamacao')
                                            Reclamação
                                        @elseif($solicitacao->tipo === 'outros')
                                            Outros
                                        @endif
                                    </span>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-8 h-8 mr-3 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold text-xs">
                                            {{ strtoupper(substr($solicitacao->usuario->name, 0, 2)) }}
                                        </div>
                                        <div class="text-sm text-gray-800">{{ $solicitacao->usuario->name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $solicitacao->updated_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <a href="{{ route('admin.solicitacoes.show', $solicitacao->id) }}" 
                                       class="inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-[#052323] to-[#052323] rounded-xl hover:shadow-lg hover:scale-105">
                                        <i class="fas fa-comments mr-2"></i> Abrir
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-16 text-center text-gray-500">
            <i class="fas fa-inbox text-4xl mb-4 text-gray-200"></i>
            <p class="text-sm font-medium text-gray-700">Nenhuma solicitação encontrada.</p>
        </div>
    @endforelse

    @if ($solicitacoes->hasPages())
    <div class="mt-6">
        {{ $solicitacoes->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
