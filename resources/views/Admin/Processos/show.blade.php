@extends('layouts.app')

@section('page-title', 'Detalhes do Processo')
@section('page-subtitle', $processo->numero_processo ?? $processo->numero_procedimento ?? '#'.$processo->id)

@php
    $statusColorMap = [
        'blue'   => 'bg-blue-100 text-blue-700 border-blue-200',
        'green'  => 'bg-green-100 text-green-700 border-green-200',
        'red'    => 'bg-red-100 text-red-700 border-red-200',
        'purple' => 'bg-purple-100 text-purple-700 border-purple-200',
        'orange' => 'bg-orange-100 text-orange-700 border-orange-200',
    ];
    
    // O Model pode ou não estar com cast (estamos usando sem cast no momento para depuração)
    $status = $processo->status;
    $statusEnum = $status instanceof \App\Enums\ProcessoStatusEnum 
        ? $status 
        : (\App\Enums\ProcessoStatusEnum::tryFrom($status) ?? \App\Enums\ProcessoStatusEnum::EM_ANDAMENTO);
    
    $statusLabel = $statusEnum->label();
    $statusClasses = $statusColorMap[$statusEnum->color()] ?? 'bg-gray-100 text-gray-700 border-gray-200';
@endphp

@section('content')
<div class="py-8 space-y-8">

    {{-- Cabeçalho / Ações --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.processos.index', ['prefeitura_id' => $processo->prefeitura_id]) }}"
                class="flex items-center justify-center w-10 h-10 transition-colors bg-white border border-gray-200 shadow-sm rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ $processo->numero_processo ?? $processo->numero_procedimento }}
                </h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full bg-teal-50 text-teal-700 border border-teal-100">
                        {{ $processo->prefeitura->nome }}
                    </span>
                    <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ $statusClasses }}">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.processos.edit', $processo) }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar Processo
            </a>
            <a href="{{ route('admin.processos.iniciar', $processo) }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-[#052323] rounded-xl hover:shadow-lg hover:scale-105 transition-all">
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
            <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-sm font-bold tracking-wider text-gray-400 uppercase">Dados do Processo</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <label class="block mb-1 text-xs font-bold tracking-wide text-gray-400 uppercase">Objeto</label>
                        <p class="text-gray-900 leading-relaxed">{{ html_entity_decode(strip_tags($processo->objeto)) }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-bold tracking-wide text-gray-400 uppercase">Modalidade</label>
                            <p class="font-semibold text-gray-900">{{ $processo->modalidade->getDisplayName() }}</p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold tracking-wide text-gray-400 uppercase">Tipo de Contratação</label>
                            <p class="font-semibold text-gray-900">{{ $processo->tipo_contratacao_nome }}</p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold tracking-wide text-gray-400 uppercase">Nº Processo</label>
                            <p class="font-mono font-semibold text-gray-900">{{ $processo->numero_processo ?? '—' }}</p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold tracking-wide text-gray-400 uppercase">Nº Procedimento</label>
                            <p class="font-mono font-semibold text-gray-900">{{ $processo->numero_procedimento ?? '—' }}</p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold tracking-wide text-gray-400 uppercase">Tipo de Procedimento</label>
                            <p class="font-semibold text-gray-900">{{ $processo->tipo_procedimento_nome }}</p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold tracking-wide text-gray-400 uppercase">Responsável pela Elaboração</label>
                            <p class="font-semibold text-gray-900">{{ $processo->user->name ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: Detalhes Técnicos --}}
            @if($processo->detalhe)
                <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-sm font-bold tracking-wider text-gray-400 uppercase">Detalhes Técnicos</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block mb-1 text-xs font-bold tracking-wide text-gray-400 uppercase">Valor Estimado</label>
                                <p class="text-lg font-bold text-teal-700">
                                    R$ {{ number_format((float) ($processo->detalhe->valor_estimado ?? 0), 2, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-bold tracking-wide text-gray-400 uppercase">Unidade/Setor</label>
                                <p class="font-semibold text-gray-900">{{ $processo->detalhe->unidade_setor ?? '—' }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block mb-1 text-xs font-bold tracking-wide text-gray-400 uppercase">Dotação Orçamentária</label>
                                <div class="p-3 text-sm text-gray-700 bg-gray-50 rounded-xl border border-gray-100">
                                    {!! $processo->detalhe->dotacao_orcamentaria ?? '—' !!}
                                </div>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-bold tracking-wide text-gray-400 uppercase">Prazo de Entrega</label>
                                <p class="font-semibold text-gray-900">{{ $processo->detalhe->prazo_entrega ?? '—' }}</p>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-bold tracking-wide text-gray-400 uppercase">Local de Entrega</label>
                                <p class="font-semibold text-gray-900">{{ $processo->detalhe->local_entrega ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- Coluna Lateral --}}
        <div class="space-y-8">

            {{-- Card: Datas e Prazos --}}
            <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-sm font-bold tracking-wider text-gray-400 uppercase">Datas e Prazos</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Criado em</p>
                            <p class="text-sm font-semibold text-gray-700">{{ $processo->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>

                    @if($processo->detalhe?->data_hora)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-blue-50 border border-blue-100">
                            <div>
                                <p class="text-[10px] font-bold text-blue-400 uppercase tracking-wider">Data da Sessão</p>
                                <p class="text-sm font-bold text-blue-700">{{ $processo->detalhe->data_hora->format('d/m/Y H:i') }}</p>
                            </div>
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    @endif

                    @if($processo->data_cancelamento)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-red-50 border border-red-100">
                            <div>
                                <p class="text-[10px] font-bold text-red-400 uppercase tracking-wider">Cancelado em</p>
                                <p class="text-sm font-bold text-red-700">{{ $processo->data_cancelamento->format('d/m/Y') }}</p>
                            </div>
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card: Documentos Gerados --}}
            <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-sm font-bold tracking-wider text-gray-400 uppercase">Documentos</h3>
                    <span class="px-2 py-0.5 text-[10px] font-bold bg-gray-100 text-gray-500 rounded-full uppercase tracking-tighter">
                        {{ $processo->documentos->count() }} arquivo(s)
                    </span>
                </div>
                <div class="p-2">
                    <div class="divide-y divide-gray-50">
                        @forelse($processo->documentos as $doc)
                            <div class="flex items-center justify-between p-3 transition-colors hover:bg-gray-50 rounded-xl group">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-gray-700 truncate max-w-[150px]" title="{{ $doc->tipo_documento }}">
                                            {{ ucwords(str_replace(['_', '-'], ' ', $doc->tipo_documento)) }}
                                        </p>
                                        <p class="text-[10px] text-gray-400">{{ $doc->gerado_em ? \Carbon\Carbon::parse($doc->gerado_em)->format('d/m/Y') : '—' }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('admin.processo.documento.dowload', ['processo' => $processo->id, 'tipo' => $doc->id]) }}"
                                    class="p-2 transition-colors opacity-0 text-gray-400 hover:text-teal-600 group-hover:opacity-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </a>
                            </div>
                        @empty
                            <div class="py-12 text-center">
                                <p class="text-xs font-medium text-gray-400 italic">Nenhum documento gerado.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
