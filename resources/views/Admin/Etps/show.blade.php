@extends('layouts.app')
@section('page-title', 'Detalhes do ETP')
@section('page-subtitle', 'Visualização do Estudo Técnico Preliminar')

@section('content')
    <div class="py-8">
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('admin.etps.index') }}"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Voltar para a Lista
            </a>

            <div class="inline-flex items-center px-4 py-2 text-sm font-bold rounded-lg shadow-sm
                    @if($etp->status === 'pendente') bg-yellow-100 text-yellow-800 border-yellow-200
                    @elseif($etp->status === 'em_analise') bg-blue-100 text-blue-800 border-blue-200
                    @elseif($etp->status === 'aprovado') bg-green-100 text-green-800 border-green-200
                    @elseif($etp->status === 'em_processo') bg-purple-100 text-purple-800 border-purple-200
                    @elseif($etp->status === 'recusado') bg-red-100 text-red-800 border-red-200
                    @endif border">
                Status: {{ ucfirst(str_replace('_', ' ', $etp->status)) }}
            </div>
        </div>

        <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
            <div
                class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-file-alt mr-2 text-[#009496]"></i> Solicitação
                    ETP-{{ str_pad($etp->id, 4, '0', STR_PAD_LEFT) }}/{{ $etp->created_at->format('Y') }}
                </h3>
                <span class="text-sm text-gray-500">Criado em {{ $etp->created_at->format('d/m/Y H:i') }}</span>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <!-- Informações Iniciais -->
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-100">
                        <h4 class="text-lg font-medium text-gray-800 mb-4 border-b pb-2">Dados Gerais</h4>
                        <ul class="space-y-3">
                            <li><span
                                    class="text-gray-500 font-medium block text-xs uppercase tracking-wider">Secretaria</span>
                                <span class="text-gray-900 font-semibold">{{ $etp->secretaria->nome ?? 'N/A' }}</span>
                            </li>
                            <li><span
                                    class="text-gray-500 font-medium block text-xs uppercase tracking-wider">Responsável</span>
                                <span class="text-gray-900 font-semibold">{{ $etp->servidor_responsavel ?? 'N/A' }}</span>
                            </li>
                            <li><span
                                    class="text-gray-500 font-medium block text-xs uppercase tracking-wider">Modalidade</span>
                                <span
                                    class="text-gray-900 font-semibold uppercase">{{ $etp->modalidade ?? 'Não informada' }}</span>
                            </li>
                            <li><span class="text-gray-500 font-medium block text-xs uppercase tracking-wider">Dotação
                                    Orçamentária</span> <span
                                    class="text-gray-900 font-semibold">{{ $etp->dotacao_orcamentaria ?? 'Não informada' }}</span>
                            </li>

                            @if(!in_array($etp->modalidade, ['concorrencia', 'inexigibilidade']))
                                <li><span class="text-gray-500 font-medium block text-xs uppercase tracking-wider">Tipo de
                                        Contratação</span> <span
                                        class="text-[#009496] font-bold uppercase">{{ $etp->tipo_contratacao }}</span></li>
                                @if($etp->tipo_contratacao === 'lote')
                                    <li><span class="text-gray-500 font-medium block text-xs uppercase tracking-wider">Nome do
                                            Lote</span> <span class="text-gray-900 font-semibold">{{ $etp->nome_lote }}</span></li>
                                    <li><span class="text-gray-500 font-medium block text-xs uppercase tracking-wider">Prazo de
                                            Entrega</span> <span
                                            class="text-gray-900 font-semibold">{{ $etp->prazo_entrega }}</span></li>
                                @endif
                            @endif
                        </ul>
                    </div>

                    <!-- Objeto -->
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-100">
                        <h4 class="text-lg font-medium text-gray-800 mb-4 border-b pb-2">Objeto da Licitação</h4>
                        <p class="text-gray-700 whitespace-pre-wrap text-sm leading-relaxed">{{ $etp->objeto_licitacao }}
                        </p>
                    </div>
                </div>

                @if(!in_array($etp->modalidade, ['concorrencia', 'inexigibilidade']))
                    <div class="mb-8">
                        <h4 class="text-lg font-medium text-gray-800 mb-4 border-b pb-2"><i
                                class="fas fa-boxes mr-2 text-[#009496]"></i> Itens Solicitados</h4>
                        @if($etp->itens->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left text-gray-500 border border-gray-200 rounded-lg">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 rounded-tl-lg">ID</th>
                                            <th scope="col" class="px-6 py-3 rounded-tr-lg">Descrição do Item</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($etp->itens as $item)
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4 font-medium text-gray-900">{{ $item->id }}</td>
                                                <td class="px-6 py-4">{{ $item->descricao_item }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">Nenhum item vinculado a este ETP.</p>
                        @endif
                    </div>
                @endif

                @if($etp->cotacao_path)
                    @php
                        $extension = pathinfo($etp->cotacao_path, PATHINFO_EXTENSION);
                    @endphp
                    <!-- <div>
                        <h4 class="text-lg font-medium text-gray-800 mb-4 border-b pb-2"><i
                                class="fas fa-paperclip mr-2 text-[#009496]"></i> Anexos</h4>

                        @if(strtolower($extension) === 'pdf')
                            <div class="w-full h-screen max-h-[800px] border border-gray-300 rounded-xl overflow-hidden shadow-sm">
                                <iframe src="{{ Storage::url($etp->cotacao_path) }}" class="w-full h-full" frameborder="0"></iframe>
                            </div>
                        @else
                            <a href="{{ Storage::url($etp->cotacao_path) }}" target="_blank"
                                class="inline-flex items-center px-4 py-3 bg-white border border-gray-300 rounded-lg hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-3">
                                    <i class="fas fa-download"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">Cotação do Fornecedor / Projeto Básico</p>
                                    <p class="text-xs text-gray-500">Clique para abrir o arquivo anexado</p>
                                </div>
                            </a>
                        @endif
                    </div> -->
                @endif

                @if($etp->processo_id)
                    <div class="mt-8 bg-purple-50 p-6 rounded-xl border border-purple-200">
                        <div class="flex items-center">
                            <div
                                class="w-12 h-12 rounded-full bg-purple-200 text-purple-700 flex items-center justify-center mr-4">
                                <i class="fas fa-link text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-purple-900">Vinculado a Processo de Licitação</h4>
                                <p class="text-sm text-purple-800">Este ETP foi aprovado e agora faz parte do Processo Nº <span
                                        class="font-bold">{{ $etp->processo->numero_processo ?? $etp->processo_id }}</span></p>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection