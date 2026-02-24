@extends('layouts.app')
@section('page-title', 'Análise do ETP')
@section('page-subtitle', 'Revisão, aprovação e vinculação de Estudo Técnico Preliminar')

@section('content')
<div class="py-8">
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.etps_recebidos.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar para a Lista
        </a>
        
        <div class="inline-flex items-center px-4 py-2 text-sm font-bold rounded-lg shadow-sm border
            @if($etp->status === 'pendente') bg-yellow-100 text-yellow-800 border-yellow-200
            @elseif($etp->status === 'em_analise') bg-blue-100 text-blue-800 border-blue-200
            @elseif($etp->status === 'aprovado') bg-green-100 text-green-800 border-green-200
            @elseif($etp->status === 'em_processo') bg-purple-100 text-purple-800 border-purple-200
            @elseif($etp->status === 'recusado') bg-red-100 text-red-800 border-red-200
            @endif">
            <i class="fas @if($etp->status === 'aprovado' || $etp->status === 'em_processo') fa-check-circle @elseif($etp->status === 'recusado') fa-times-circle @else fa-clock @endif mr-2"></i>
            Status Atual: {{ ucfirst(str_replace('_', ' ', $etp->status)) }}
        </div>
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

    <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 flex justify-between items-center">
            <h3 class="text-xl font-semibold text-gray-800">
                <i class="fas fa-file-alt mr-2 text-[#009496]"></i> ETP-{{ str_pad($etp->id, 4, '0', STR_PAD_LEFT) }}/{{ $etp->created_at->format('Y') }}
            </h3>
            
            <div class="flex space-x-2">
                @if(in_array($etp->status, ['pendente', 'em_analise', 'recusado']))
                    <form action="{{ route('admin.etps_recebidos.approve', $etp->id) }}" method="POST" class="inline" onsubmit="return confirm('Confirmar aprovação deste ETP?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all flex items-center shadow-sm">
                            <i class="fas fa-check mr-2"></i> Aprovar
                        </button>
                    </form>
                @endif
                
                @if(in_array($etp->status, ['pendente', 'em_analise', 'aprovado']))
                    <form action="{{ route('admin.etps_recebidos.reject', $etp->id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja recusar este ETP?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-all flex items-center shadow-sm">
                            <i class="fas fa-times mr-2"></i> Recusar
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="p-8 border-b border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Informações Iniciais -->
                <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 shadow-sm">
                    <h4 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2"><i class="fas fa-info-circle mr-2 text-[#009496]"></i> Dados Gerais</h4>
                    <ul class="space-y-4">
                        <li>
                            <span class="text-gray-500 font-medium block text-xs uppercase tracking-wider mb-1">Prefeitura / Licitante</span> 
                            <span class="text-gray-900 font-bold text-base">{{ $etp->prefeitura->nome ?? 'N/A' }}</span>
                        </li>
                        <li>
                            <span class="text-gray-500 font-medium block text-xs uppercase tracking-wider mb-1">Secretaria Solicitante</span> 
                            <span class="text-gray-900 font-semibold">{{ $etp->secretaria->nome ?? 'N/A' }}</span>
                        </li>
                        <li>
                            <span class="text-gray-500 font-medium block text-xs uppercase tracking-wider mb-1">Responsável pela Solicitação</span> 
                            <span class="text-gray-900 font-semibold flex items-center">
                                <i class="fas fa-user-circle text-gray-400 mr-2 text-lg"></i> {{ $etp->responsavel->name ?? 'N/A' }}
                            </span>
                        </li>
                        <li class="pt-3 border-t border-gray-200">
                            <span class="text-gray-500 font-medium block text-xs uppercase tracking-wider mb-1">Tipo de Contratação</span> 
                            <span class="inline-block bg-[#009496]/10 text-[#009496] px-3 py-1 rounded-md font-bold uppercase text-sm border border-[#009496]/20">{{ $etp->tipo_contratacao }}</span>
                        </li>
                        @if($etp->tipo_contratacao === 'lote')
                            <li>
                                <span class="text-gray-500 font-medium block text-xs uppercase tracking-wider mb-1">Nome do Lote</span> 
                                <span class="text-gray-900 font-semibold">{{ $etp->nome_lote }}</span>
                            </li>
                            <li>
                                <span class="text-gray-500 font-medium block text-xs uppercase tracking-wider mb-1">Prazo de Entrega Estimado</span> 
                                <span class="text-gray-900 font-semibold bg-white px-2 py-1 rounded border border-gray-200 text-sm"><i class="far fa-calendar-alt mr-1 text-gray-400"></i> {{ $etp->prazo_entrega }}</span>
                            </li>
                        @endif
                    </ul>
                </div>
                
                <!-- Objeto -->
                <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col">
                    <h4 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2"><i class="fas fa-align-left mr-2 text-[#009496]"></i> Objeto da Licitação</h4>
                    <div class="flex-grow bg-white p-4 rounded-lg border border-gray-200 overflow-y-auto max-h-[300px]">
                        <p class="text-gray-700 whitespace-pre-wrap text-sm leading-relaxed">{{ $etp->objeto_licitacao }}</p>
                    </div>
                </div>
            </div>

            @if($etp->tipo_contratacao === 'lote')
            <div class="mb-8 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <h4 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2"><i class="fas fa-boxes mr-2 text-[#009496]"></i> Composição do Lote</h4>
                @if($etp->itens->count() > 0)
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-bold text-gray-800 w-24">ID do Item</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-gray-800">Descrição Detalhada do Item</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($etp->itens as $item)
                                <tr class="bg-white hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-mono font-medium text-gray-900 bg-gray-50">{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-6 py-4 text-gray-800 font-medium">{{ $item->descricao_item }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700 font-medium">Nenhum item vinculado a este ETP de lote.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            
            @if($etp->cotacao_path)
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm mb-8">
                <h4 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2"><i class="fas fa-paperclip mr-2 text-[#009496]"></i> Documentos Anexos</h4>
                <a href="{{ Storage::url($etp->cotacao_path) }}" target="_blank" class="inline-flex items-center px-4 py-3 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center mr-4 shadow-sm group-hover:scale-110 transition-transform">
                        <i class="fas fa-download text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-blue-900 mb-0.5">Cotação do Fornecedor Local</p>
                        <p class="text-xs text-blue-700 font-medium">Visualizar arquivo original submetido pela Secretaria</p>
                    </div>
                </a>
            </div>
            @endif
            @endif
            
            @if($etp->processo_id)
            <div class="mt-8 bg-purple-50 p-6 rounded-xl border border-purple-200 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 opacity-10">
                    <i class="fas fa-link text-9xl text-purple-900"></i>
                </div>
                <div class="flex items-center relative z-10">
                    <div class="w-16 h-16 rounded-full bg-purple-600 text-white flex items-center justify-center mr-5 shadow-md">
                        <i class="fas fa-link text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-purple-900 mb-1">Vinculado a Processo de Licitação</h4>
                        <p class="text-sm text-purple-800 mb-3">Este ETP já foi convertido em um processo de licitação ativo.</p>
                        <div class="bg-white px-4 py-2 rounded-lg border border-purple-200 inline-block">
                            <span class="text-purple-600 text-xs font-bold uppercase tracking-wider block mb-1">Processo Nº</span>
                            <span class="text-gray-900 font-mono font-bold">{{ $etp->processo->numero_processo ?? $etp->processo_id }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @else
                <!-- Ações de Vinculação de Processo -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <h4 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2"><i class="fas fa-cogs mr-2 text-indigo-600"></i> Ações do Sistema</h4>
                    
                    @if($etp->status === 'aprovado')
                        <div class="bg-indigo-50 border border-indigo-100 p-6 rounded-xl shadow-sm">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div>
                                    <h5 class="font-bold text-indigo-900 text-lg mb-1">Criar Processo Licitatório</h5>
                                    <p class="text-sm text-indigo-700">Este ETP está aprovado e pronto para ser transformado em um processo formal de licitação.</p>
                                </div>
                                
                                <form action="{{ route('admin.etps_recebidos.link_process', $etp->id) }}" method="POST" class="flex-shrink-0" id="linkProcessForm">
                                    @csrf
                                    <input type="hidden" name="processo_id" id="processo_id_input">
                                    <button type="button" onclick="openLinkProcessModal()" class="px-6 py-3 font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 hover:shadow-lg transition-all flex items-center transform hover:-translate-y-0.5" title="Vincular a um novo Processo">
                                        <i class="fas fa-plus-circle mr-2"></i> Iniciar Criação
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 border border-gray-200 p-6 rounded-xl shadow-sm text-center">
                            <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-lock text-gray-500 text-lg"></i>
                            </div>
                            <h5 class="font-bold text-gray-800 mb-1">Ações Bloqueadas</h5>
                            <p class="text-sm text-gray-500 max-w-lg mx-auto">A criação vinculada de um Processo Licitatório só estará disponível quando o status deste ETP for alterado para <span class="font-bold text-green-600 relative bottom-px">Aprovado</span>.</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Vincular Processo -->
@if($etp->status === 'aprovado' && !$etp->processo_id)
<div id="modal-link" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-60 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col transform transition-all scale-95 opacity-0" id="modal-link-content">
        <div class="p-6 border-b border-gray-100 bg-indigo-50 flex justify-between items-center">
            <h3 class="text-xl font-bold text-indigo-900 flex items-center">
                <i class="fas fa-link mr-3 text-indigo-600 bg-white p-2 rounded-full shadow-sm"></i> 
                Vincular a um Processo
            </h3>
            <button onclick="closeLinkProcessModal()" class="text-gray-400 hover:text-red-500 bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-sm focus:outline-none transition-colors duration-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-8">
            <div class="mb-6 bg-blue-50 p-4 rounded-lg border border-blue-100 flex items-start">
                <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                <p class="text-sm text-blue-800 leading-relaxed font-medium">Ao criar um novo processo a partir deste botão, o sistema redirecionará você para a tela de criação padrão de processos, onde você deve preencher o restante dos dados da licitação pertinentes à Prefeitura <strong>{{ $etp->prefeitura->nome }}</strong>.</p>
            </div>
            
            <p class="text-sm text-gray-700 mb-6 pb-6 border-b border-gray-100 text-center font-medium">
                Confirma a intenção de iniciar um novo processo para o <strong class="text-gray-900 text-base">ETP-{{ str_pad($etp->id, 4, '0', STR_PAD_LEFT) }}</strong>?
            </p>
            
            <div class="flex justify-center md:space-x-4 flex-col md:flex-row space-y-3 md:space-y-0">
                <button type="button" onclick="closeLinkProcessModal()" class="px-6 py-2.5 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all shadow-sm w-full md:w-auto">
                    Não, Voltar
                </button>
                <button type="button" onclick="submitLinkProcessForm()" class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 transition-all shadow-md w-full md:w-auto">
                    Sim, Criar Processo <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openLinkProcessModal() {
        const modal = document.getElementById('modal-link');
        const content = document.getElementById('modal-link-content');
        
        modal.classList.remove('hidden');
        // Small delay to allow display:block to apply before animating opacity/transform
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }
    
    function closeLinkProcessModal() {
        const modal = document.getElementById('modal-link');
        const content = document.getElementById('modal-link-content');
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300); // Wait for transition to finish
    }
    
    function submitLinkProcessForm() {
        // Redireciona para o `ProcessoController@create` passando os atributos do ETP na URL
        const etpId = '{{ $etp->id }}';
        const prefId = '{{ $etp->prefeitura_id }}';
        const obj = encodeURIComponent('{{ addslashes($etp->objeto_licitacao) }}');
        
        // Vamos marcar no form
        document.getElementById('linkProcessForm').action = "{{ route('admin.processos.create') }}?etp_id=" + etpId + "&prefeitura_id=" + prefId + "&objeto=" + obj;
        
        // Submete normalmente via GET
        window.location.href = document.getElementById('linkProcessForm').action;
    }
</script>
@endif

@endsection
