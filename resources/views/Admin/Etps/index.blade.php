@extends('layouts.app')
@section('page-title', 'Meus ETPs')
@section('page-subtitle', 'Solicitações de Estudo Técnico Preliminar da sua Secretaria')

@section('content')
<div class="py-8">
    <div class="flex justify-end mb-8">
        <a href="{{ route('admin.etps.create') }}" class="inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-[#052323] to-[#052323] rounded-xl hover:shadow-lg hover:scale-105">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Criar Novo ETP
        </a>
    </div>

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

    <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-100 shadow-sm rounded-2xl">
        <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <h3 class="text-xl font-semibold text-gray-800">
                Lista de Solicitações ETP
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full overflow-hidden divide-y divide-gray-200 rounded-lg shadow-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Nº</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Secretaria</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Responsável</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Objeto</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Data</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-center text-gray-600 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($etps as $etp)
                    <tr class="transition-colors duration-200 hover:bg-gray-50/80">
                        <td class="px-4 py-3 font-mono text-sm text-gray-900 whitespace-nowrap">ETP-{{ str_pad($etp->id, 4, '0', STR_PAD_LEFT) }}/{{ $etp->created_at->format('Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $etp->secretaria->nome ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $etp->responsavel->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 cursor-help" title="{{ $etp->objeto_licitacao }}">{{ str()->limit($etp->objeto_licitacao, 40) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 uppercase">{{ $etp->tipo_contratacao }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full
                                @if($etp->status === 'pendente') bg-yellow-100 text-yellow-800
                                @elseif($etp->status === 'em_analise') bg-blue-100 text-blue-800
                                @elseif($etp->status === 'aprovado') bg-green-100 text-green-800
                                @elseif($etp->status === 'em_processo') bg-purple-100 text-purple-800
                                @elseif($etp->status === 'recusado') bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $etp->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $etp->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.etps.show', $etp->id) }}" class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 transition-colors duration-200 rounded-md hover:bg-indigo-100 focus:outline-none" title="Visualizar ETP">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center text-gray-500">
                            <p class="text-sm font-medium text-gray-700">Nenhum ETP encontrado.</p>
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
