@extends('layouts.app')
@section('page-title', $processo->numero_processo ?? $processo->numero_procedimento ?? 'Processo')
@section('page-subtitle', $processo->prefeitura->nome ?? '')

@section('content')
@php
    $cfg                = $statusConfig[$processo->planejamento_status];
    $aguardandoResposta = $processo->aguardandoRespostaRecurso();
    $ordemStatus        = array_keys($statusConfig);
    $indiceAtual        = array_search($processo->planejamento_status, $ordemStatus);
    $totalDocs          = $checklist->count();
    $geradosDocs        = $checklist->where('gerado', true)->count();
    $percentual         = $totalDocs > 0 ? round(($geradosDocs / $totalDocs) * 100) : 0;
@endphp

<div class="py-6 px-4 sm:px-6 lg:px-8"
    x-data="{
        modalAgendar: false,
        modalNota: {{ $errors->hasAny(['texto', 'anexo']) ? 'true' : 'false' }},
        processoId: {{ $processo->id }},
        dataMinima: '{{ now()->toDateString() }}'
    }">

    <div class="space-y-5">

        {{-- Cabeçalho --}}
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.planejamento.index') }}"
                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 font-mono">
                        {{ $processo->numero_processo ?? $processo->numero_procedimento ?? '#'.$processo->id }}
                    </h2>
                    <p class="text-sm text-gray-500">{{ $processo->prefeitura->nome ?? '—' }}</p>
                </div>
            </div>
            <span class="text-sm font-bold px-3 py-1.5 rounded-full {{ $cfg['cor_badge'] }}">
                {{ $cfg['label'] }}
            </span>
        </div>

        {{-- Flash --}}
        @if(session('sucesso'))
            <div x-data="{ show: true }" x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="flex items-center justify-between gap-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="shrink-0 w-7 h-7 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    {{ session('sucesso') }}
                </div>
                <button @click="show = false" class="text-green-500 hover:text-green-700 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        <div class="space-y-4">

            {{-- Dados gerais --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="h-1 {{ $cfg['cor_head'] }}"></div>
                <div class="p-5 space-y-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Dados do Processo</h3>
                        <a href="{{ route('admin.processos.show', $processo) }}"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-600 hover:text-teal-800 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Ver processo completo
                        </a>
                    </div>

                    @if($processo->nome_resumido)
                        <div>
                            <p class="text-xs text-gray-400 font-medium mb-1">Identificação de Controle / Nome Resumido</p>
                            <p class="text-gray-900 font-semibold leading-relaxed">{{ $processo->nome_resumido }}</p>
                        </div>
                        <hr class="border-gray-100">
                    @endif

                    @if($processo->objeto)
                        <div>
                            <p class="text-xs text-gray-400 font-medium mb-1">Objeto</p>
                            <p class="text-gray-800 leading-relaxed">{{ html_entity_decode(strip_tags($processo->objeto)) }}</p>
                        </div>
                        <hr class="border-gray-100">
                    @endif

                    <div class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                        <div>
                            <p class="text-xs text-gray-400 font-medium mb-1">Prefeitura</p>
                            <p class="text-gray-900 font-semibold">{{ $processo->prefeitura->nome ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium mb-1">Número</p>
                            <p class="text-gray-900 font-mono font-semibold">{{ $processo->numero_processo ?? $processo->numero_procedimento ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium mb-1">Modalidade</p>
                            <p class="text-gray-800">{{ $processo->modalidade?->getDisplayName() ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium mb-1">Tipo de Contratação</p>
                            <p class="text-gray-800">{{ $processo->tipo_contratacao_nome }}</p>
                        </div>
                        @if($processo->planejamento_data_abertura)
                            <div>
                                <p class="text-xs text-gray-400 font-medium mb-1">Data de Abertura</p>
                                <p class="text-gray-900 font-semibold">{{ $processo->planejamento_data_abertura->format('d/m/Y') }}</p>
                            </div>
                        @endif
                        @if($processo->planejamento_fim_recurso)
                            <div>
                                <p class="text-xs text-gray-400 font-medium mb-1">Prazo do Recurso</p>
                                <p class="font-semibold {{ $aguardandoResposta ? 'text-red-600' : 'text-orange-600' }}">
                                    {{ $processo->planejamento_fim_recurso->format('d/m/Y') }}
                                    @if($aguardandoResposta)
                                        <span class="ml-1 text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full font-bold">vencido</span>
                                    @endif
                                </p>
                            </div>
                        @endif
                        <div>
                            <p class="text-xs text-gray-400 font-medium mb-1">Criado em</p>
                            <p class="text-gray-700">{{ $processo->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium mb-1">Última atualização</p>
                            <p class="text-gray-700">{{ $processo->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Linha do tempo + ações contextuais --}}
            @if(! in_array($processo->modalidade, [\App\Enums\ModalidadeEnum::INEXIGIBILIDADE, \App\Enums\ModalidadeEnum::DISPENSA]))
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-5">Linha do Tempo</h3>
                <ol class="flex items-start w-full">
                    @foreach($statusConfig as $status => $cfgTl)
                        @php $indice = array_search($status, $ordemStatus); @endphp
                        <li class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
                            <div class="flex flex-col items-center">
                                <div @class([
                                    'w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold shrink-0 ring-2 ring-offset-2',
                                    'text-white ring-current ' . $cfgTl['cor_head'] => $indice === $indiceAtual,
                                    'text-white ring-transparent opacity-70 ' . $cfgTl['cor_head'] => $indice < $indiceAtual,
                                    'bg-gray-100 text-gray-400 ring-transparent' => $indice > $indiceAtual,
                                ])>
                                    @if($indice < $indiceAtual)
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        {{ $indice + 1 }}
                                    @endif
                                </div>
                                <span @class([
                                    'text-xs mt-2 text-center leading-tight max-w-[72px]',
                                    'font-bold text-gray-900' => $indice === $indiceAtual,
                                    'text-gray-500'           => $indice < $indiceAtual,
                                    'text-gray-300'           => $indice > $indiceAtual,
                                ])>{{ $cfgTl['label'] }}</span>
                            </div>
                            @if(!$loop->last)
                                <div @class([
                                    'flex-1 h-0.5 mx-2 mb-5 rounded-full',
                                    'bg-gray-400' => $indice < $indiceAtual,
                                    'bg-gray-200' => $indice >= $indiceAtual,
                                ])></div>
                            @endif
                        </li>
                    @endforeach
                </ol>

                {{-- Ações contextuais ao status atual --}}
                @if(auth()->user()->hasDirectPermission('planejamento'))
                <div class="mt-5 pt-5 border-t border-gray-100 flex flex-wrap gap-3">

                    @if($processo->planejamento_status === 'em_elaboracao')
                        <button @click="modalAgendar = true"
                            class="inline-flex items-center gap-2 text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-xl px-5 py-2.5 transition-colors shadow-sm">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Agendar Sessão
                        </button>
                    @endif

                    @if($processo->planejamento_status === 'aguardando_sessao')
                        <div class="flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Aguardando avanço automático na data de abertura
                        </div>
                    @endif

                    @if($processo->planejamento_status === 'em_andamento')
                        <form method="POST" action="{{ route('admin.planejamento.status.update', $processo) }}"
                            onsubmit="return confirm('Confirmar conclusão da sessão deste processo?')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="planejamento_status" value="concluida">
                            <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold bg-green-600 hover:bg-green-700 text-white rounded-xl px-5 py-2.5 transition-colors shadow-sm">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Concluir Sessão
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.planejamento.status.update', $processo) }}"
                            onsubmit="return confirm('Confirmar início do prazo de recurso? O prazo de 3 dias úteis começará a contar agora.')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="planejamento_status" value="em_recurso">
                            <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold bg-orange-500 hover:bg-orange-600 text-white rounded-xl px-5 py-2.5 transition-colors shadow-sm">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Iniciar Recurso
                            </button>
                        </form>
                    @endif

                    @if($processo->planejamento_status === 'em_recurso' && $aguardandoResposta)
                        <form method="POST" action="{{ route('admin.planejamento.status.update', $processo) }}"
                            onsubmit="return confirm('Confirmar encaminhamento para conclusão?')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="planejamento_status" value="concluida">
                            <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold bg-slate-600 hover:bg-slate-700 text-white rounded-xl px-5 py-2.5 transition-colors shadow-sm">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                Encaminhar para Conclusão
                            </button>
                        </form>
                    @endif

                    @if($processo->planejamento_status === 'em_recurso' && !$aguardandoResposta)
                        <div class="flex items-center gap-2 text-sm text-orange-700 bg-orange-50 border border-orange-200 rounded-xl px-4 py-2.5">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Prazo do recurso: {{ $processo->planejamento_fim_recurso?->format('d/m/Y') ?? '—' }}
                        </div>
                        <form method="POST" action="{{ route('admin.planejamento.finalizar.prazo.recurso', $processo) }}"
                            onsubmit="return confirm('Confirmar encerramento manual do prazo de recurso? O processo passará para aguardando resposta.')">
                            @csrf @method('PATCH')
                            <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold bg-orange-600 hover:bg-orange-700 text-white rounded-xl px-5 py-2.5 transition-colors shadow-sm">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Finalizar Prazo
                            </button>
                        </form>
                    @endif

                    @if($processo->planejamento_status === 'concluida')
                        <form method="POST" action="{{ route('admin.planejamento.status.update', $processo) }}"
                            onsubmit="return confirm('Confirmar início da finalização deste processo?')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="planejamento_status" value="finalizacao">
                            <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold bg-teal-700 hover:bg-teal-800 text-white rounded-xl px-5 py-2.5 transition-colors shadow-sm">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                                Iniciar Finalização
                            </button>
                        </form>
                    @endif

                    @if($processo->planejamento_status === 'finalizacao')
                        <div class="flex items-center gap-3 text-sm border rounded-xl px-4 py-2.5 bg-teal-50 border-teal-200 text-teal-800">
                            <svg class="w-4 h-4 shrink-0 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>
                                Finalização iniciada
                                @if($processo->finalizacaoIniciador)
                                    por <strong>{{ $processo->finalizacaoIniciador->name }}</strong>
                                @endif
                                @if($processo->finalizacao_iniciada_em)
                                    em {{ $processo->finalizacao_iniciada_em->format('d/m/Y \à\s H:i') }}
                                @endif
                            </span>
                        </div>
                    @endif

                </div>
                @endif {{-- hasDirectPermission('planejamento') --}}
            </div>
            @endif

            {{-- Notas --}}
            <div id="notas" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Notas</h3>
                        @if($processo->notas->count() > 0)
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $processo->notas->count() }} {{ $processo->notas->count() === 1 ? 'registro' : 'registros' }}
                            </p>
                        @endif
                    </div>
                    <button @click="modalNota = true"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold bg-teal-700 hover:bg-teal-800 text-white rounded-lg px-3 py-1.5 transition-colors shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nova Nota
                    </button>
                </div>

                @if($processo->notas->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 text-center px-4">
                        <svg class="w-10 h-10 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-400">Nenhuma nota ainda</p>
                        <p class="text-xs text-gray-300 mt-1">Registre observações sobre o andamento.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 divide-y sm:divide-y-0">
                        @foreach($processo->notas as $nota)
                            @php $notaCfg = $statusConfig[$nota->status_em_vigor] ?? null; @endphp
                            <div class="px-5 py-4 space-y-2 border-b border-gray-50
                                {{ !$loop->last ? 'sm:border-r' : '' }}
                                {{ $loop->iteration > 2 ? 'sm:border-t' : '' }}
                                {{ $loop->iteration > 3 ? 'xl:border-t' : '' }}">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ strtoupper(substr($nota->user->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-gray-700 truncate">{{ $nota->user->name ?? '—' }}</p>
                                        <p class="text-xs text-gray-400">{{ $nota->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    @if($notaCfg)
                                        <span class="shrink-0 text-xs px-2 py-0.5 rounded-full font-medium {{ $notaCfg['cor_badge'] }}">
                                            {{ $notaCfg['label'] }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-700 leading-snug pl-9">{{ $nota->texto }}</p>
                                @if($nota->hasAnexo())
                                    <div class="pl-9">
                                        <a href="{{ Storage::url($nota->anexo_path) }}" target="_blank" download="{{ $nota->anexo_nome }}"
                                            class="inline-flex items-center gap-2 text-xs font-medium text-teal-600 hover:text-teal-800 bg-teal-50 hover:bg-teal-100 border border-teal-100 rounded-lg px-3 py-1.5 transition-colors max-w-full">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                            </svg>
                                            <span class="truncate">{{ $nota->anexo_nome }}</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Checklist de Documentos --}}
            @if($checklist->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Documentos do Processo</h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $geradosDocs }} de {{ $totalDocs }} {{ $totalDocs === 1 ? 'documento gerado' : 'documentos gerados' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('admin.processos.iniciar', $processo) }}"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold bg-[#052323] hover:bg-[#0a3a3a] text-white rounded-lg px-3 py-1.5 transition-colors shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Gerar Documentos
                        </a>
                        <div class="w-28 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500
                                {{ $percentual === 100 ? 'bg-green-500' : ($percentual >= 50 ? 'bg-teal-500' : 'bg-amber-400') }}"
                                style="width: {{ $percentual }}%"></div>
                        </div>
                        <span class="text-xs font-bold {{ $percentual === 100 ? 'text-green-600' : 'text-gray-500' }}">
                            {{ $percentual }}%
                        </span>
                    </div>
                </div>

                @php $pendentes = $checklist->where('gerado', false); @endphp
                @if($pendentes->isNotEmpty())
                    <div class="px-5 py-3 bg-amber-50 border-b border-amber-100 flex items-start gap-3">
                        <svg class="w-4 h-4 shrink-0 text-amber-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                        </svg>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-amber-800 mb-1">
                                {{ $pendentes->count() }} {{ $pendentes->count() === 1 ? 'documento pendente' : 'documentos pendentes' }}:
                            </p>
                            <p class="text-xs text-amber-700 leading-relaxed">
                                {{ $pendentes->map(fn($i) => Str::title(mb_strtolower($i['titulo'])))->join(', ') }}
                            </p>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x-0">
                    @foreach($checklist as $item)
                        <div class="flex items-start gap-3 px-4 py-3 border-b border-gray-50 last:border-b-0
                            {{ $loop->iteration % 2 === 0 ? 'sm:border-l border-gray-50' : '' }}">

                            {{-- Indicador --}}
                            @if($item['gerado'])
                                <div class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-green-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            @else
                                <div class="shrink-0 mt-0.5 w-5 h-5 rounded-full border-2 border-dashed border-gray-200"></div>
                            @endif

                            {{-- Título + data --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold leading-tight
                                    {{ $item['gerado'] ? 'text-gray-700' : 'text-gray-400' }}">
                                    {{ Str::title(mb_strtolower($item['titulo'])) }}
                                </p>
                                @if($item['gerado'] && $item['gerado_em'])
                                    <p class="text-[10px] text-gray-400 mt-0.5">
                                        {{ \Carbon\Carbon::parse($item['gerado_em'])->format('d/m/Y \à\s H:i') }}
                                    </p>
                                @elseif(!$item['gerado'])
                                    <p class="text-[10px] text-gray-300 mt-0.5">Pendente</p>
                                @endif
                            </div>

                            {{-- Cor do tipo --}}
                            <div class="shrink-0 w-1.5 h-1.5 rounded-full mt-1.5 opacity-60"
                                style="background-color: {{ $item['cor'] }}"></div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>{{-- /space-y-4 --}}

    </div>{{-- /space-y-5 --}}

    {{-- Modal: Agendar Sessão --}}
    @php $dataSessaoDetectada = $processo->detalhe?->data_hora?->toDateString(); @endphp
    <div x-show="modalAgendar"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4"
        @click.self="modalAgendar = false" style="display:none">
        <div x-show="modalAgendar"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden" @click.stop>
            <div class="h-1 bg-amber-500"></div>
            <div class="p-6 space-y-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Agendar Sessão</h3>
                        <p class="text-sm text-gray-500 mt-1">O processo avançará para <strong>Em Andamento</strong> automaticamente na meia-noite da data escolhida.</p>
                    </div>
                    <button @click="modalAgendar = false" class="shrink-0 text-gray-400 hover:text-gray-600 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.planejamento.status.update', $processo) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="planejamento_status" value="aguardando_sessao">
                    <div class="space-y-4">
                        @if($dataSessaoDetectada)
                            <div class="flex items-start gap-2.5 bg-amber-50 border border-amber-200 rounded-lg px-3.5 py-2.5">
                                <svg class="w-4 h-4 shrink-0 text-amber-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-xs text-amber-800">
                                    Data detectada automaticamente dos dados do processo:
                                    <strong>{{ $processo->detalhe->data_hora->format('d/m/Y') }}</strong>.
                                    Você pode alterá-la se necessário.
                                </p>
                            </div>
                        @endif
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Data de Abertura <span class="text-red-500">*</span></label>
                            <input type="date" name="data_abertura" required
                                value="{{ $dataSessaoDetectada ?? old('data_abertura') }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-amber-500 focus:border-amber-500">
                        </div>
                        <div class="flex justify-end gap-3 pt-1">
                            <button type="button" @click="modalAgendar = false" class="text-sm font-semibold text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">Cancelar</button>
                            <button type="submit" class="text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-lg px-5 py-2 transition-colors shadow-sm">Confirmar Agendamento</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Nova Nota --}}
    <div x-show="modalNota"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4"
        @click.self="modalNota = false" style="display:none">
        <div x-show="modalNota"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden" @click.stop>
            <div class="h-1 bg-teal-700"></div>
            <div class="p-6 space-y-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Nova Nota</h3>
                        <p class="text-sm text-gray-500 mt-0.5">
                            Processo <span class="font-mono font-semibold text-gray-700">{{ $processo->numero_processo ?? '#'.$processo->id }}</span>
                            &mdash; <span class="{{ $cfg['cor_badge'] }} text-xs font-semibold px-2 py-0.5 rounded-full">{{ $cfg['label'] }}</span>
                        </p>
                    </div>
                    <button @click="modalNota = false" class="shrink-0 text-gray-400 hover:text-gray-600 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @if($errors->hasAny(['texto', 'anexo']))
                    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                        <ul class="space-y-0.5">
                            @foreach($errors->get('texto') as $err) <li>{{ $err }}</li> @endforeach
                            @foreach($errors->get('anexo') as $err) <li>{{ $err }}</li> @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('admin.planejamento.notas.store', $processo) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="nota_texto" class="block text-sm font-semibold text-gray-700 mb-1.5">Observação <span class="text-red-500">*</span></label>
                        <textarea id="nota_texto" name="texto" rows="4" required maxlength="1000"
                            placeholder="Registre o andamento, decisões ou qualquer observação relevante..."
                            class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500 resize-none">{{ old('texto') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Arquivo <span class="ml-1 text-xs text-gray-400 font-normal">— opcional (PDF, Word, Excel, imagem · máx. 10 MB)</span></label>
                        <label x-data="{ nome: '' }" class="flex items-center gap-3 w-full cursor-pointer rounded-lg border border-dashed border-gray-300 hover:border-teal-400 hover:bg-teal-50/30 transition-colors px-4 py-3 group">
                            <div class="shrink-0 w-9 h-9 bg-gray-100 group-hover:bg-teal-100 rounded-lg flex items-center justify-center transition-colors">
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-teal-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-500 group-hover:text-teal-600 transition-colors" x-text="nome || 'Clique para selecionar ou arraste o arquivo'"></p>
                                <p class="text-xs text-gray-400" x-show="!nome">pdf, doc, docx, xls, xlsx, jpg, png</p>
                            </div>
                            <input type="file" name="anexo" class="sr-only" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif"
                                @change="nome = $event.target.files[0]?.name ?? ''">
                        </label>
                    </div>
                    <div class="flex justify-end gap-3 pt-1">
                        <button type="button" @click="modalNota = false" class="text-sm font-semibold text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">Cancelar</button>
                        <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold bg-teal-700 hover:bg-teal-800 text-white rounded-lg px-5 py-2.5 transition-colors shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Salvar Nota
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
