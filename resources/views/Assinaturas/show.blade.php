@extends('layouts.app')

@section('page-title', 'Assinatura de Documento')
@php
    $documentavelNome = 'Documento';
    if ($versao && $versao->documentavel) {
        $doc = $versao->documentavel;
        if (class_basename($doc) === 'Documento' && !empty($doc->tipo_documento)) {
            $documentavelNome = ucfirst(str_replace('_', ' ', $doc->tipo_documento));
        } else {
            $documentavelNome = class_basename($doc);
        }
    }
@endphp
@section('page-subtitle', $documentavelNome . ' — versão ' . $versao->versao)

@section('content')
    <div x-data="{ mostrarModalRecusa: false }" class="space-y-6">

        @if (session('success'))
            <div class="p-4 rounded-lg bg-green-50 border border-green-200">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-lg bg-red-50 border border-red-200">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-lg bg-red-50 border border-red-200">
                <ul class="text-sm text-red-700 list-disc list-inside">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- COLUNA ESQUERDA: PDF embedded --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-3 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">Documento para revisão</h3>
                    <a href="{{ $urlPdf }}" target="_blank"
                       class="text-xs font-medium text-[#009496] hover:underline">
                        Abrir em nova aba ↗
                    </a>
                </div>
                <div class="bg-gray-100" style="height: 70vh;">
                    <embed src="{{ $urlPdf }}" type="application/pdf" class="w-full h-full">
                </div>
            </div>

            {{-- COLUNA DIREITA: dados + ações --}}
            <div class="space-y-4">

                {{-- Dados da solicitação --}}
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3 pb-2 border-b border-gray-200">
                        Dados da Solicitação
                    </h4>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Status</dt>
                            <dd>
                                @php
                                    $statusBadges = [
                                        'pendente'  => 'bg-amber-100 text-amber-800',
                                        'assinada'  => 'bg-emerald-100 text-emerald-800',
                                        'recusada'  => 'bg-red-100 text-red-800',
                                        'cancelada' => 'bg-gray-100 text-gray-700',
                                        'expirada'  => 'bg-amber-100 text-amber-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium {{ $statusBadges[$solicitacao->status] }} rounded">
                                    {{ ucfirst($solicitacao->status) }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Modo</dt>
                            <dd class="font-medium text-gray-800">
                                {{ $solicitacao->ordem > 0 ? "Sequencial #{$solicitacao->ordem}" : 'Paralelo' }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Solicitado por</dt>
                            <dd class="font-medium text-gray-800 text-right">
                                {{ optional($solicitacao->solicitadoPor)->name ?? '—' }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Solicitado em</dt>
                            <dd class="font-medium text-gray-800 text-right">
                                {{ optional($solicitacao->solicitado_em)->format('d/m/Y H:i') }}
                            </dd>
                        </div>
                        @if ($solicitacao->expires_at)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Prazo</dt>
                                <dd class="font-medium {{ $solicitacao->estaExpirada() ? 'text-red-600' : 'text-gray-800' }} text-right">
                                    {{ $solicitacao->expires_at->format('d/m/Y') }}
                                    @if ($solicitacao->estaExpirada())
                                        <span class="text-xs">(expirado)</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Ações (somente se ainda assinável) --}}
                @if ($solicitacao->podeSerAssinada())
                    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-[#009496]">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Sua ação</h4>
                        <p class="text-xs text-gray-500 mb-4">
                            Ao assinar, você declara que revisou o documento e concorda com seu conteúdo.
                            A assinatura é juridicamente vinculante.
                        </p>

                        <form method="POST" action="{{ route('minhas-assinaturas.assinar', $solicitacao->id) }}"
                              onsubmit="return confirm('Confirma a assinatura digital deste documento?');">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-3 text-sm font-semibold text-white bg-[#009496] rounded-md hover:bg-[#007779] flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Assinar Documento
                            </button>
                        </form>

                        <button type="button"
                                @click="mostrarModalRecusa = true"
                                class="w-full mt-2 px-4 py-2 text-sm font-medium text-red-700 bg-red-50 rounded-md hover:bg-red-100">
                            Recusar Assinatura
                        </button>
                    </div>
                @endif

                {{-- Histórico de assinaturas nesta versão --}}
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3 pb-2 border-b border-gray-200">
                        Assinaturas Nesta Versão
                        <span class="ml-1 text-xs font-normal text-gray-500">
                            ({{ $assinaturasAnteriores->count() }})
                        </span>
                    </h4>

                    @if ($assinaturasAnteriores->isEmpty())
                        <p class="text-xs text-gray-500">
                            Nenhuma assinatura registrada ainda.
                        </p>
                    @else
                        <ul class="space-y-3">
                            @foreach ($assinaturasAnteriores as $ass)
                                <li class="border-l-2 border-emerald-300 pl-3 py-1">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ optional($ass->assinante)->name ?? ($ass->metadados['nome'] ?? '—') }}
                                    </p>
                                    @if (!empty($ass->metadados['numero_portaria']))
                                        <p class="text-xs text-gray-500">
                                            Portaria {{ $ass->metadados['numero_portaria'] }}
                                        </p>
                                    @endif
                                    <p class="text-xs text-gray-500">
                                        Em {{ $ass->assinado_em->format('d/m/Y \à\s H:i') }}
                                    </p>
                                    <code class="text-xs px-1.5 py-0.5 bg-gray-100 rounded font-mono">
                                        {{ $ass->codigo_verificador }}
                                    </code>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <a href="{{ route('minhas-assinaturas.index') }}"
                   class="block text-center text-xs text-gray-500 hover:text-gray-700">
                    ← Voltar para Minhas Assinaturas
                </a>
            </div>
        </div>

        {{-- MODAL DE RECUSA --}}
        <div x-show="mostrarModalRecusa"
             x-cloak
             x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4"
             @keydown.escape.window="mostrarModalRecusa = false">

            <div @click.outside="mostrarModalRecusa = false"
                 class="w-full max-w-lg bg-white rounded-2xl shadow-2xl">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recusar Assinatura</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Ao recusar, <strong>toda a rodada será cancelada</strong>. Outros assinantes deste documento
                        não poderão mais assiná-lo. O operador precisará gerar uma nova versão.
                    </p>
                </div>

                <form method="POST" action="{{ route('minhas-assinaturas.recusar', $solicitacao->id) }}"
                      class="p-6 space-y-4">
                    @csrf

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">
                            Motivo da recusa <span class="text-red-500">*</span>
                        </label>
                        <textarea name="motivo" rows="4" required minlength="5" maxlength="500"
                                  placeholder="Ex.: Valor da contratação difere do aprovado..."
                                  class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-400"></textarea>
                        <p class="mt-1 text-xs text-gray-500">Entre 5 e 500 caracteres.</p>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="mostrarModalRecusa = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                onclick="return confirm('Tem certeza? Toda a rodada será cancelada.');"
                                class="px-6 py-2 text-sm font-semibold text-white bg-red-600 rounded-md hover:bg-red-700">
                            Confirmar Recusa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
