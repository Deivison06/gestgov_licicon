@extends('layouts.app')
@section('page-title', 'Detalhes do ETP')
@section('page-subtitle', 'Visualização do Estudo Técnico Preliminar')

@section('content')
    <div class="py-8">
        <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.etps.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-[var(--ink)] bg-[var(--card)] border border-[var(--line)] rounded-xl hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] transition-all duration-200 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                        </path>
                    </svg>
                    Voltar para a Lista
                </a>

                @php
                    $podeEditar = ($etp->status === 'pendente' || $etp->status === 'recusado') || 
                                 auth()->user()->hasAnyRole(['diretor_licicon', 'gerente_licicon', 'colaborador_licicon']);
                @endphp

                @if($podeEditar)
                    <a href="{{ route('admin.etps.edit', $etp->id) }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] transition-all duration-200 shadow-sm hover:scale-105">
                        <i class="fas fa-edit"></i>
                        Editar ETP
                    </a>
                @endif
            </div>

            <div
                class="inline-flex items-center px-4 py-2 text-sm font-bold rounded-xl shadow-sm border
                @if ($etp->status === 'pendente') bg-amber-500/15 text-amber-300 border-amber-500/30
                @elseif($etp->status === 'em_analise') bg-blue-500/15 text-blue-300 border-blue-500/30
                @elseif($etp->status === 'aprovado') bg-emerald-500/15 text-emerald-300 border-emerald-500/30
                @elseif($etp->status === 'em_processo') bg-purple-500/15 text-purple-300 border-purple-500/30
                @elseif($etp->status === 'concluido') bg-[var(--card-3)] text-[var(--mint)] border-[var(--line)]
                @elseif($etp->status === 'recusado') bg-red-500/15 text-red-300 border-red-500/30 @endif">
                <i
                    class="fas @if ($etp->status === 'aprovado' || $etp->status === 'em_processo' || $etp->status === 'concluido') fa-check-circle @elseif($etp->status === 'recusado') fa-times-circle @else fa-clock @endif mr-2"></i>
                Status Atual: {{ ucfirst(str_replace('_', ' ', $etp->status)) }}
            </div>
        </div>

        <div class="card !rounded-2xl overflow-hidden mb-8">
            <div class="card-h w-full flex justify-between items-center">
                <h3 class="text-xl font-bold text-[var(--ink)] flex items-center">
                    <i class="fas fa-file-alt mr-2 text-[var(--mint)]"></i> Solicitação
                    ETP-{{ str_pad($etp->id, 4, '0', STR_PAD_LEFT) }}/{{ $etp->created_at->format('Y') }}
                </h3>
                <span class="text-sm text-[var(--ink-2)]">Criado em {{ $etp->created_at->format('d/m/Y H:i') }}</span>
            </div>

            <div class="p-8 border-b border-[var(--line)]">
                @if($etp->status === 'recusado' && $etp->motivo_recusa)
                    <div class="mb-8 p-6 bg-red-950/40 border-l-4 border-red-500 rounded-xl shadow-sm">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-bold text-red-300">Sua Solicitação foi Recusada</h3>
                                <div class="mt-2 text-red-200 text-sm leading-relaxed">
                                    <p class="font-semibold mb-1">Motivo informado pela análise:</p>
                                    <p class="whitespace-pre-wrap">{{ $etp->motivo_recusa }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <!-- Informações Iniciais -->
                    <div class="bg-[var(--card-2)] p-6 rounded-xl border border-[var(--line)] shadow-sm">
                        <h4 class="text-lg font-bold text-[var(--ink)] mb-4 border-b border-[var(--line)] pb-2 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-[var(--mint)]"></i> Dados Gerais
                        </h4>
                        <ul class="space-y-4">
                            <li>
                                <span class="text-[var(--ink-2)] font-semibold block text-xs uppercase tracking-wider mb-1">Secretaria</span>
                                <span class="text-[var(--ink)] font-bold text-base">{{ $etp->secretaria->nome ?? 'N/A' }}</span>
                            </li>
                            <li>
                                <span class="text-[var(--ink-2)] font-semibold block text-xs uppercase tracking-wider mb-1">Responsável</span>
                                <span class="text-[var(--ink)] font-semibold flex items-center">
                                    <i class="fas fa-user-circle text-[var(--mint)] mr-2 text-lg"></i>
                                    {{ $etp->servidor_responsavel ?? 'N/A' }}
                                </span>
                            </li>
                            <li class="pt-3 border-t border-[var(--line)]">
                                <span class="text-[var(--ink-2)] font-semibold block text-xs uppercase tracking-wider mb-1">Modalidade</span>
                                <span class="inline-block bg-purple-500/15 text-purple-300 border border-purple-500/30 px-3 py-1 rounded-lg font-bold uppercase text-sm">
                                    {{ $etp->modalidade ?? 'Não informada' }}
                                </span>
                            </li>
                            <li class="pt-3 border-t border-[var(--line)]">
                                <span class="text-[var(--ink-2)] font-semibold block text-xs uppercase tracking-wider mb-1">Dotação Orçamentária</span>
                                <span class="text-[var(--ink)] font-semibold bg-[var(--bg)] px-3 py-1 rounded-lg border border-[var(--line)] text-sm">
                                    {{ $etp->dotacao_orcamentaria ?? 'Não informada' }}
                                </span>
                            </li>

                            @if(!in_array($etp->modalidade, ['concorrencia', 'inexigibilidade']))
                                <li class="pt-3 border-t border-[var(--line)]">
                                    <span class="text-[var(--ink-2)] font-semibold block text-xs uppercase tracking-wider mb-1">Tipo de Contratação</span>
                                    <span class="inline-block bg-[var(--card-3)] text-[var(--mint)] px-3 py-1 rounded-lg font-bold uppercase text-sm border border-[var(--line)]">
                                        {{ $etp->tipo_contratacao }}{{ $etp->organizacao_itens ? ' - ' . $etp->organizacao_itens : '' }}
                                    </span>
                                </li>
                                <li>
                                    <span class="text-[var(--ink-2)] font-semibold block text-xs uppercase tracking-wider mb-1">Prazo de Entrega</span>
                                    <span class="text-[var(--ink)] font-semibold bg-[var(--bg)] px-3 py-1 rounded-lg border border-[var(--line)] text-sm flex items-center gap-1.5 w-fit">
                                        <i class="far fa-calendar-alt text-[var(--mint)]"></i>
                                        {{ $etp->prazo_entrega }}
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <!-- Objeto -->
                    <div class="bg-[var(--card-2)] p-6 rounded-xl border border-[var(--line)] shadow-sm flex flex-col">
                        <h4 class="text-lg font-bold text-[var(--ink)] mb-4 border-b border-[var(--line)] pb-2 flex items-center">
                            <i class="fas fa-align-left mr-2 text-[var(--mint)]"></i> Objeto da Licitação
                        </h4>
                        <div class="flex-grow bg-[var(--bg)] p-4 rounded-xl border border-[var(--line)] overflow-y-auto max-h-[300px]">
                            <p class="text-[var(--ink)] whitespace-pre-wrap text-sm leading-relaxed">{{ $etp->objeto_licitacao }}</p>
                        </div>
                    </div>
                </div>

                <!-- ITENS OU LOTES -->
                @if(!in_array($etp->modalidade, ['concorrencia', 'inexigibilidade']))
                    @if($etp->tipo_contratacao !== 'obras' && !$etp->usaLotes())
                        <!-- ITENS (PARA ITEM, SERVIÇOS E COMPRAS) -->
                        <div class="mb-8 bg-[var(--card-2)] p-6 rounded-xl border border-[var(--line)] shadow-sm">
                            {{-- Cabeçalho com total e botão de export --}}
                            <div class="border-b border-[var(--line)] mb-4 pb-3 flex items-center justify-between flex-wrap gap-3">
                                <h4 class="text-lg font-bold text-[var(--ink)] flex items-center">
                                    <i class="fas fa-boxes mr-2 text-[var(--mint)]"></i>
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
                                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-sm hover:scale-105">
                                        <i class="fas fa-file-excel"></i>
                                        Exportar Itens
                                    </a>
                                @endif
                            </div>

                            @if($etp->itens->count() > 0)
                                <div class="overflow-x-auto rounded-xl border border-[var(--line)]">
                                    <table class="w-full text-sm text-left text-[var(--ink-2)]">
                                        <thead class="text-xs text-[var(--ink-2)] uppercase bg-[var(--bg)] border-b border-[var(--line)]">
                                            <tr>
                                                <th scope="col" class="px-4 py-3 font-bold text-[var(--ink)] w-12 text-center">#</th>
                                                <th scope="col" class="px-6 py-3 font-bold text-[var(--ink)]">Descrição do Item</th>
                                                <th scope="col" class="px-6 py-3 font-bold text-[var(--ink)]">Unidade</th>
                                                <th scope="col" class="px-6 py-3 font-bold text-[var(--ink)]">Quantidade</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[var(--line)]/40">
                                            @foreach($etp->itens as $index => $item)
                                                <tr class="bg-[var(--card-2)] hover:bg-[var(--hover-bg)] transition-colors">
                                                    <td class="px-4 py-4 text-center">
                                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[var(--card-3)] text-[var(--mint)] text-xs font-bold border border-[var(--line)]">
                                                            {{ $index + 1 }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 text-[var(--ink)] font-medium">{{ $item->descricao_item }}</td>
                                                    <td class="px-6 py-4 text-[var(--ink-2)]">{{ $item->pivot->unidade }}</td>
                                                    <td class="px-6 py-4 text-[var(--ink-2)]">{{ $item->pivot->quantidade }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-[var(--bg)] border-t-2 border-[var(--line)]">
                                                <td colspan="3" class="px-6 py-3 text-xs font-bold text-[var(--ink-2)] uppercase">Total de Itens</td>
                                                <td class="px-6 py-3 font-bold text-[var(--mint)]">{{ $etp->itens->count() }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="bg-amber-950/30 border-l-4 border-amber-500 p-4 rounded-xl text-amber-300">
                                    <div class="flex items-center">
                                        <i class="fas fa-exclamation-triangle text-amber-400 mr-3"></i>
                                        <p class="text-sm font-medium">Nenhum item vinculado a este ETP.</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                    @elseif($etp->usaLotes() && $etp->lotes->count() > 0)
                        <!-- LOTES -->
                        @php
                            $totalItensGeral = $etp->lotes->sum(fn($l) => $l->itens->count());
                        @endphp
                        <div class="mb-8">
                            <div class="border-b border-[var(--line)] mb-4 pb-3 flex items-center justify-between flex-wrap gap-3">
                                <h4 class="text-lg font-bold text-[var(--ink)] flex items-center">
                                    <i class="fas fa-layer-group mr-2 text-[var(--mint)]"></i>
                                    Lotes da Contratação
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[var(--card-3)] text-[var(--mint)] border border-[var(--line)] ml-2">
                                        {{ $etp->lotes->count() }} {{ $etp->lotes->count() === 1 ? 'lote' : 'lotes' }} ·
                                        {{ $totalItensGeral }} {{ $totalItensGeral === 1 ? 'item' : 'itens' }}
                                    </span>
                                </h4>
                                @if($totalItensGeral > 0)
                                    <a href="{{ route('admin.etps.export-itens', $etp->id) }}"
                                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-sm hover:scale-105">
                                        <i class="fas fa-file-excel"></i>
                                        Exportar XLS
                                    </a>
                                @endif
                            </div>

                            <div class="space-y-6">
                                @php $numGlobal = 1; @endphp
                                @foreach($etp->lotes as $lote)
                                    <div class="border border-[var(--line)] rounded-xl overflow-hidden bg-[var(--card-2)]">
                                        <div class="bg-[var(--bg)] px-6 py-4 border-b border-[var(--line)] flex items-center justify-between">
                                            <h5 class="text-md font-semibold text-[var(--ink)] flex items-center">
                                                <i class="fas fa-tag mr-2 text-[var(--mint)]"></i> {{ $lote->nome }}
                                            </h5>
                                            <span class="text-xs font-medium text-[var(--ink-2)]">
                                                {{ $lote->itens->count() }} {{ $lote->itens->count() === 1 ? 'item' : 'itens' }}
                                            </span>
                                        </div>
                                        <div class="p-6">
                                            @if($lote->itens->count() > 0)
                                                <div class="overflow-x-auto rounded-xl border border-[var(--line)]">
                                                    <table class="w-full text-sm text-left text-[var(--ink-2)]">
                                                        <thead class="text-xs text-[var(--ink-2)] uppercase bg-[var(--bg)] border-b border-[var(--line)]">
                                                            <tr>
                                                                <th scope="col" class="px-4 py-3 w-12 text-center font-bold text-[var(--ink)]">#</th>
                                                                <th scope="col" class="px-6 py-3 font-bold text-[var(--ink)]">Descrição do Item</th>
                                                                <th scope="col" class="px-6 py-3 font-bold text-[var(--ink)]">Unidade</th>
                                                                <th scope="col" class="px-6 py-3 font-bold text-[var(--ink)]">Quantidade</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-[var(--line)]/40">
                                                            @foreach($lote->itens as $item)
                                                                <tr class="bg-[var(--card-2)] hover:bg-[var(--hover-bg)] transition-colors">
                                                                    <td class="px-4 py-4 text-center">
                                                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[var(--card-3)] text-[var(--mint)] text-xs font-bold border border-[var(--line)]">
                                                                            {{ $numGlobal++ }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="px-6 py-4 text-[var(--ink)] font-medium">{{ $item->descricao_item }}</td>
                                                                    <td class="px-6 py-4 text-[var(--ink-2)]">{{ $item->pivot->unidade }}</td>
                                                                    <td class="px-6 py-4 text-[var(--ink-2)]">{{ $item->pivot->quantidade }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <p class="text-[var(--ink-2)] text-sm">Nenhum item vinculado a este lote.</p>
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

                    <div class="bg-[var(--card-2)] p-6 rounded-xl border border-[var(--line)] shadow-sm mb-8">
                        <h4 class="text-lg font-bold text-[var(--ink)] mb-4 border-b border-[var(--line)] pb-2 flex items-center">
                            <i class="fas fa-paperclip mr-2 text-[var(--mint)]"></i> Documentos Anexos
                        </h4>

                        @if ($isPdf)
                            <div
                                class="flex items-center justify-between bg-[var(--bg)] p-4 rounded-xl border border-[var(--line)]">
                                <div class="flex items-center">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-red-950/50 text-red-400 border border-red-500/30 flex items-center justify-center mr-4 shadow-sm">
                                        <i class="fas fa-file-pdf text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-[var(--ink)] mb-0.5">{{ $tituloAnexo }}</p>
                                        <p class="text-xs text-[var(--ink-2)]">Clique no botão para visualizar o PDF</p>
                                    </div>
                                </div>
                                <a href="{{ asset($etp->cotacao_path) }}"
                                    download
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-[var(--mint)] text-[var(--on-mint)] font-bold rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-sm hover:scale-105">
                                        <i class="fas fa-download"></i>
                                        Baixar PDF
                                </a>

                            </div>
                        @elseif($isImage)
                            <div class="bg-[var(--bg)] p-4 rounded-xl border border-[var(--line)]">
                                <p class="text-sm font-bold text-[var(--ink)] mb-3">{{ $tituloAnexo }}</p>
                                <img src="{{ url($etp->cotacao_path) }}" alt="Anexo"
                                    class="max-w-full h-auto rounded-xl border border-[var(--line)] max-h-96 mx-auto">
                            </div>
                        @else
                            <a href="{{ url($etp->cotacao_path) }}" target="_blank"
                                class="inline-flex items-center px-4 py-3 bg-[var(--bg)] border border-[var(--line)] rounded-xl hover:bg-[var(--hover-bg)] hover:border-[var(--mint)]/50 transition-all group w-full">
                                <div
                                    class="w-12 h-12 rounded-xl bg-[var(--card)] border border-[var(--line)] text-[var(--mint)] flex items-center justify-center mr-4 shadow-sm group-hover:scale-110 transition-transform">
                                    <i class="fas fa-download text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-[var(--ink)] mb-0.5">{{ $tituloAnexo }}</p>
                                    <p class="text-xs text-[var(--ink-2)] font-medium">Visualizar arquivo original</p>
                                </div>
                            </a>
                        @endif
                    </div>
                @endif

                @if($etp->processo_id)
                    <div class="mt-8 bg-purple-950/40 p-6 rounded-xl border border-purple-500/30">
                        <div class="flex items-center">
                            <div
                                class="w-12 h-12 rounded-xl bg-purple-500/20 text-purple-300 border border-purple-500/30 flex items-center justify-center mr-4">
                                <i class="fas fa-link text-xl"></i>
                            </div>
                            <div>
                                <a href="{{ route('admin.processos.iniciar', $etp->processo_id) }}" class="group">
                                    <h4 class="text-lg font-bold text-purple-200 group-hover:text-purple-100 transition-colors">Vinculado a Processo de Licitação</h4>
                                    <p class="text-sm text-purple-300">Este ETP foi aprovado e agora faz parte do Processo Nº
                                        <span class="font-bold text-white">{{ $etp->processo->numero_processo ?? $etp->processo_id }}</span>
                                    </p>      
                                </a>
                            </div>
                        </div>
                    </div>
                @elseif($etp->status === 'concluido')
                    <div class="mt-8 bg-[var(--card-3)]/70 p-6 rounded-xl border border-[var(--line)]">
                        <div class="flex items-center">
                            <div
                                class="w-12 h-12 rounded-xl bg-[var(--card)] text-[var(--mint)] border border-[var(--line)] flex items-center justify-center mr-4">
                                <i class="fas fa-flag-checkered text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-[var(--ink)]">ETP Concluído</h4>
                                <p class="text-sm text-[var(--ink-2)]">Este ETP foi analisado e encerrado pela equipe responsável.</p>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
