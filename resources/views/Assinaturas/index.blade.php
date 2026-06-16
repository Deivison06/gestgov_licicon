@extends('layouts.app')

@section('page-title', 'Minhas Assinaturas')
@section('page-subtitle', 'Solicitações pendentes e histórico recente')

@section('content')
    @if (session('success'))
        <div class="p-4 mb-6 rounded-lg bg-green-50 border border-green-200">
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 mb-6 rounded-lg bg-red-50 border border-red-200">
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Resumo numérico --}}
    <div class="grid grid-cols-1 gap-4 mb-8 md:grid-cols-3">
        <div class="p-5 bg-white rounded-xl shadow-sm border-l-4 border-amber-500">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pendentes</p>
            <p class="mt-1 text-3xl font-bold text-amber-600">{{ $pendentes->total() }}</p>
            <p class="mt-1 text-xs text-gray-500">Aguardando sua assinatura</p>
        </div>

        <div class="p-5 bg-white rounded-xl shadow-sm border-l-4 border-emerald-500">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Assinadas</p>
            <p class="mt-1 text-3xl font-bold text-emerald-600">
                {{ $historico->filter(fn($h) => $h->status === \App\Models\SolicitacaoAssinatura::STATUS_ASSINADA)->count() }}
            </p>
            <p class="mt-1 text-xs text-gray-500">Nas últimas 10</p>
        </div>

        <div class="p-5 bg-white rounded-xl shadow-sm border-l-4 border-red-400">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Recusadas / Canceladas</p>
            <p class="mt-1 text-3xl font-bold text-red-500">
                {{ $historico->filter(fn($h) => in_array($h->status, [
                    \App\Models\SolicitacaoAssinatura::STATUS_RECUSADA,
                    \App\Models\SolicitacaoAssinatura::STATUS_CANCELADA,
                    \App\Models\SolicitacaoAssinatura::STATUS_EXPIRADA,
                ])) ->count() }}
            </p>
            <p class="mt-1 text-xs text-gray-500">Nas últimas 10</p>
        </div>
    </div>

    {{-- Pendentes --}}
    <div class="mb-8 overflow-hidden bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-amber-50 to-white">
            <h3 class="text-lg font-semibold text-gray-800">Solicitações Pendentes</h3>
            <p class="mt-1 text-xs text-gray-500">Ordenadas por urgência (prazo mais próximo no topo)</p>
        </div>

        @if ($pendentes->isEmpty())
            <div class="p-10 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-medium">Nenhuma solicitação pendente.</p>
                <p class="text-xs text-gray-400 mt-1">Quando alguém te enviar um documento para assinar, aparecerá aqui.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase tracking-wider">Documento</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase tracking-wider">Solicitado por</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase tracking-wider">Modo</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase tracking-wider">Prazo</th>
                            <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase tracking-wider">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($pendentes as $sol)
                            @php
                                $diasRestantes = $sol->expires_at ? now()->diffInDays($sol->expires_at, false) : null;
                                $urgencia = match (true) {
                                    $diasRestantes === null    => 'gray',
                                    $diasRestantes < 0          => 'red',
                                    $diasRestantes <= 1         => 'red',
                                    $diasRestantes <= 3         => 'amber',
                                    default                     => 'emerald',
                                };
                                $documentavelNome = 'Documento';
                                if ($sol->versao && $sol->versao->documentavel) {
                                    $doc = $sol->versao->documentavel;
                                    if (class_basename($doc) === 'Documento' && !empty($doc->tipo_documento)) {
                                        $documentavelNome = ucfirst(str_replace('_', ' ', $doc->tipo_documento));
                                    } else {
                                        $documentavelNome = class_basename($doc);
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $documentavelNome }}
                                        @if ($sol->versao)
                                            <span class="text-xs font-normal text-gray-500">— v{{ $sol->versao->versao }}</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Solicitado {{ optional($sol->solicitado_em)->diffForHumans() }}
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-700">
                                    {{ optional($sol->solicitadoPor)->name ?? '—' }}
                                </td>
                                <td class="px-6 py-3">
                                    @if ($sol->ordem > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-800 rounded">
                                            Sequencial #{{ $sol->ordem }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-700 rounded">
                                            Paralelo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3">
                                    @if ($sol->expires_at)
                                        @php
                                            $bgClass = ['gray' => 'bg-gray-100 text-gray-700',
                                                        'emerald' => 'bg-emerald-100 text-emerald-800',
                                                        'amber' => 'bg-amber-100 text-amber-800',
                                                        'red' => 'bg-red-100 text-red-800'][$urgencia];
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium {{ $bgClass }} rounded">
                                            @if ($diasRestantes < 0)
                                                Vencido há {{ abs(floor($diasRestantes)) }}d
                                            @elseif ($diasRestantes < 1)
                                                Hoje
                                            @else
                                                {{ floor($diasRestantes) }}d restantes
                                            @endif
                                        </span>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            {{ $sol->expires_at->format('d/m/Y') }}
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">Sem prazo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <a href="{{ route('minhas-assinaturas.show', $sol->id) }}"
                                       class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-[#009496] rounded-md hover:bg-[#007779]">
                                        Revisar e assinar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($pendentes->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $pendentes->appends(['historico' => request('historico')])->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Histórico --}}
    <div class="overflow-hidden bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Histórico Recente</h3>
            <p class="mt-1 text-xs text-gray-500">Suas últimas movimentações</p>
        </div>

        @if ($historico->isEmpty())
            <div class="p-8 text-center text-sm text-gray-500">
                Nenhuma movimentação registrada ainda.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-2 text-left">Documento</th>
                            <th class="px-6 py-2 text-left">Status</th>
                            <th class="px-6 py-2 text-left">Processada em</th>
                            <th class="px-6 py-2 text-left">Código verificador</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($historico as $sol)
                            @php
                                $statusBadges = [
                                    'assinada'  => 'bg-emerald-100 text-emerald-800',
                                    'recusada'  => 'bg-red-100 text-red-800',
                                    'cancelada' => 'bg-gray-100 text-gray-700',
                                    'expirada'  => 'bg-amber-100 text-amber-800',
                                ];
                                $badge = $statusBadges[$sol->status] ?? 'bg-gray-100 text-gray-700';

                                $documentavelNome = 'Documento';
                                if ($sol->versao && $sol->versao->documentavel) {
                                    $doc = $sol->versao->documentavel;
                                    if (class_basename($doc) === 'Documento' && !empty($doc->tipo_documento)) {
                                        $documentavelNome = ucfirst(str_replace('_', ' ', $doc->tipo_documento));
                                    } else {
                                        $documentavelNome = class_basename($doc);
                                    }
                                }
                            @endphp
                            <tr>
                                <td class="px-6 py-3 text-gray-800">
                                    {{ $documentavelNome }}
                                    @if ($sol->versao)
                                        <span class="text-xs text-gray-500">— v{{ $sol->versao->versao }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium {{ $badge }} rounded">
                                        {{ ucfirst($sol->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-gray-600">
                                    {{ optional($sol->processada_em)->format('d/m/Y H:i') ?? '—' }}
                                </td>
                                <td class="px-6 py-3">
                                    @if ($sol->assinatura)
                                        <code class="text-xs px-2 py-1 bg-gray-100 rounded font-mono">
                                            {{ $sol->assinatura->codigo_verificador }}
                                        </code>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($historico->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $historico->appends(['pendentes' => request('pendentes')])->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
