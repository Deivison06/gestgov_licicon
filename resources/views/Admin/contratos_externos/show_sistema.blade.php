@extends('layouts.app')

@section('page-title', 'Detalhes do Contrato do Sistema')
@section('page-subtitle', 'Informações completas do contrato gerado automaticamente')

@section('content')

    <div class="py-6">
        {{-- Botão Voltar --        <div class="mb-6">
            <a href="{{ route('admin.contratos.index', ['tipo' => 'sistema']) }}"
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
                        <h2 class="text-2xl font-bold text-[var(--ink)]">Contrato do Sistema</h2>
                        <p class="mt-1 text-sm text-[var(--ink-2)]">Processo: {{ $processo->numero_processo }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Botão Concluir --}}
                        @if($processo->contrato && $processo->contrato->situacao !== 'CONCLUÍDO')
                            <form action="{{ route('admin.contratos.concluir.sistema', $processo->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-[var(--vio)] bg-[var(--vio-bg)] border border-[var(--vio-border)] rounded-xl hover:bg-[var(--vio-bg)] transition-colors" onclick="return confirm('Deseja realmente concluir este contrato? Esta ação não poderá ser desfeita.')">
                                    <i class="fas fa-check-circle"></i>
                                    Concluir Contrato
                                </button>
                            </form>
                        @endif
                        {{-- Botão Download --}}
                        @if($processo->contrato)
                            <a href="{{ route('admin.processos.contrato.download', ['processo' => $processo->id]) }}"
                               target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-sm hover:scale-105">
                                <i class="fas fa-download"></i>
                                Download PDF
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Informações Gerais --}}
            <div class="p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {{-- Coluna 1: Dados do Contrato e Processo --}}
                    <div>
                        <h3 class="mb-4 text-lg font-bold text-[var(--ink)]">Informações do Contrato</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Prefeitura</label>
                                <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ $processo->prefeitura->nome ?? '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Número do Processo</label>
                                <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ $processo->numero_processo }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Número do Contrato</label>
                                <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ optional($processo->contrato)->numero_contrato ?? '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Modalidade</label>
                                <p class="mt-1 text-sm font-semibold text-[var(--ink)]">
                                    {{ $processo->modalidade ? $processo->modalidade->getDisplayName() : '-' }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Tipo de Procedimento</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[var(--card-3)] text-[var(--mint)] border border-[var(--line)] mt-1">
                                    {{ $processo->tipo_procedimento_nome ?? '-' }}
                                </span>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Valor Total Estimado do Processo</label>
                                <p class="mt-1 text-xl font-bold text-[var(--mint)]">R$ {{ number_format($processo->valor_total_vencedores ?? 0, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Coluna 2: Vigência --}}
                    <div>
                        <h3 class="mb-4 text-lg font-bold text-[var(--ink)]">Vigência</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Data de Assinatura</label>
                                <p class="mt-1 text-sm font-semibold text-[var(--ink)]">
                                    {{ optional(optional($processo->contrato)->data_assinatura_contrato)->format('d/m/Y') ?? '-' }}
                                </p>
                            </div>

                            @if($processo->contrato)
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Situação</label>
                                    @php
                                        $situacao = $processo->contrato->situacao;
                                        $cores = [
                                            'VIGENTE' => 'bg-[var(--card-3)] text-[var(--mint)] border border-[var(--line)]',
                                            'VENCIDO' => 'bg-[var(--bad-bg)] text-[var(--bad-strong)] border border-[var(--bad-border)]',
                                            'CONCLUÍDO' => 'bg-[var(--vio-bg)] text-[var(--vio)] border border-[var(--vio-border)]',
                                            'PENDENTE' => 'bg-[var(--warn-bg)] text-[var(--warn-2)] border border-[var(--warn-border)]'
                                        ];
                                        $cor = $cores[$situacao] ?? 'bg-[var(--card)] text-[var(--ink-2)] border border-[var(--line)]';
                                    @endphp
                                    <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $cor }}">
                                        {{ $situacao }}
                                    </span>
                                </div>
                            @endif

                            @php
                                $vigencia = is_array($processo->detalhe->prazo_vigencia ?? null)
                                    ? $processo->detalhe->prazo_vigencia
                                    : ['12_meses'];

                                $outro_vigencia = $processo->detalhe->prazo_vigencia_outro ?? '________________.';
                                $objeto_continuado = strtolower($processo->detalhe->objeto_continuado ?? 'nao');

                                if (in_array('exercicio_financeiro', $vigencia)) {
                                    $textoVigencia = "Até 31/12 do exercício financeiro da contratação";
                                } elseif (in_array('12_meses', $vigencia)) {
                                    $textoVigencia = "12 meses";
                                } elseif (in_array('outro', $vigencia)) {
                                    $textoVigencia = $outro_vigencia;
                                } else {
                                    $textoVigencia = "________________";
                                }
                            @endphp

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Prazo de Vigência</label>
                                <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[var(--card-3)] text-[var(--mint)] border border-[var(--line)]">
                                    {{ $textoVigencia }}
                                </span>
                            </div>

                            @if($objeto_continuado == 'sim')
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Prorrogação</label>
                                <p class="mt-1 text-sm font-semibold text-[var(--ink)]">Admite prorrogação (Objeto Continuado)</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Objeto --}}
                <div class="mt-8">
                    <h3 class="mb-2 text-lg font-bold text-[var(--ink)]">Objeto do Contrato</h3>
                    <div class="p-4 bg-[var(--bg)] border border-[var(--line)] rounded-xl">
                        <p class="text-sm text-[var(--ink)]">{!! $processo->objeto ?? 'Não informado' !!}</p>
                    </div>
                </div>

                {{-- Dados da Empresa Vencedora --}}
                <div class="mt-8">
                    <h3 class="mb-4 text-lg font-bold text-[var(--ink)]">Dados das Empresas Vencedoras</h3>

                    @if($processo->vencedores->isNotEmpty())
                        <div class="grid grid-cols-1 gap-6">
                            @foreach($processo->vencedores as $vencedor)
                                <div class="p-5 border border-[var(--line)] rounded-xl shadow-sm bg-[var(--bg)]">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Razão Social</label>
                                            <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ $vencedor->razao_social ?? '-' }}</p>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">CPF/CNPJ</label>
                                            <p class="mt-1 text-sm font-semibold text-[var(--ink)]">{{ $vencedor->cpf_cnpj_formatado ?? ($vencedor->cpf_cnpj ?? ($vencedor->cnpj ?? '-')) }}</p>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--ink-2)]">Valor Contratado</label>
                                            <p class="mt-1 text-sm font-bold text-[var(--mint)]">R$ {{ number_format($vencedor->valor_vencedor ?? $vencedor->valor_total, 2, ',', '.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 bg-[var(--warn-bg)] text-[var(--warn-2)] rounded-xl border border-[var(--warn-border)]">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Nenhuma empresa vencedora registrada para este processo.
                        </div>
                    @endif
                </div>

                @php
                    $podeAbrirAditivoSistema = auth()->user()->can('fiscalizar contratos')
                        || auth()->user()->can('contratos')
                        || auth()->user()->hasRole(['prefeitura', 'Prefeitura - Personalizado']);
                @endphp
                @if($processo->contrato)
                    {{-- Incidentes Contratuais (Aditivos) --}}
                    <div class="mt-8 pt-8 border-t border-[var(--line)]">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-[var(--ink)] flex items-center gap-2">
                                <i class="fas fa-file-contract text-[var(--mint)]"></i>
                                Incidentes Contratuais (Aditivos)
                            </h3>
                            @if($podeAbrirAditivoSistema)
                                <button type="button"
                                        onclick="document.getElementById('modalNovoAditivoSistema').classList.remove('hidden')"
                                        class="inline-flex items-center px-4 py-2 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-sm hover:scale-105">
                                    <i class="fas fa-plus mr-2"></i>
                                    Registrar Incidente Contratual
                                </button>
                            @endif
                        </div>

                        {{-- Lista de Aditivos --}}
                        @if($processo->contrato->incidentes && $processo->contrato->incidentes->isNotEmpty())
                            <div class="space-y-3 mb-6">
                                @foreach($processo->contrato->incidentes as $incidente)
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
                                                    ? route('admin.incidentes.externo', ['contrato_id' => $processo->contrato->id, 'incidente_id' => $incidente->id])
                                                    : route('admin.incidentes.documentos', ['contrato_id' => $processo->contrato->id, 'incidente_id' => $incidente->id]) }}"
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

                    {{-- Modal: Iniciar Novo Aditivo --}}
                    @if($podeAbrirAditivoSistema)
                        <div id="modalNovoAditivoSistema" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title-aditivo-sistema" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 transition-opacity bg-[var(--bg)]/80 backdrop-blur-sm" aria-hidden="true"
                                     onclick="document.getElementById('modalNovoAditivoSistema').classList.add('hidden')"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-[var(--card)] border border-[var(--line)] rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <form method="POST" action="{{ route('admin.incidentes.store', $processo->contrato->id) }}">
                                        @csrf
                                        <div class="px-5 pt-6 pb-5 bg-[var(--card)] sm:p-6 sm:pb-5">
                                            <div class="sm:flex sm:items-start">
                                                <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-[var(--card-3)] border border-[var(--line)] rounded-xl sm:mx-0 sm:h-10 sm:w-10">
                                                    <i class="text-[var(--mint)] fas fa-file-contract"></i>
                                                </div>
                                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                    <h3 class="text-lg font-bold leading-6 text-[var(--ink)]" id="modal-title-aditivo-sistema">
                                                        Iniciar Novo Aditivo
                                                    </h3>
                                                    <div class="mt-4 space-y-4">
                                                        <div>
                                                            <label for="aditivo_tipo_sistema" class="block text-sm font-semibold text-[var(--ink-2)]">Tipo de Aditivo <span class="text-[var(--mint)]">*</span></label>
                                                            <select id="aditivo_tipo_sistema" name="tipo" required class="block w-full mt-1.5 px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] sm:text-sm">
                                                                <option value="">Selecione...</option>
                                                                <option value="prazo">Prorrogação de Prazo</option>
                                                                <option value="valor">Acréscimo de Valor</option>
                                                                <option value="prazo_valor">Prazo e Valor</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label for="aditivo_categoria_sistema" class="block text-sm font-semibold text-[var(--ink-2)]">Categoria do Contrato <span class="text-[var(--mint)]">*</span></label>
                                                            <select id="aditivo_categoria_sistema" name="categoria" required class="block w-full mt-1.5 px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] sm:text-sm">
                                                                <option value="">Selecione...</option>
                                                                <option value="compras_servicos">Compras e Serviços</option>
                                                                <option value="obras">Obras</option>
                                                            </select>
                                                        </div>
                                                        @if(! auth()->user()->hasAnyRole(['diretor_licicon', 'gerente_licicon', 'colaborador_licicon']))
                                                            <input type="hidden" name="origem" value="externo">
                                                            <p class="text-xs text-[var(--ink-2)]">
                                                                <i class="fas fa-circle-info text-[var(--mint)]"></i> Este aditivo será registrado como <strong>Externo</strong>.
                                                                Para corrigir ou cancelar um aditivo depois de criado, entre em contato com a equipe Licicon.
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="px-5 py-4 bg-[var(--card-2)] border-t border-[var(--line)] sm:px-6 sm:flex sm:flex-row-reverse">
                                            <button type="submit" class="inline-flex justify-center w-full px-5 py-2.5 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-sm sm:ml-3 sm:w-auto hover:scale-105">
                                                Iniciar Aditivo
                                            </button>
                                            <button type="button" onclick="document.getElementById('modalNovoAditivoSistema').classList.add('hidden')" class="inline-flex justify-center w-full px-4 py-2.5 mt-3 text-sm font-semibold text-[var(--ink)] bg-[var(--card)] border border-[var(--line)] rounded-xl hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] transition-colors sm:mt-0 sm:ml-3 sm:w-auto">
                                                Cancelar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
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
                                        Fiscalizações ({{ $processo->contrato->fiscalizacoes->count() }})
                                    </h4>
                                    @can('fiscalizar contratos')
                                        <a href="{{ route('admin.fiscalizacoes.create', ['id' => $processo->contrato->id, 'type' => get_class($processo->contrato)]) }}"
                                           class="text-xs font-bold text-[var(--mint)] hover:underline flex items-center gap-1">
                                            <i class="fas fa-plus"></i> Nova Fiscalização
                                        </a>
                                    @endcan
                                </div>

                                @if($processo->contrato->fiscalizacoes->isNotEmpty())
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
                                                @foreach($processo->contrato->fiscalizacoes as $fisc)
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
                                        Ocorrências ({{ $processo->contrato->ocorrencias->count() }})
                                    </h4>
                                    @can('fiscalizar contratos')
                                        <a href="{{ route('admin.ocorrencias.create', ['id' => $processo->contrato->id, 'type' => get_class($processo->contrato)]) }}"
                                           class="text-xs font-bold text-[var(--mint)] hover:underline flex items-center gap-1">
                                            <i class="fas fa-plus"></i> Registrar Ocorrência
                                        </a>
                                    @endcan
                                </div>

                                @if($processo->contrato->ocorrencias->isNotEmpty())
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
                                                @foreach($processo->contrato->ocorrencias as $ocor)
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
                @endif
            </div>
        </div>
    </div>

@endsection
