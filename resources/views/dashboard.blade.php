@extends('layouts.app')

@section('title', 'Dashboard - GestGov Licicon')

@section('content')
    <div class="py-2 space-y-8">
        <!-- Cabeçalho com Filtro de Cidade -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-[var(--ink)]">
                    @if($prefeitura)
                        Painel da GestGov — {{ $prefeitura->nome }}
                    @else
                        Painel Geral da GestGov
                    @endif
                </h2>
                <p class="mt-1 text-sm font-medium text-[var(--ink-3)]">Visão consolidada dos processos · {{ now()->format('d/m/Y') }}</p>
            </div>

            @if(auth()->user()->hasAnyRole(['diretor_licicon', 'gerente_licicon', 'colaborador_licicon']))
            <!-- Filtro de Cidade -->
            <div class="w-full md:w-72">
                <form action="{{ route('admin.dashboard') }}" method="GET">
                    <label for="filtro-cidade" class="sr-only">Filtrar por cidade</label>
                    <select id="filtro-cidade" name="cidade" onchange="this.form.submit()"
                            class="in !h-11 !rounded-xl !bg-[var(--card-2)] !border-[var(--line)] !text-[var(--ink)] focus:!border-[var(--mint)] focus:!ring-[var(--mint)]/25 text-sm font-medium shadow-sm">
                        <option value="">Todas as Cidades</option>
                        @foreach($prefeituras as $pref)
                            <option value="{{ $pref->id }}" {{ $prefeitura && $prefeitura->id == $pref->id ? 'selected' : '' }}>
                                {{ $pref->nome }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            @endif
        </div>

        <!-- Grade de Indicadores (KPI Cards no formato do SIT) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <!-- Cartão Pregões -->
            <div class="card kpi">
                <div class="top">
                    <span class="ic bg-[var(--info-bg)] text-[var(--info)]">
                        <i class="fas fa-gavel text-base"></i>
                    </span>
                    <span class="lab">Pregões</span>
                </div>
                <div class="num">{{ $pregoes }}</div>
                <div class="desc">Total de pregões</div>
            </div>

            <!-- Cartão Dispensas -->
            <div class="card kpi">
                <div class="top">
                    <span class="ic bg-[var(--ok-bg)] text-[var(--ok)]">
                        <i class="fas fa-file-signature text-base"></i>
                    </span>
                    <span class="lab">Dispensas</span>
                </div>
                <div class="num text-[var(--ok)]">{{ $dispensas }}</div>
                <div class="desc">Total de dispensas</div>
            </div>

            <!-- Cartão Inexigibilidades -->
            <div class="card kpi">
                <div class="top">
                    <span class="ic bg-[var(--vio-bg)] text-[var(--vio)]">
                        <i class="fas fa-ban text-base"></i>
                    </span>
                    <span class="lab">Inexigibilidades</span>
                </div>
                <div class="num text-[var(--vio)]">{{ $inexigibilidades }}</div>
                <div class="desc">Inexigibilidades ativas</div>
            </div>

            <!-- Cartão Concorrências -->
            <div class="card kpi">
                <div class="top">
                    <span class="ic bg-[var(--orange-bg)] text-[var(--orange)]">
                        <i class="fas fa-balance-scale text-base"></i>
                    </span>
                    <span class="lab">Concorrências</span>
                </div>
                <div class="num text-[var(--orange)]">{{ $concorrencia }}</div>
                <div class="desc">Concorrências públicas</div>
            </div>

            <!-- Cartão Em Andamento -->
            <div class="card kpi">
                <div class="top">
                    <span class="ic bg-[var(--warn-bg)] text-[var(--warn)]">
                        <i class="fas fa-spinner text-base"></i>
                    </span>
                    <span class="lab">Em Andamento</span>
                </div>
                <div class="num text-[var(--warn)]">{{ $emAndamento }}</div>
                <div class="desc">Em fase de análise</div>
            </div>

            <!-- Cartão Concluídos (SIT KPI Dark Variant com Mint) -->
            <div class="card kpi dark">
                <div class="top">
                    <span class="ic bg-white/10 text-[var(--mint)]">
                        <i class="fas fa-check-circle text-base"></i>
                    </span>
                    <span class="lab">Concluídos</span>
                </div>
                <div class="num text-[var(--mint)]">{{ $finalizadosTotal }}</div>
                <div class="desc">Processos concluídos</div>
            </div>
        </div>

        <!-- Lista de Processos Recentes -->
        <div class="card">
            <div class="card-h">
                <div>
                    <h2>
                        @if($prefeitura)
                            Processos Recentes — {{ $prefeitura->nome }}
                        @else
                            Processos Recentes do Sistema
                        @endif
                    </h2>
                    <div class="sub">Acompanhamento das últimas movimentações e despachos</div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="pill p-gray">Total: {{ $processos->count() }}</span>
                    @if(!$prefeitura)
                        <span class="text-[var(--ink-3)]"><i class="fas fa-building ml-1"></i></span>
                    @endif
                </div>
            </div>

            <div class="divide-y divide-[var(--line-2)]">
                @forelse ($processos->sortByDesc('created_at')->take(10) as $processo)
                    <div class="px-6 py-4 transition-colors hover:bg-[var(--hover-bg)]">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2.5 mb-2">
                                    <span class="font-bold text-sm text-[var(--ink)] tracking-tight">
                                        Processo #{{ $processo->numero_processo }}
                                    </span>

                                    <!-- Badge da Modalidade -->
                                    @php
                                        $modClasse = match($processo->modalidade->value ?? $processo->modalidade) {
                                            1 => 'p-warn',
                                            2 => 'p-ok',
                                            3 => 'p-vio',
                                            4 => 'p-info',
                                            default => 'p-gray'
                                        };
                                    @endphp
                                    <span class="pill {{ $modClasse }}">
                                        {{ $processo->modalidade->getDisplayName() ?? 'Modalidade' }}
                                    </span>

                                    <!-- Badge do Status -->
                                    @php
                                        $stClasse = match($processo->status->value ?? $processo->status) {
                                            'EM_ANDAMENTO' => 'p-warn',
                                            'FINALIZADO' => 'p-ok',
                                            'CANCELADO' => 'p-bad',
                                            'REPUBLICADO' => 'p-vio',
                                            'ADIADO' => 'p-warn',
                                            default => 'p-gray'
                                        };
                                    @endphp
                                    <span class="pill {{ $stClasse }}">
                                        {{ $processo->status->label() ?? $processo->status }}
                                    </span>
                                </div>
                                <div class="text-[13px] text-[var(--ink-2)] space-y-1.5">
                                    <p class="flex items-center gap-2">
                                        <i class="fas fa-building text-[var(--ink-3)] text-xs"></i>
                                        <span>{{ $processo->prefeitura->nome ?? 'Prefeitura não vinculada' }}</span>
                                    </p>
                                    <p class="flex items-start gap-2 line-clamp-2">
                                        <i class="fas fa-tag text-[var(--ink-3)] text-xs mt-1"></i>
                                        <span><b>Objeto:</b> {!! strip_tags($processo->objeto) !!}</span>
                                    </p>
                                    <div class="flex items-center gap-4 pt-1">
                                        <span class="flex items-center text-xs text-[var(--ink-3)]">
                                            <i class="far fa-clock mr-1.5"></i>
                                            Criado em {{ $processo->created_at->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="md:text-right shrink-0">
                                <a href="{{ route('admin.processos.iniciar', $processo->id) }}"
                                   class="btn sm pri">
                                    <i class="fas fa-eye text-xs"></i>
                                    <span>Ver Detalhes</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center text-[var(--ink-3)]">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[var(--card-2)] border border-[var(--line)] flex items-center justify-center text-[var(--ink-3)]">
                            <i class="fas fa-clipboard-list text-2xl"></i>
                        </div>
                        <b class="block text-base text-[var(--ink)] mb-1">Nenhum processo encontrado</b>
                        <p class="text-sm">Não há registros para os filtros selecionados.</p>
                    </div>
                @endforelse
            </div>

            <div class="px-6 py-4 border-t border-[var(--line-2)] bg-[var(--card-2)] flex items-center justify-between">
                <div class="text-xs text-[var(--ink-3)]">
                    Mostrando {{ min(10, $processos->count()) }} de {{ $processos->count() }} processos
                </div>
                <a href="{{ route('admin.processos.index') }}"
                   class="inline-flex items-center text-xs font-bold text-[var(--teal-text)] hover:text-[var(--mint)] transition-colors">
                    Ver todos os processos
                    <i class="ml-1.5 fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- Seção de Suporte (Formato do SIT .help) -->
        <section class="help">
            <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center shrink-0">
                <i class="fas fa-question text-[var(--mint)] text-xl"></i>
            </div>
            <div class="flex-1 min-w-[220px]">
                <b class="text-base font-bold text-white block">Precisa de ajuda com o Licicon?</b>
                <p class="text-sm text-white/75">Nossa equipe de suporte técnico e jurídico está pronta para auxiliar nas suas demandas.</p>
            </div>
            <a href="https://wa.me/558988060983" target="_blank" rel="noopener noreferrer"
               class="btn pri !h-11 !px-6 !text-sm">
                Falar com o suporte
            </a>
        </section>
    </div>
@endsection
