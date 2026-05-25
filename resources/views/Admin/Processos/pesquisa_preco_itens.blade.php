@extends('layouts.app')
@section('page-title', 'Pesquisa de Preço por Item')
@section('page-subtitle', 'Selecione um item para buscar referências de preço no PNCP')

@section('content')
<div class="px-6 pb-10">

    {{-- ── CABEÇALHO / BREADCRUMB ─────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.processos.edit', $processo->id) }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Voltar ao Processo
            </a>
            <div class="text-gray-300">›</div>
            <span class="text-sm font-semibold text-gray-700">Pesquisa de Preço por Item</span>
        </div>

        <a href="{{ route('admin.pesquisa_preco.index', ['processo_id' => $processo->id]) }}"
           target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            Abrir Pesquisa sem filtro
        </a>
    </div>

    {{-- ── CARD DO PROCESSO ──────────────────────────────────────────── --}}
    <div class="mb-6 p-4 bg-white border border-gray-200 rounded-xl shadow-sm flex items-center gap-5">
        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-[#009496]/10 text-[#009496] flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Processo</p>
            <p class="text-lg font-extrabold text-gray-800 truncate">{{ $processo->numero_processo }}</p>
            <p class="text-xs text-gray-500 mt-0.5">
                {{ $processo->modalidade?->getDisplayName() ?? $processo->modalidade }}
                @if($processo->tipo_contratacao)
                    · {{ $processo->tipo_contratacao?->getDisplayName() ?? $processo->tipo_contratacao }}
                @endif
            </p>
        </div>
        <div class="flex-shrink-0 text-right">
            @php $countPncp = $processo->pesquisaPrecoItens()->count(); @endphp
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Já coletados</p>
            <p class="text-2xl font-extrabold {{ $countPncp > 0 ? 'text-green-600' : 'text-gray-300' }}">
                {{ $countPncp }}
            </p>
            <p class="text-xs text-gray-400">{{ Str::plural('item', $countPncp) }} no relatório</p>
        </div>
    </div>

    {{-- ── ORIGEM DOS ITENS ─────────────────────────────────────────── --}}
    @if($fonte === 'etp' && $etpInfo)
    <div class="mb-5 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-3">
        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
            <i class="fas fa-link text-sm"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-emerald-800">
                Itens originados do ETP Inteligente
                <span class="ml-1 font-normal text-emerald-700">
                    ETP-{{ str_pad($etpInfo->id, 4, '0', STR_PAD_LEFT) }}/{{ $etpInfo->created_at->format('Y') }}
                </span>
            </p>
            <p class="text-[11px] text-emerald-600">{{ $itens->count() }} {{ Str::plural('item', $itens->count()) }} vinculados</p>
        </div>
        <div class="ml-auto">
            <a href="{{ route('admin.etps.show', $etpInfo->id) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-white border border-emerald-200 rounded-lg hover:bg-emerald-50 transition-all">
                <i class="fas fa-eye"></i> Ver ETP
            </a>
        </div>
    </div>
    @elseif($fonte === 'xls')
    <div class="mb-5 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center gap-3">
        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
            <i class="fas fa-file-excel text-sm"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-blue-800">Itens importados via arquivo XLS/XML</p>
            <p class="text-[11px] text-blue-600">{{ $itens->count() }} {{ Str::plural('item', $itens->count()) }} importados</p>
        </div>
    </div>
    @endif

    {{-- ── LISTA DE ITENS ───────────────────────────────────────────── --}}
    @if($itens->isEmpty())
    <div class="flex flex-col items-center justify-center py-24 text-gray-400">
        <div class="p-5 bg-gray-50 rounded-full mb-4">
            <svg class="w-12 h-12 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        </div>
        <p class="text-base font-semibold">Nenhum item encontrado</p>
        <p class="text-sm mt-1">Vincule um ETP ou importe um arquivo XLS no processo para usar esta funcionalidade.</p>
        <a href="{{ route('admin.processos.edit', $processo->id) }}"
           class="mt-5 inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all">
            Ir para o Processo
        </a>
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-gray-700">Itens para pesquisa de preço</h2>
                <p class="text-xs text-gray-400 mt-0.5">Clique em "Buscar no PNCP" para abrir a pesquisa com o termo pré-preenchido</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#009496]/10 text-[#009496] text-xs font-bold rounded-full">
                {{ $itens->count() }} {{ Str::plural('item', $itens->count()) }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 w-12 text-center">#</th>
                        <th class="px-4 py-3">Descrição do Item</th>
                        <th class="px-4 py-3 w-24 text-center">Unidade</th>
                        <th class="px-4 py-3 w-24 text-right">Quantidade</th>
                        <th class="px-4 py-3 w-44 text-center">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($itens as $idx => $item)
                    <tr class="bg-white hover:bg-gray-50/70 transition-colors group">
                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#009496]/10 text-[#009496] text-xs font-bold">
                                {{ $idx + 1 }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="text-gray-800 font-medium leading-snug">{{ $item['descricao'] }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 text-xs font-semibold">
                                {{ $item['unidade'] ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right font-semibold text-gray-700">
                            {{ number_format($item['quantidade'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <a href="{{ route('admin.pesquisa_preco.index', ['processo_id' => $processo->id, 'termo' => $item['descricao']]) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-all shadow-sm group-hover:shadow-md">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Buscar no PNCP
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Rodapé com link para pesquisa geral --}}
        <div class="px-5 py-3.5 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
            <p class="text-xs text-gray-400">
                Cada botão abre uma nova aba com a pesquisa já iniciada para aquele item.
            </p>
            <a href="{{ route('admin.pesquisa_preco.index', ['processo_id' => $processo->id]) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Pesquisa livre no PNCP
            </a>
        </div>
    </div>
    @endif

</div>
@endsection
