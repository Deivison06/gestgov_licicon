@extends('layouts.app')

@section('page-title', 'Detalhes do Processo')
@section('page-subtitle', $processo->numero_processo ?? $processo->numero_procedimento ?? '#'.$processo->id)

@php
    $statusColorMap = [
        'blue'   => 'bg-blue-500/15 text-blue-300 border-blue-500/30',
        'green'  => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
        'red'    => 'bg-red-500/15 text-red-300 border-red-500/30',
        'purple' => 'bg-purple-500/15 text-purple-300 border-purple-500/30',
        'orange' => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
    ];
    
    // O Model pode ou não estar com cast (estamos usando sem cast no momento para depuração)
    $status = $processo->status;
    $statusEnum = $status instanceof \App\Enums\ProcessoStatusEnum 
        ? $status 
        : (\App\Enums\ProcessoStatusEnum::tryFrom($status) ?? \App\Enums\ProcessoStatusEnum::EM_ANDAMENTO);
    
    $statusLabel = $statusEnum->label();
    $statusClasses = $statusColorMap[$statusEnum->color()] ?? 'bg-[#142825] text-[#a2b9b5] border-[#24403c]';
@endphp

@section('content')
<div class="py-8 space-y-8">

    {{-- Cabeçalho / Ações --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.processos.index', ['prefeitura_id' => $processo->prefeitura_id]) }}"
                class="flex items-center justify-center w-10 h-10 transition-colors bg-[#10201e] border border-[#24403c] shadow-sm rounded-xl text-[#e6f0ee] hover:text-[#2DC197] hover:bg-[#142825]"
                title="Voltar para a lista">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-[#e6f0ee]">
                    {{ $processo->numero_processo ?? $processo->numero_procedimento }}
                </h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full bg-[#132d27] text-[#2DC197] border border-[#24403c]">
                        {{ $processo->prefeitura->nome }}
                    </span>
                    <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ $statusClasses }}">
                        {{ $statusLabel }}
                    </span>
                    @if($processo->foi_republicado)
                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full border bg-purple-500/15 text-purple-300 border-purple-500/30"
                              title="Este processo é fruto de uma republicação">
                            Republicado
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.processos.edit', $processo) }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-[#e6f0ee] bg-[#10201e] border border-[#24403c] rounded-xl hover:bg-[#142825] hover:text-[#2DC197] transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar Processo
            </a>
            <a href="{{ route('admin.processos.iniciar', $processo) }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-[#041d1a] bg-[#2DC197] rounded-xl hover:bg-[#26aa84] hover:shadow-lg hover:scale-105 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Gerar Documentos
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">

        {{-- Coluna Principal --}}
        <div class="space-y-8 lg:col-span-2">

            {{-- Card: Informações Gerais --}}
            <div class="overflow-hidden card !rounded-2xl">
                <div class="card-h">
                    <h3 class="text-sm font-bold tracking-wider text-[#a2b9b5] uppercase flex items-center gap-2">
                        <i class="fas fa-info-circle text-[#2DC197]"></i> Dados do Processo
                    </h3>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <label class="block mb-1 text-xs font-bold tracking-wide text-[#a2b9b5] uppercase">Objeto</label>
                        <p class="text-[#e6f0ee] leading-relaxed">{{ html_entity_decode(strip_tags($processo->objeto)) }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-bold tracking-wide text-[#a2b9b5] uppercase">Modalidade</label>
                            <p class="font-semibold text-[#e6f0ee]">{{ $processo->modalidade->getDisplayName() }}</p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold tracking-wide text-[#a2b9b5] uppercase">Tipo de Contratação</label>
                            <p class="font-semibold text-[#e6f0ee]">{{ $processo->tipo_contratacao_nome }}</p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold tracking-wide text-[#a2b9b5] uppercase">Nº Processo</label>
                            <p class="font-mono font-semibold text-[#e6f0ee]">{{ $processo->numero_processo ?? '—' }}</p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold tracking-wide text-[#a2b9b5] uppercase">Nº Procedimento</label>
                            <p class="font-mono font-semibold text-[#e6f0ee]">{{ $processo->numero_procedimento ?? '—' }}</p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold tracking-wide text-[#a2b9b5] uppercase">Tipo de Procedimento</label>
                            <p class="font-semibold text-[#e6f0ee]">{{ $processo->tipo_procedimento_nome }}</p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold tracking-wide text-[#a2b9b5] uppercase">Responsável pela Elaboração</label>
                            <p class="font-semibold text-[#e6f0ee]">{{ $processo->user->name ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: Detalhes Técnicos --}}
            @if($processo->detalhe)
                <div class="overflow-hidden card !rounded-2xl">
                    <div class="card-h">
                        <h3 class="text-sm font-bold tracking-wider text-[#a2b9b5] uppercase flex items-center gap-2">
                            <i class="fas fa-cogs text-[#2DC197]"></i> Detalhes Técnicos
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block mb-1 text-xs font-bold tracking-wide text-[#a2b9b5] uppercase">Valor Estimado</label>
                                <p class="text-xl font-bold text-[#2DC197]">
                                    {{ $processo->detalhe->valor_estimado ?? '—' }}
                                </p>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-bold tracking-wide text-[#a2b9b5] uppercase">Unidade/Setor</label>
                                <p class="font-semibold text-[#e6f0ee]">{{ $processo->detalhe->unidade_setor ?? '—' }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block mb-1 text-xs font-bold tracking-wide text-[#a2b9b5] uppercase">Dotação Orçamentária</label>
                                <div class="p-3 text-sm text-[#e6f0ee] bg-[#0a1514] rounded-xl border border-[#24403c]">
                                    {!! $processo->detalhe->dotacao_orcamentaria ?? '—' !!}
                                </div>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-bold tracking-wide text-[#a2b9b5] uppercase">Prazo de Entrega</label>
                                <p class="font-semibold text-[#e6f0ee]">{{ $processo->detalhe->prazo_entrega ?? '—' }}</p>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-bold tracking-wide text-[#a2b9b5] uppercase">Local de Entrega</label>
                                <p class="font-semibold text-[#e6f0ee]">{{ $processo->detalhe->local_entrega ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- Coluna Lateral --}}
        <div class="space-y-8">

            {{-- Card: Datas e Prazos --}}
            <div class="overflow-hidden card !rounded-2xl">
                <div class="card-h">
                    <h3 class="text-sm font-bold tracking-wider text-[#a2b9b5] uppercase flex items-center gap-2">
                        <i class="far fa-calendar-alt text-[#2DC197]"></i> Datas e Prazos
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-[#0a1514] border border-[#24403c]">
                        <div>
                            <p class="text-[10px] font-bold text-[#a2b9b5] uppercase tracking-wider">Criado em</p>
                            <p class="text-sm font-semibold text-[#e6f0ee]">{{ $processo->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <svg class="w-5 h-5 text-[#2DC197]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>

                    @if($processo->detalhe?->data_hora)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-blue-950/30 border border-blue-500/30">
                            <div>
                                <p class="text-[10px] font-bold text-blue-300 uppercase tracking-wider">Data da Sessão</p>
                                <p class="text-sm font-bold text-blue-200">{{ $processo->detalhe->data_hora->format('d/m/Y H:i') }}</p>
                            </div>
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    @endif

                    @if($processo->data_cancelamento)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-red-950/30 border border-red-500/30">
                            <div>
                                <p class="text-[10px] font-bold text-red-300 uppercase tracking-wider">Cancelado em</p>
                                <p class="text-sm font-bold text-red-200">{{ $processo->data_cancelamento->format('d/m/Y') }}</p>
                            </div>
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card: Documentos Gerados --}}
            <div class="overflow-hidden card !rounded-2xl">
                <div class="flex items-center justify-between card-h">
                    <h3 class="text-sm font-bold tracking-wider text-[#a2b9b5] uppercase flex items-center gap-2">
                        <i class="fas fa-file-contract text-[#2DC197]"></i> Documentos
                    </h3>
                    <span class="px-2.5 py-0.5 text-[10px] font-bold bg-[#0a1514] text-[#2DC197] border border-[#24403c] rounded-full uppercase tracking-tighter">
                        {{ $processo->documentos->count() }} arquivo(s)
                    </span>
                </div>
                <div class="p-2">
                    <div class="divide-y divide-[#24403c]/40">
                        @forelse($processo->documentos as $doc)
                            <div class="flex items-center justify-between p-3 transition-colors hover:bg-[#142825] rounded-xl group">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-950/50 text-red-400 border border-red-500/30">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-[#e6f0ee] truncate max-w-[150px]" title="{{ $doc->tipo_documento }}">
                                            {{ ucwords(str_replace(['_', '-'], ' ', $doc->tipo_documento)) }}
                                        </p>
                                        <p class="text-[10px] text-[#a2b9b5]">{{ $doc->gerado_em ? \Carbon\Carbon::parse($doc->gerado_em)->format('d/m/Y') : '—' }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('admin.processo.documento.dowload', ['processo' => $processo->id, 'tipo' => $doc->id]) }}"
                                    class="p-2 transition-colors opacity-70 text-[#a2b9b5] hover:text-[#2DC197] group-hover:opacity-100"
                                    title="Download">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </a>
                            </div>
                        @empty
                            <div class="py-12 text-center">
                                <p class="text-xs font-medium text-[#a2b9b5] italic">Nenhum documento gerado.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
