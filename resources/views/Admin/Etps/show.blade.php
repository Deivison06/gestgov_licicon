@extends('layouts.app')
@section('page-title', 'Detalhes do ETP')
@section('page-subtitle', 'Visualização do Estudo Técnico Preliminar')

@section('content')
    <div class="py-8">
        <div class="mb-6 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.etps.index') }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                        </path>
                    </svg>
                    Voltar para a Lista
                </a>

                @php
                    $podeEditar = ($etp->status === 'pendente' || $etp->status === 'recusado') || 
                                 auth()->user()->hasRole(['diretor_licicon', 'gerente_licicon']);
                @endphp

                @if($podeEditar)
                    <a href="{{ route('admin.etps.edit', $etp->id) }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-all duration-200 shadow-sm">
                        <i class="fas fa-edit mr-2"></i>
                        Editar ETP
                    </a>
                @endif
            </div>

            <div
                class="inline-flex items-center px-4 py-2 text-sm font-bold rounded-lg shadow-sm border
                @if ($etp->status === 'pendente') bg-yellow-100 text-yellow-800 border-yellow-200
                @elseif($etp->status === 'em_analise') bg-blue-100 text-blue-800 border-blue-200
                @elseif($etp->status === 'aprovado') bg-green-100 text-green-800 border-green-200
                @elseif($etp->status === 'em_processo') bg-purple-100 text-purple-800 border-purple-200
                @elseif($etp->status === 'recusado') bg-red-100 text-red-800 border-red-200 @endif">
                <i
                    class="fas @if ($etp->status === 'aprovado' || $etp->status === 'em_processo') fa-check-circle @elseif($etp->status === 'recusado') fa-times-circle @else fa-clock @endif mr-2"></i>
                Status Atual: {{ ucfirst(str_replace('_', ' ', $etp->status)) }}
            </div>
        </div>

        <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden mb-8">
            <div
                class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-file-alt mr-2 text-[#009496]"></i> Solicitação
                    ETP-{{ str_pad($etp->id, 4, '0', STR_PAD_LEFT) }}/{{ $etp->created_at->format('Y') }}
                </h3>
                <span class="text-sm text-gray-500">Criado em {{ $etp->created_at->format('d/m/Y H:i') }}</span>
            </div>

            <div class="p-8 border-b border-gray-100">
                @if($etp->status === 'recusado' && $etp->motivo_recusa)
                    <div class="mb-8 p-6 bg-red-50 border-l-4 border-red-500 rounded-lg shadow-sm">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-500 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-bold text-red-800">Sua Solicitação foi Recusada</h3>
                                <div class="mt-2 text-red-700 text-sm leading-relaxed">
                                    <p class="font-semibold mb-1">Motivo informado pela análise:</p>
                                    <p class="whitespace-pre-wrap">{{ $etp->motivo_recusa }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <!-- Informações Iniciais -->
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 shadow-sm">
                        <h4 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2"><i
                                class="fas fa-info-circle mr-2 text-[#009496]"></i> Dados Gerais</h4>
                        <ul class="space-y-4">
                            <li>
                                <span
                                    class="text-gray-500 font-medium block text-xs uppercase tracking-wider mb-1">Secretaria</span>
                                <span class="text-gray-900 font-bold text-base">{{ $etp->secretaria->nome ?? 'N/A' }}</span>
                            </li>
                            <li>
                                <span
                                    class="text-gray-500 font-medium block text-xs uppercase tracking-wider mb-1">Responsável</span>
                                <span class="text-gray-900 font-semibold flex items-center">
                                    <i class="fas fa-user-circle text-gray-400 mr-2 text-lg"></i>
                                    {{ $etp->servidor_responsavel ?? 'N/A' }}
                                </span>
                            </li>
                            <li class="pt-3 border-t border-gray-200">
                                <span
                                    class="text-gray-500 font-medium block text-xs uppercase tracking-wider mb-1">Modalidade</span>
                                <span
                                    class="inline-block bg-purple-100 text-purple-800 px-3 py-1 rounded-md font-bold uppercase text-sm">{{ $etp->modalidade ?? 'Não informada' }}</span>
                            </li>
                            <li class="pt-3 border-t border-gray-200">
                                <span class="text-gray-500 font-medium block text-xs uppercase tracking-wider mb-1">Dotação Orçamentária</span>
                                <span
                                    class="text-gray-900 font-semibold bg-white px-2 py-1 rounded border border-gray-200 text-sm">{{ $etp->dotacao_orcamentaria ?? 'Não informada' }}</span>
                            </li>

                            @if(!in_array($etp->modalidade, ['concorrencia', 'inexigibilidade']))
                                <li class="pt-3 border-t border-gray-200">
                                    <span class="text-gray-500 font-medium block text-xs uppercase tracking-wider mb-1">Tipo de
                                        Contratação</span>
                                    <span
                                        class="inline-block bg-[#009496]/10 text-[#009496] px-3 py-1 rounded-md font-bold uppercase text-sm border border-[#009496]/20">{{ $etp->tipo_contratacao }}</span>
                                </li>
                                <li>
                                    <span class="text-gray-500 font-medium block text-xs uppercase tracking-wider mb-1">Prazo de
                                        Entrega</span>
                                    <span class="text-gray-900 font-semibold bg-white px-2 py-1 rounded border border-gray-200 text-sm"><i
                                        class="far fa-calendar-alt mr-1 text-gray-400"></i>
                                        {{ $etp->prazo_entrega }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <!-- Objeto -->
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col">
                        <h4 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2"><i
                                class="fas fa-align-left mr-2 text-[#009496]"></i> Objeto da Licitação</h4>
                        <div class="flex-grow bg-white p-4 rounded-lg border border-gray-200 overflow-y-auto max-h-[300px]">
                            <p class="text-gray-700 whitespace-pre-wrap text-sm leading-relaxed">{{ $etp->objeto_licitacao }}</p>
                        </div>
                    </div>
                </div>

                <!-- ITENS OU LOTES -->
                @if(!in_array($etp->modalidade, ['concorrencia', 'inexigibilidade']))
                    @if(in_array($etp->tipo_contratacao, ['item', 'servicos', 'compras']))
                        <!-- ITENS (PARA ITEM, SERVIÇOS E COMPRAS) -->
                        <div class="mb-8 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            {{-- Cabeçalho com total e botão de export --}}
                            <div class="border-b border-gray-200 mb-4 flex items-center justify-between">
                                <h4 class="text-lg font-bold text-gray-800 mb-4 pb-2">
                                    <i class="fas fa-boxes mr-2 text-[#009496]"></i>
                                    @if($etp->tipo_contratacao === 'servicos')
                                        Serviços Solicitados
                                    @elseif($etp->tipo_contratacao === 'compras')
                                        Itens de Compra
                                    @else
                                        Itens Solicitados
                                    @endif
                                </h4>
                                @if($etp->itens->count() > 0)
                                    <a href="{{ route('admin.etps.export-itens', $etp->id) }}"
                                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-all shadow-sm">
                                        <i class="fas fa-file-excel mr-2"></i>
                                        Exportar XLS
                                    </a>
                                @endif
                            </div>

                            @if($etp->itens->count() > 0)
                                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                                    <table class="w-full text-sm text-left text-gray-500">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-4 py-3 font-bold text-gray-800 w-12 text-center">#</th>
                                                <th scope="col" class="px-6 py-3 font-bold text-gray-800">Descrição do Item</th>
                                                <th scope="col" class="px-6 py-3 font-bold text-gray-800">Unidade</th>
                                                <th scope="col" class="px-6 py-3 font-bold text-gray-800">Quantidade</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach($etp->itens as $index => $item)
                                                <tr class="bg-white hover:bg-gray-50 transition-colors">
                                                    <td class="px-4 py-4 text-center">
                                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#009496]/10 text-[#009496] text-xs font-bold">
                                                            {{ $index + 1 }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 text-gray-800 font-medium">{{ $item->descricao_item }}</td>
                                                    <td class="px-6 py-4 text-gray-600">{{ $item->pivot->unidade }}</td>
                                                    <td class="px-6 py-4 text-gray-600">{{ $item->pivot->quantidade }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-gray-50 border-t-2 border-gray-200">
                                                <td colspan="3" class="px-6 py-3 text-xs font-bold text-gray-600 uppercase">Total de Itens</td>
                                                <td class="px-6 py-3 font-bold text-gray-800">{{ $etp->itens->count() }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-md">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700 font-medium">Nenhum item vinculado a este ETP.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                    @elseif($etp->tipo_contratacao === 'lote' && $etp->lotes->count() > 0)
                        <!-- LOTES -->
                        @php
                            $totalItensGeral = $etp->lotes->sum(fn($l) => $l->itens->count());
                        @endphp
                        <div class="mb-8">
                            <div class="border-b border-gray-200 mb-4 flex items-center justify-between">
                                <h4 class="text-lg font-bold text-gray-800 mb-4 pb-2">
                                    <i class="fas fa-layer-group mr-2 text-[#009496]"></i>
                                    Lotes da Contratação
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-[#009496]/10 text-[#009496] border border-[#009496]/20 ml-2">
                                        {{ $etp->lotes->count() }} {{ $etp->lotes->count() === 1 ? 'lote' : 'lotes' }} ·
                                        {{ $totalItensGeral }} {{ $totalItensGeral === 1 ? 'item' : 'itens' }}
                                    </span>
                                </h4>
                                @if($totalItensGeral > 0)
                                    <a href="{{ route('admin.etps.export-itens', $etp->id) }}"
                                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-all shadow-sm">
                                        <i class="fas fa-file-excel mr-2"></i>
                                        Exportar XLS
                                    </a>
                                @endif
                            </div>

                            <div class="space-y-6">
                                @php $numGlobal = 1; @endphp
                                @foreach($etp->lotes as $lote)
                                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                                        <div class="bg-gray-100 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                                            <h5 class="text-md font-semibold text-gray-800">
                                                <i class="fas fa-tag mr-2 text-[#009496]"></i> {{ $lote->nome }}
                                            </h5>
                                            <span class="text-xs font-medium text-gray-500">
                                                {{ $lote->itens->count() }} {{ $lote->itens->count() === 1 ? 'item' : 'itens' }}
                                            </span>
                                        </div>
                                        <div class="p-6">
                                            @if($lote->itens->count() > 0)
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-sm text-left text-gray-500">
                                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                                            <tr>
                                                                <th scope="col" class="px-4 py-3 w-12 text-center">#</th>
                                                                <th scope="col" class="px-6 py-3">Descrição do Item</th>
                                                                <th scope="col" class="px-6 py-3">Unidade</th>
                                                                <th scope="col" class="px-6 py-3">Quantidade</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($lote->itens as $item)
                                                                <tr class="bg-white border-b hover:bg-gray-50">
                                                                    <td class="px-4 py-4 text-center">
                                                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#009496]/10 text-[#009496] text-xs font-bold">
                                                                            {{ $numGlobal++ }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="px-6 py-4">{{ $item->descricao_item }}</td>
                                                                    <td class="px-6 py-4">{{ $item->pivot->unidade }}</td>
                                                                    <td class="px-6 py-4">{{ $item->pivot->quantidade }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <p class="text-gray-500 text-sm">Nenhum item vinculado a este lote.</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

                <!-- ANEXOS -->
                @if ($etp->cotacao_path)
                    @php
                        $extension = pathinfo($etp->cotacao_path, PATHINFO_EXTENSION);
                        $isPdf = strtolower($extension) === 'pdf';
                        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
                        $tituloAnexo = in_array($etp->modalidade, ['concorrencia', 'inexigibilidade'])
                            ? 'Projeto Básico'
                            : 'Cotação do Fornecedor';
                    @endphp

                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm mb-8">
                        <h4 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">
                            <i class="fas fa-paperclip mr-2 text-[#009496]"></i> Documentos Anexos
                        </h4>

                        @if ($isPdf)
                            <div
                                class="flex items-center justify-between bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <div class="flex items-center">
                                    <div
                                        class="w-12 h-12 rounded-full bg-red-600 text-white flex items-center justify-center mr-4 shadow-sm">
                                        <i class="fas fa-file-pdf text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-blue-900 mb-0.5">{{ $tituloAnexo }}</p>
                                        <p class="text-xs text-blue-700">Clique no botão para visualizar o PDF</p>
                                    </div>
                                </div>
                                <a href="{{ asset($etp->cotacao_path) }}"
                                    download
                                    class="inline-flex items-center px-4 py-2 bg-[#009496] text-white rounded-lg hover:bg-[#007a7a] transition-all">
                                        <i class="fas fa-download mr-2"></i>
                                        Baixar PDF
                                </a>

                            </div>
                        @elseif($isImage)
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <p class="text-sm font-bold text-blue-900 mb-3">{{ $tituloAnexo }}</p>
                                <img src="{{ url($etp->cotacao_path) }}" alt="Anexo"
                                    class="max-w-full h-auto rounded-lg border border-gray-300 max-h-96 mx-auto">
                            </div>
                        @else
                            <a href="{{ url($etp->cotacao_path) }}" target="_blank"
                                class="inline-flex items-center px-4 py-3 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 hover:shadow-md transition-all group w-full">
                                <div
                                    class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center mr-4 shadow-sm group-hover:scale-110 transition-transform">
                                    <i class="fas fa-download text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-blue-900 mb-0.5">{{ $tituloAnexo }}</p>
                                    <p class="text-xs text-blue-700 font-medium">Visualizar arquivo original</p>
                                </div>
                            </a>
                        @endif
                    </div>
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
                                <p class="text-sm text-purple-800">Este ETP foi aprovado e agora faz parte do Processo Nº
                                    <span class="font-bold">{{ $etp->processo->numero_processo ?? $etp->processo_id }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
