@extends('Autenticar._layout')

@section('title', $resultado['status'] === 'autentico' ? 'Documento autêntico' : 'Código não encontrado')

@section('content')
    @if ($resultado['status'] === 'autentico')

        {{-- ========================================== --}}
        {{-- SUCESSO — documento autêntico                --}}
        {{-- ========================================== --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            {{-- Header verde --}}
            <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-8 py-6 text-white">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-3xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">Documento autêntico</h2>
                        <p class="text-sm text-emerald-100">
                            Verificado em {{ now()->format('d/m/Y \à\s H:i') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Metadados --}}
            <div class="px-8 py-6 border-b border-slate-200">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo de Documento</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">
                            {{ $resultado['documento_tipo'] ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Versão</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">
                            v{{ $resultado['versao_numero'] ?? '—' }}
                            @if ($resultado['versao'] && $resultado['versao']->isConsolidada())
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-800 rounded">
                                    Final
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Gerado em</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">{{ $resultado['gerado_em'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Código verificador</dt>
                        <dd class="mt-1">
                            <code class="text-xs px-2 py-1 bg-slate-100 rounded font-mono">
                                {{ $codigo }}
                            </code>
                        </dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Hash SHA-256</dt>
                        <dd class="mt-1">
                            <code class="text-xs break-all px-2 py-1 bg-slate-100 rounded font-mono text-slate-700">
                                {{ $resultado['hash'] ?? '—' }}
                            </code>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Lista de assinantes --}}
            <div class="px-8 py-6">
                <h3 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-signature text-[#009496]"></i>
                    Assinantes
                    <span class="text-sm font-normal text-slate-500">
                        ({{ $resultado['assinaturas']->count() }})
                    </span>
                </h3>

                <ul class="space-y-3">
                    @foreach ($resultado['assinaturas'] as $ass)
                        @php
                            $meta = is_array($ass->metadados) ? $ass->metadados : [];
                            $ehReferenciada = $resultado['assinatura_referenciada']?->id === $ass->id;
                        @endphp
                        <li class="border {{ $ehReferenciada ? 'border-[#009496] bg-teal-50/50' : 'border-slate-200' }} rounded-lg p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $meta['nome'] ?? optional($ass->assinante)->name ?? '—' }}
                                        @if ($ehReferenciada)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 text-xs font-medium bg-[#009496] text-white rounded">
                                                Código consultado
                                            </span>
                                        @endif
                                    </p>

                                    <div class="mt-1 text-xs text-slate-500 space-y-0.5">
                                        @if (!empty($meta['numero_portaria']))
                                            <p>Portaria {{ $meta['numero_portaria'] }}</p>
                                        @endif
                                        @if (!empty($meta['cargo']))
                                            <p>{{ $meta['cargo'] }}</p>
                                        @endif
                                        @if (!empty($meta['prefeitura']))
                                            <p>{{ $meta['prefeitura'] }}</p>
                                        @endif
                                    </div>

                                    <p class="mt-2 text-xs text-slate-600">
                                        <i class="fas fa-clock mr-1"></i>
                                        Assinado em {{ $ass->assinado_em->format('d/m/Y \à\s H:i:s') }}
                                    </p>

                                    <p class="mt-1">
                                        <code class="text-xs px-1.5 py-0.5 bg-slate-100 rounded font-mono">
                                            {{ $ass->codigo_verificador }}
                                        </code>
                                        <span class="text-xs text-slate-400 ml-1">CRC: {{ $ass->crc_humano }}</span>
                                    </p>
                                </div>

                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Ações --}}
            @if (!empty($resultado['download_disponivel']))
                <div class="px-8 py-5 bg-slate-50 border-t border-slate-200 flex flex-wrap gap-3 justify-end">
                    <a href="{{ route('autenticar.formulario') }}"
                       class="px-5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-100">
                        Nova consulta
                    </a>
                    <a href="{{ route('autenticar.download', $codigo) }}"
                       target="_blank"
                       class="px-5 py-2 text-sm font-semibold text-white bg-[#009496] rounded-md hover:bg-[#007779] flex items-center gap-2">
                        <i class="fas fa-download"></i>
                        Baixar PDF assinado
                    </a>
                </div>
            @endif
        </div>

    @else

        {{-- ========================================== --}}
        {{-- FALHA — código não encontrado                --}}
        {{-- ========================================== --}}
        <div class="max-w-xl mx-auto">
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-red-500 to-red-600 px-8 py-6 text-white">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-times-circle text-3xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">Código não encontrado</h2>
                            <p class="text-sm text-red-100">Verifique se digitou corretamente</p>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <p class="text-sm text-slate-700 mb-4">
                        O código <code class="px-2 py-0.5 bg-slate-100 rounded font-mono text-xs">{{ $codigo }}</code>
                        não corresponde a nenhuma assinatura registrada no sistema.
                    </p>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                        <h4 class="text-sm font-semibold text-amber-900 mb-2">
                            <i class="fas fa-triangle-exclamation mr-1"></i>
                            Possíveis causas
                        </h4>
                        <ul class="text-xs text-amber-900 list-disc list-inside space-y-1">
                            <li>O código foi digitado com erro</li>
                            <li>O documento não foi assinado por este sistema</li>
                            <li>O documento foi assinado mas a versão foi cancelada/recusada</li>
                            <li>O documento pode ter sido <strong>adulterado</strong> e o código não confere</li>
                        </ul>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('autenticar.formulario') }}"
                           class="px-5 py-2 text-sm font-semibold text-white bg-[#009496] rounded-md hover:bg-[#007779]">
                            Tentar outro código
                        </a>
                    </div>
                </div>
            </div>
        </div>

    @endif
@endsection
