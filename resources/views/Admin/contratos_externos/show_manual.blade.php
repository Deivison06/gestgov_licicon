@extends('layouts.app')

@section('page-title', 'Detalhes do Contrato Manual')
@section('page-subtitle', 'Informações completas do contrato')

@section('content')

    <div class="py-6">
        {{-- Botão Voltar --}}
        <div class="mb-6">
            <a href="{{ route('admin.contratos.index', ['tipo' => 'manual']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-[var(--ink)] bg-[var(--card)] border border-[var(--line)] rounded-xl hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] transition-colors shadow-sm">
                <i class="fas fa-arrow-left"></i>
                Voltar para Lista
            </a>
        </div>

        {{-- Cabeçalho --}}
        <div class="mb-6 card !rounded-2xl">
            <div class="card-h">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between w-full gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-[var(--ink)]">Contrato Manual</h2>
                        <p class="mt-1 text-sm text-[var(--ink-2)]">ID: {{ $contrato->id }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Botão Concluir --}}
                        @if($contrato->situacao !== 'CONCLUÍDO')
                            <form action="{{ route('admin.contratos.concluir.manual', $contrato->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-[var(--vio)] bg-[var(--vio-bg)] border border-[var(--vio-border)] rounded-xl hover:bg-[var(--vio-bg)] transition-colors" onclick="return confirm('Deseja realmente concluir este contrato? Esta ação não poderá ser desfeita.')">
                                    <i class="fas fa-check-circle"></i>
                                    Concluir Contrato
                                </button>
                            </form>
                        @endif
                        {{-- Botão Download --}}
                        @if($contrato->arquivo_contrato)
                            <a href="{{ asset($contrato->arquivo_contrato) }}"
                               target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-sm hover:scale-105">
                                <i class="fas fa-download"></i>
                                Download PDF
                            </a>
                        @endif

                        {{-- Botão Editar --}}
                        <a href="{{ route('admin.contratos.edit', $contrato->id) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-[var(--ink)] bg-[var(--hover-bg)] border border-[var(--line)] rounded-xl hover:bg-[var(--card-3)] hover:text-[var(--mint)] transition-colors">
                            <i class="fas fa-edit"></i>
                            Editar
                        </a>
                    </div>
                </div>
            </div>

            {{-- Informações Gerais --}}
            <div class="p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {{-- Coluna 1: Dados do Contrato --}}
                    <div>
                        <h3 class="mb-4 text-lg font-bold text-[var(--ink)]">Informações do Contrato</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Prefeitura</label>
                                <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ $contrato->prefeitura->nome ?? '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Número do Processo</label>
                                <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ $contrato->numero_processo }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Número do Procedimento</label>
                                <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ $contrato->numero_procedimento ?? '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Número do Contrato</label>
                                <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ $contrato->numero_contrato ?? '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Modalidade</label>
                                <p class="mt-1 text-sm font-semibold text-[var(--ink)]">
                                    @if($contrato->modalidade)
                                        {{ $contrato->modalidade->getDisplayName() }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Tipo de Contrato</label>
                                @php
                                    $tipoContratoCores = [
                                        'Serviço' => 'bg-[var(--vio-bg)] text-[var(--vio)] border border-[var(--vio-border)]',
                                        'Obra' => 'bg-[var(--orange-bg)] text-[var(--orange)] border border-[var(--bad-border)]',
                                    ];
                                    $corTipoContrato = $tipoContratoCores[$contrato->tipo_contrato] ?? 'bg-[var(--card-3)] text-[var(--mint)] border border-[var(--line)]';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $corTipoContrato }} mt-1">
                                    {{ $contrato->tipo_contrato }}
                                </span>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Valor Total</label>
                                <p class="mt-1 text-xl font-bold text-[var(--mint)]">R$ {{ number_format($contrato->valor_total, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Coluna 2: Vigência e Contratante --}}
                    <div>
                        <h3 class="mb-4 text-lg font-bold text-[var(--ink)]">Vigência e Contratante</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Status de Vigência</label>
                                @php
                                    $statusColors = [
                                        'VIGENTE' => 'bg-[var(--card-3)] text-[var(--mint)] border border-[var(--line)]',
                                        'VENCIDO' => 'bg-[var(--bad-bg)] text-[var(--bad-strong)] border border-[var(--bad-border)]',
                                        'CONCLUÍDO' => 'bg-[var(--vio-bg)] text-[var(--vio)] border border-[var(--vio-border)]',
                                        'PENDENTE' => 'bg-[var(--warn-bg)] text-[var(--warn-2)] border border-[var(--warn-border)]',
                                    ];
                                    $cor = $statusColors[$contrato->situacao] ?? 'bg-[var(--card)] text-[var(--ink-2)] border border-[var(--line)]';
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $cor }} mt-1">
                                    {{ $contrato->situacao }}
                                </span>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Data de Assinatura</label>
                                <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ $contrato->data_assinatura ? $contrato->data_assinatura->format('d/m/Y') : '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Data de Início</label>
                                <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ $contrato->data_inicio ? $contrato->data_inicio->format('d/m/Y') : '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Data de Finalização</label>
                                <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ $contrato->data_finalizacao ? $contrato->data_finalizacao->format('d/m/Y') : '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Contratante (Secretaria)</label>
                                <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ $contrato->secretaria->nome ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Objeto --}}
                <div class="mt-8">
                    <h3 class="mb-2 text-lg font-bold text-[var(--ink)]">Objeto do Contrato</h3>
                    <div class="p-4 bg-[var(--bg)] border border-[var(--line)] rounded-xl">
                        <p class="text-sm text-[var(--ink)]">{{ $contrato->objeto }}</p>
                    </div>
                </div>

                {{-- Dados da Empresa --}}
                <div class="mt-8">
                    <h3 class="mb-4 text-lg font-bold text-[var(--ink)]">Dados da Empresa Contratada</h3>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Razão Social</label>
                            <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ $contrato->empresa->razao_social ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">CNPJ</label>
                            <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ $contrato->empresa->cnpj_formatado ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Representante</label>
                            <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ $contrato->empresa->representante ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Endereço</label>
                            <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ $contrato->empresa->endereco ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Incidentes Contratuais (Aditivos) --}}
                @php
                    $podeAbrirAditivoManual = auth()->user()->can('fiscalizar contratos')
                        || auth()->user()->can('contratos')
                        || auth()->user()->hasRole(['prefeitura', 'Prefeitura - Personalizado']);
                @endphp
                <div class="mt-8 pt-8 border-t border-[var(--line)]">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-[var(--ink)] flex items-center gap-2">
                            <i class="fas fa-file-contract text-[var(--mint)]"></i>
                            Incidentes Contratuais (Aditivos)
                        </h3>
                        @if($podeAbrirAditivoManual)
                            <button type="button"
                                    onclick="document.getElementById('modalNovoAditivoManual').classList.remove('hidden')"
                                    class="inline-flex items-center px-4 py-2 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-sm hover:scale-105">
                                <i class="fas fa-plus mr-2"></i>
                                Registrar Incidente Contratual
                            </button>
                        @endif
                    </div>

                    @if($contrato->incidentes && $contrato->incidentes->isNotEmpty())
                        <div class="space-y-3 mb-6">
                            @foreach($contrato->incidentes as $incidente)
                                <div class="flex items-center justify-between p-3.5 text-sm bg-[var(--bg)] border border-[var(--line)] rounded-xl">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-[var(--ink)]">
                                            {{ ucfirst(str_replace('_', ' e ', $incidente->tipo)) }}
                                            <span class="ml-1 text-xs font-normal text-[var(--ink-2)]">
                                                ({{ $incidente->categoria === 'obras' ? 'Obras' : 'Compras e Serviços' }})
                                            </span>
                                        </span>
                                        <span class="text-xs text-[var(--ink-2)]">
                                            Registrado em {{ $incidente->created_at->format('d/m/Y') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 text-[11px] font-bold text-[var(--mint)] bg-[var(--card-3)] border border-[var(--line)] rounded-full">
                                            Aditivo {{ $incidente->ehExterno() ? 'Externo' : '' }}
                                        </span>
                                        <a href="{{ $incidente->ehExterno()
                                                ? route('admin.incidentes-manual.externo', ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id])
                                                : route('admin.incidentes-manual.documentos', ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id]) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-[var(--ink)] bg-[var(--card)] border border-[var(--line)] rounded-lg hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] transition-colors"
                                           title="{{ $incidente->ehExterno() ? 'Ver Aditivo Externo' : 'Gerenciar Documentos do Aditivo' }}">
                                            <i class="fas fa-file-alt"></i> {{ $incidente->ehExterno() ? 'Ver Aditivo' : 'Documentos' }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-[var(--bg)] rounded-xl p-6 border border-[var(--line)] mb-6 text-center text-[var(--ink-2)]">
                            Nenhum incidente contratual registrado.
                        </div>
                    @endif
                </div>

                {{-- Modal: Iniciar Novo Aditivo (Contrato Manual) --}}
                @if($podeAbrirAditivoManual)
                    <x-aditivos.modal-novo-aditivo
                        modal-id="modalNovoAditivoManual"
                        :action-route="route('admin.incidentes-manual.store', $contrato->id)"
                        :dark="true"
                        :forcar-externo="! auth()->user()->hasAnyRole(['diretor_licicon', 'gerente_licicon', 'colaborador_licicon'])" />
                @endif

                {{-- Acompanhamento da Execução Contratual --}}
                <div class="mt-8 pt-8 border-t border-[var(--line)]">
                    <h3 class="mb-6 text-lg font-bold text-[var(--ink)] flex items-center gap-2">
                        <i class="fas fa-chart-line text-[var(--mint)]"></i>
                        Acompanhamento da Execução (Fiscalizações & Ocorrências)
                    </h3>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Painel de Fiscalizações --}}
                        <div class="bg-[var(--bg)] rounded-2xl p-5 border border-[var(--line)]">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-bold text-[var(--ink)] flex items-center gap-2">
                                    <i class="fas fa-clipboard-check text-[var(--mint)]"></i>
                                    Fiscalizações ({{ $contrato->fiscalizacoes->count() }})
                                </h4>
                                @can('fiscalizar contratos')
                                    <a href="{{ route('admin.fiscalizacoes.create', ['id' => $contrato->id, 'type' => get_class($contrato)]) }}"
                                       class="text-xs font-bold text-[var(--mint)] hover:underline flex items-center gap-1">
                                        <i class="fas fa-plus"></i> Nova Fiscalização
                                    </a>
                                @endcan
                            </div>

                            @if($contrato->fiscalizacoes->isNotEmpty())
                                <div class="overflow-x-auto">
                                    <table class="w-full text-xs text-left">
                                        <thead class="text-xs font-semibold text-[var(--ink-2)] uppercase bg-[var(--card)] border-b border-[var(--line)]">
                                            <tr>
                                                <th class="px-3 py-2.5 rounded-l-lg">Nº</th>
                                                <th class="px-3 py-2.5">Data</th>
                                                <th class="px-3 py-2.5">Conclusão</th>
                                                <th class="px-3 py-2.5 text-right rounded-r-lg">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[var(--line)]/40">
                                            @foreach($contrato->fiscalizacoes as $fisc)
                                                <tr class="hover:bg-[var(--card)]/60 transition-colors">
                                                    <td class="px-3 py-2.5 font-semibold text-[var(--ink)]">
                                                        {{ $fisc->numero_fiscalizacao }}
                                                    </td>
                                                    <td class="px-3 py-2.5 text-[var(--ink-2)]">
                                                        {{ $fisc->data_fiscalizacao?->format('d/m/Y') ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2.5">
                                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $fisc->conclusao_badge_class }}">
                                                            {{ $fisc->conclusao_fiscal?->getDisplayName() ?? '—' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2.5 text-right">
                                                        <div class="flex items-center justify-end gap-1.5 font-medium">
                                                            <a href="{{ route('admin.fiscalizacoes.show', $fisc->id) }}"
                                                               class="p-1.5 text-[var(--mint)] hover:bg-[var(--card-3)] rounded-lg transition-colors" title="Visualizar">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="{{ route('admin.fiscalizacoes.edit', $fisc->id) }}"
                                                               class="p-1.5 text-[var(--warn-2)] hover:bg-[var(--warn-bg)] rounded-lg transition-colors" title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-6 text-[var(--ink-2)]">
                                    <i class="fas fa-clipboard-check text-2xl mb-2 text-[var(--ink-2)]/60"></i>
                                    <p class="text-xs">Nenhuma fiscalização registrada para este contrato.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Painel de Ocorrências --}}
                        <div class="bg-[var(--bg)] rounded-2xl p-5 border border-[var(--line)]">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-bold text-[var(--ink)] flex items-center gap-2">
                                    <i class="fas fa-triangle-exclamation text-[var(--warn-2)]"></i>
                                    Ocorrências ({{ $contrato->ocorrencias->count() }})
                                </h4>
                                @can('fiscalizar contratos')
                                    <a href="{{ route('admin.ocorrencias.create', ['id' => $contrato->id, 'type' => get_class($contrato)]) }}"
                                       class="text-xs font-bold text-[var(--mint)] hover:underline flex items-center gap-1">
                                        <i class="fas fa-plus"></i> Registrar Ocorrência
                                    </a>
                                @endcan
                            </div>

                            @if($contrato->ocorrencias->isNotEmpty())
                                <div class="overflow-x-auto">
                                    <table class="w-full text-xs text-left">
                                        <thead class="text-xs font-semibold text-[var(--ink-2)] uppercase bg-[var(--card)] border-b border-[var(--line)]">
                                            <tr>
                                                <th class="px-3 py-2.5 rounded-l-lg">Nº</th>
                                                <th class="px-3 py-2.5">Data</th>
                                                <th class="px-3 py-2.5">Status</th>
                                                <th class="px-3 py-2.5 text-right rounded-r-lg">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[var(--line)]/40">
                                            @foreach($contrato->ocorrencias as $ocor)
                                                <tr class="hover:bg-[var(--card)]/60 transition-colors">
                                                    <td class="px-3 py-2.5 font-semibold text-[var(--ink)]">
                                                        {{ $ocor->numero_ocorrencia }}
                                                    </td>
                                                    <td class="px-3 py-2.5 text-[var(--ink-2)]">
                                                        {{ $ocor->data_ocorrencia?->format('d/m/Y') ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2.5">
                                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $ocor->status_badge_class }}">
                                                            {{ $ocor->status_texto }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2.5 text-right font-medium">
                                                        <div class="flex items-center justify-end gap-1.5">
                                                            <a href="{{ route('admin.ocorrencias.show', $ocor->id) }}"
                                                               class="p-1.5 text-[var(--mint)] hover:bg-[var(--card-3)] rounded-lg transition-colors" title="Visualizar">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            @if($ocor->status->value !== 'concluida')
                                                                <a href="{{ route('admin.ocorrencias.edit', $ocor->id) }}"
                                                                   class="p-1.5 text-[var(--warn-2)] hover:bg-[var(--warn-bg)] rounded-lg transition-colors" title="Editar">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-6 text-[var(--ink-2)]">
                                    <i class="fas fa-triangle-exclamation text-2xl mb-2 text-[var(--ink-2)]/60"></i>
                                    <p class="text-xs">Nenhuma ocorrência registrada para este contrato.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
