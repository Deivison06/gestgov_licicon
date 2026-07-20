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
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Modalidade</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-center text-gray-600 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($etps as $etp)
                    <tr class="transition-colors duration-200 hover:bg-gray-50/80">
                        <td class="px-4 py-3 font-mono text-sm text-gray-900 whitespace-nowrap align-top">ETP-{{ str_pad($etp->id, 4, '0', STR_PAD_LEFT) }}/{{ $etp->created_at->format('Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 align-top">{{ $etp->secretaria->nome ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 align-top">{{ $etp->servidor_responsavel ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 uppercase align-top">{{ $etp->modalidade }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 align-top">
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full
                                @if($etp->status === 'pendente') bg-yellow-100 text-yellow-800
                                @elseif($etp->status === 'em_analise') bg-blue-100 text-blue-800
                                @elseif($etp->status === 'aprovado') bg-green-100 text-green-800
                                @elseif($etp->status === 'em_processo') bg-purple-100 text-purple-800
                                @elseif($etp->status === 'concluido') bg-teal-100 text-teal-800
                                @elseif($etp->status === 'recusado') bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $etp->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center align-top">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('admin.etps.show', $etp->id) }}" 
                                class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 transition-colors duration-200 rounded-md hover:bg-indigo-100 focus:outline-none" 
                                title="Visualizar ETP">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="{{ route('admin.etps.pdf', $etp->id) }}" 
                                class="inline-flex items-center justify-center w-8 h-8 text-green-600 transition-colors duration-200 rounded-md hover:bg-green-100 focus:outline-none" 
                                title="Baixar PDF do ETP" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                
                                @php
                                    $podeEditar = ($etp->status === 'pendente' || $etp->status === 'recusado') || 
                                                 auth()->user()->hasRole(['diretor_licicon', 'gerente_licicon']);
                                @endphp

                                @if($podeEditar)
                                    <a href="{{ route('admin.etps.edit', $etp->id) }}" 
                                    class="inline-flex items-center justify-center w-8 h-8 text-blue-600 transition-colors duration-200 rounded-md hover:bg-blue-100 focus:outline-none" 
                                    title="Editar ETP">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                
                                @if($etp->status === 'pendente' || $etp->status === 'recusado')
                                    <button type="button" 
                                            onclick="confirmarExclusao('{{ $etp->id }}', 'ETP-{{ str_pad($etp->id, 4, '0', STR_PAD_LEFT) }}/{{ $etp->created_at->format('Y') }}')"
                                            class="inline-flex items-center justify-center w-8 h-8 text-red-600 transition-colors duration-200 rounded-md hover:bg-red-100 focus:outline-none" 
                                            title="Excluir ETP">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    <!-- Linha do Objeto -->
                    <tr class="border-t-0 bg-gray-50/30">
                        <td colspan="7" class="px-4 py-2 text-sm text-gray-600">
                            <div class="flex items-start gap-2">
                                <span class="font-semibold text-gray-700 whitespace-nowrap">Objeto:</span>
                                <span class="text-gray-600" title="{{ $etp->objeto_licitacao }}">{{ $etp->objeto_licitacao }}</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center text-gray-500">
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
<!-- Modal de Confirmação de Exclusão -->
<div id="modalExcluir" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="modal-excluir-overlay"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Confirmar Exclusão
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="mensagem-exclusao">
                                Tem certeza que deseja excluir este ETP? Esta ação não pode ser desfeita.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form id="form-excluir" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Sim, excluir
                    </button>
                </form>
                <button type="button" 
                        onclick="fecharModalExclusao()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarExclusao(id, identificacao) {
    document.getElementById('mensagem-exclusao').innerHTML = 
        `Tem certeza que deseja excluir o ETP <strong>${identificacao}</strong>? Esta ação não pode ser desfeita.`;
    
    document.getElementById('form-excluir').action = '{{ url("admin/etps") }}/' + id;
    
    document.getElementById('modalExcluir').classList.remove('hidden');
}

function fecharModalExclusao() {
    document.getElementById('modalExcluir').classList.add('hidden');
}

// Fechar modal ao clicar no overlay
document.getElementById('modal-excluir-overlay').addEventListener('click', fecharModalExclusao);
</script>
@endsection
