@extends('layouts.app')

@section('page-title', 'Finalizar processo ' . $processo->numero_processo)
@section('page-subtitle', 'Cadastrar/Editar detalhes do processo')

@section('content')
    {{-- Evita "flash" do conteúdo dos cards colapsados antes do Alpine inicializar --}}
    <style>[x-cloak] { display: none !important; }</style>

    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>

    {{-- JSON com as unidades para o JS --}}
    @php
        $unidadesData = $processo->prefeitura->unidades->map(function ($unidade) {
            return [
                'id' => $unidade->id,
                'nome' => $unidade->nome,
                'servidor_responsavel' => $unidade->servidor_responsavel,
                'numero_portaria' => $unidade->numero_portaria,
                'data_portaria' => $unidade->data_portaria,
            ];
        });
    @endphp
    <script>
        const unidadesAssinantes = @json($unidadesData);
    </script>
    {{-- Fim JSON --}}

    <div class="py-8">
        @include('Admin.Processos.partials.processo-header', ['etapaAtual' => 'finalizar'])

        <!-- Área de Mensagens -->
        <div id="message-container" class="p-4"></div>

        @php
            $documentosProcessoCount = is_countable($documentosProcesso ?? null) ? count($documentosProcesso) : 0;
            $homologacoesList = $processo->homologacoes ?? collect();
            $documentosLegadosList = $documentosLegados ?? collect();
            $ehHomologacaoUnica = $ehHomologacaoUnica ?? false;
            $homologacaoUnica   = $homologacaoUnica ?? null;

            // ---- Métricas para o resumo do topo + cards ---------------------
            $totalHomologacoes      = $homologacoesList->count();
            $totalHomologadas       = $homologacoesList->where('status', \App\Models\Homologacao::STATUS_HOMOLOGADA)->count();
            $totalEmEdicao          = $homologacoesList->where('status', \App\Models\Homologacao::STATUS_EM_EDICAO)->count();
            $totalCanceladas        = $homologacoesList->where('status', \App\Models\Homologacao::STATUS_CANCELADA)->count();
            $totalLotesPendentes    = $temLotesPendentes ? $lotesPendentes->count() : 0;
            $totalDocsProcessoFeitos = $processo->documentos->whereNull('homologacao_id')->whereNotNull('caminho')->count();

            // Helper: progresso da homologação (docs + atas gerados vs esperados)
            $progressoHomologacao = function ($homol) use ($documentosPorHomologacao, $documentosHomologacao) {
                $esperados = $documentosPorHomologacao[$homol->id] ?? $documentosHomologacao ?? [];
                $totalEsperados = is_countable($esperados) ? count($esperados) : 0;
                if ($totalEsperados === 0) {
                    return ['gerados' => 0, 'total' => 0, 'percent' => 0];
                }
                $gerados = 0;
                foreach ($esperados as $tipo => $cfg) {
                    if (isset($cfg['vencedor_id'])) {
                        $ata = ($homol->atasRegistroPreco ?? collect())->firstWhere('vencedor_id', $cfg['vencedor_id']);
                        if ($ata && !empty($ata->caminho)) { $gerados++; }
                    } else {
                        $doc = $homol->documentos->firstWhere('tipo_documento', $tipo);
                        if ($doc && !empty($doc->caminho)) { $gerados++; }
                    }
                }
                return [
                    'gerados' => $gerados,
                    'total'   => $totalEsperados,
                    'percent' => (int) round(($gerados / $totalEsperados) * 100),
                ];
            };
        @endphp

        {{-- ====== RESUMO NUMÉRICO (banner no topo) ============================ --}}
        @if ($temVencedores || $totalHomologacoes > 0)
            <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Homologações</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $totalHomologacoes }}</p>
                    <p class="mt-1 text-xs text-gray-500">
                        @if ($totalHomologacoes > 0)
                            <span class="text-green-700 font-medium">{{ $totalHomologadas }} homologada(s)</span>
                            @if ($totalEmEdicao > 0) · <span class="text-amber-700 font-medium">{{ $totalEmEdicao }} em edição</span>@endif
                            @if ($totalCanceladas > 0) · <span class="text-red-700 font-medium">{{ $totalCanceladas }} cancelada(s)</span>@endif
                        @else
                            Nenhuma cadastrada ainda
                        @endif
                    </p>
                </div>

                <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Lotes pendentes</p>
                    <p class="mt-1 text-2xl font-bold {{ $totalLotesPendentes > 0 ? 'text-amber-700' : 'text-gray-400' }}">
                        {{ $totalLotesPendentes }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        @if ($totalLotesPendentes > 0)
                            Aguardando vinculação a uma homologação
                        @else
                            Todos os lotes estão vinculados
                        @endif
                    </p>
                </div>

                <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Documentos do processo</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $totalDocsProcessoFeitos }}</p>
                    <p class="mt-1 text-xs text-gray-500">PDFs gerados (atos, propostas, habilitação)</p>
                </div>

                <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Vencedores</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $processo->vencedores->count() }}</p>
                    <p class="mt-1 text-xs text-gray-500">Empresas declaradas vencedoras</p>
                </div>
            </div>
        @endif

        {{-- ====== BLOCO 1: Documentos do Processo (atos da sessão, propostas, habilitação) ====== --}}
        @if ($documentosProcessoCount > 0)
            <div class="mb-8 overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800">Documentos do Processo</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        @if ($ehHomologacaoUnica)
                            Todos os documentos do processo (sessão pública, propostas, habilitação, termo de homologação, publicações).
                        @else
                            Documentos únicos do processo (sessão pública, propostas, habilitação).
                        @endif
                    </p>
                </div>
                <div class="overflow-x-auto">
                    @include('Admin.Processos.partials.bloco-documentos', [
                        'processo' => $processo,
                        'documentos' => $documentosProcesso,
                        'documentosGerados' => $ehHomologacaoUnica
                            ? ($homologacaoUnica
                                ? $processo->documentos->filter(fn ($d) => $d->homologacao_id === null || $d->homologacao_id === $homologacaoUnica->id)
                                : $processo->documentos->whereNull('homologacao_id'))
                            : $processo->documentos->whereNull('homologacao_id'),
                        'homologacao' => $ehHomologacaoUnica ? $homologacaoUnica : null,
                        'atasRegistroPreco' => $ehHomologacaoUnica && $homologacaoUnica
                            ? ($homologacaoUnica->atasRegistroPreco ?? collect())
                            : collect(),
                    ])
                </div>
            </div>
        @endif

        {{-- ====== BLOCO 2a: Placeholder da primeira homologação (quando ainda não existe nenhuma) ====== --}}
        @if (!$ehHomologacaoUnica && $homologacoesList->isEmpty() && $temVencedores && !empty($documentosHomologacao))
            <div class="mb-8 overflow-hidden bg-white border border-emerald-200 shadow-sm rounded-2xl">
                <div class="px-6 py-4 border-b border-emerald-200 bg-gradient-to-r from-emerald-50 to-teal-50">
                    <div class="flex flex-col items-start justify-between gap-2 lg:flex-row lg:items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-emerald-900">
                                Documentos da Primeira Homologação
                                <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Aguardando criação</span>
                            </h3>
                            <p class="mt-1 text-xs text-emerald-700">
                                Ao gerar qualquer um destes PDFs (ou clicar em “Gerar Nova Homologação”), o sistema cria automaticamente a primeira homologação vinculando todos os lotes pendentes.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    @include('Admin.Processos.partials.bloco-documentos', [
                        'processo' => $processo,
                        'documentos' => $documentosHomologacao,
                        'documentosGerados' => collect(),
                        'homologacao' => null,
                    ])
                </div>
            </div>
        @endif

        {{-- ====== BLOCO 2: Cada Homologação (card colapsável) ====== --}}
        @if (!$ehHomologacaoUnica)
        @foreach ($homologacoesList as $homologacao)
            @php
                $isHomologada = $homologacao->status === \App\Models\Homologacao::STATUS_HOMOLOGADA;
                $isCancelada  = $homologacao->status === \App\Models\Homologacao::STATUS_CANCELADA;
                $isEmEdicao   = !$isHomologada && !$isCancelada;

                // Borda lateral colorida (verde / amber / red)
                $borderClass = $isHomologada ? 'border-l-green-500'
                              : ($isCancelada ? 'border-l-red-500' : 'border-l-amber-500');

                // Badge de status
                $badgeClass = $isHomologada ? 'bg-green-100 text-green-800'
                             : ($isCancelada ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800');
                $badgeLabel = $isHomologada ? 'Homologada' : ($isCancelada ? 'Cancelada' : 'Em edição');

                $progresso = $progressoHomologacao($homologacao);
                $valorTotalHomol = $homologacao->lotes->sum('vl_total');

                // Primeira homologação inicia aberta; demais ficam colapsadas pra não inundar a tela.
                $iniciaAberta = $loop->first;

                // Só oferece "Registrar Desistência" enquanto houver vencedor desta
                // homologação que ainda não tenha uma desistência registrada.
                $vencedoresComDesistencia = $homologacao->desistencias->pluck('vencedor_id');
                $temVencedorElegivelDesistencia = $homologacao->lotes->pluck('vencedor_id')
                    ->unique()
                    ->diff($vencedoresComDesistencia)
                    ->isNotEmpty();
            @endphp

            <div x-data="{ aberto: {{ $iniciaAberta ? 'true' : 'false' }}, lotesAbertos: false }"
                 class="mb-6 overflow-hidden bg-white border border-gray-200 border-l-4 {{ $borderClass }} shadow-sm rounded-2xl">

                {{-- Header sempre visível (compacto) --}}
                <div role="button" tabindex="0"
                     @click="aberto = !aberto"
                     @keydown.enter="aberto = !aberto"
                     @keydown.space.prevent="aberto = !aberto"
                     class="block w-full text-left px-5 py-4 hover:bg-gray-50 transition-colors focus:outline-none cursor-pointer">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base font-semibold text-gray-900">
                                    Homologação #{{ $homologacao->numero_sequencial }}
                                </h3>
                                <span class="px-2 py-0.5 text-xs font-medium {{ $badgeClass }} rounded-full">
                                    {{ $badgeLabel }}
                                </span>
                                <button type="button"
                                        @click.stop="deletarHomologacao('{{ $processo->id }}', '{{ $homologacao->id }}')"
                                        class="ml-2 p-1 text-red-500 transition-colors rounded hover:bg-red-50 hover:text-red-700 focus:outline-none cursor-pointer"
                                        title="Excluir Homologação">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                                @if ($temVencedorElegivelDesistencia)
                                    <button type="button"
                                            @click.stop="abrirModalDesistencia('{{ $homologacao->id }}')"
                                            class="inline-flex items-center gap-1.5 ml-2 px-2.5 py-1 text-xs font-medium text-amber-800 transition-colors bg-amber-100 border border-amber-200 rounded-full hover:bg-amber-200 focus:outline-none cursor-pointer"
                                            title="Registrar a desistência/abandono da assinatura da Ata por uma empresa vencedora">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path></svg>
                                        Desistência/Abandono de Ata
                                    </button>
                                @endif
                            </div>

                            <div class="mt-2 flex items-center flex-wrap gap-x-4 gap-y-1 text-xs text-gray-600">
                                @if ($homologacao->data_homologacao)
                                    <span>📅 {{ \Carbon\Carbon::parse($homologacao->data_homologacao)->format('d/m/Y') }}</span>
                                @endif
                                <span>📦 {{ $homologacao->lotes->count() }} lote(s)</span>
                                <span>💰 R$ {{ number_format($valorTotalHomol, 2, ',', '.') }}</span>
                                <span class="font-medium {{ $progresso['percent'] === 100 ? 'text-green-700' : 'text-gray-700' }}">
                                    ✓ {{ $progresso['gerados'] }}/{{ $progresso['total'] }} documentos
                                </span>
                            </div>

                            {{-- Barra de progresso --}}
                            @if ($progresso['total'] > 0)
                                <div class="mt-2 w-full max-w-md h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full transition-all duration-300 {{ $progresso['percent'] === 100 ? 'bg-green-500' : 'bg-amber-500' }}"
                                         style="width: {{ $progresso['percent'] }}%"></div>
                                </div>
                            @endif
                        </div>

                        {{-- Chevron expandir/recolher --}}
                        <div class="flex-shrink-0 mt-1">
                            <svg :class="aberto ? 'rotate-180' : ''"
                                 class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Conteúdo expansível --}}
                <div x-show="aberto" x-cloak x-transition.duration.200ms class="border-t border-gray-200">
                    {{-- Lotes desta homologação (colapsado por padrão) --}}
                    <div class="px-5 py-3 bg-gray-50/50 border-b border-gray-100">
                        <button type="button"
                                @click="lotesAbertos = !lotesAbertos"
                                class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                            <svg :class="lotesAbertos ? 'rotate-90' : ''"
                                 class="w-4 h-4 text-gray-400 transition-transform duration-150"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            <span>Lotes desta homologação ({{ $homologacao->lotes->count() }})</span>
                        </button>

                        <div x-show="lotesAbertos" x-cloak x-transition.duration.150ms class="mt-3">
                            @if ($homologacao->lotes->isEmpty())
                                <p class="text-sm text-gray-500">Nenhum lote vinculado.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm divide-y divide-gray-200 bg-white border border-gray-200 rounded">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Vencedor</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Lote</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Item</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Descrição</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-600 uppercase">Qtd</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-600 uppercase">Vl. Unit</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-600 uppercase">Vl. Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach ($homologacao->lotes as $lote)
                                                <tr>
                                                    <td class="px-3 py-2 text-gray-700">{{ $lote->vencedor->razao_social ?? '—' }}</td>
                                                    <td class="px-3 py-2 text-gray-700">{{ $lote->lote ?? '—' }}{{ $lote->lote_nome ? ' — ' . $lote->lote_nome : '' }}</td>
                                                    <td class="px-3 py-2 text-gray-700">{{ $lote->item }}</td>
                                                    <td class="px-3 py-2 text-gray-700">
                                                        <div class="max-w-xs truncate" title="{{ $lote->descricao }}">{{ $lote->descricao }}</div>
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-gray-700">{{ $lote->quantidade_formatada }}</td>
                                                    <td class="px-3 py-2 text-right text-gray-700">{{ $lote->valor_unitario_formatado }}</td>
                                                    <td class="px-3 py-2 text-right font-semibold text-gray-900">R$ {{ number_format($lote->vl_total, 2, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-gray-50 font-semibold">
                                                <td colspan="6" class="px-3 py-2 text-right text-gray-700">Total da Homologação</td>
                                                <td class="px-3 py-2 text-right text-gray-900">R$ {{ number_format($valorTotalHomol, 2, ',', '.') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Documentos desta homologação --}}
                    <div class="overflow-x-auto">
                        @include('Admin.Processos.partials.bloco-documentos', [
                            'processo' => $processo,
                            'documentos' => $documentosPorHomologacao[$homologacao->id] ?? $documentosHomologacao,
                            'documentosGerados' => $homologacao->documentos,
                            'homologacao' => $homologacao,
                            'atasRegistroPreco' => $homologacao->atasRegistroPreco ?? collect(),
                        ])
                    </div>

                    {{-- Desistências/abandonos de Ata registrados nesta homologação --}}
                    @include('Admin.Processos.partials.bloco-desistencias', [
                        'processo' => $processo,
                        'homologacao' => $homologacao,
                    ])
                </div>
            </div>
        @endforeach
        @endif

        {{-- ====== BLOCO 3: Lotes pendentes + botão "Gerar Nova Homologação" ====== --}}
        @if (!$ehHomologacaoUnica && $temVencedores)
            <div class="mb-8 overflow-hidden bg-white border border-amber-200 shadow-sm rounded-2xl">
                <div class="px-6 py-4 border-b border-amber-200 bg-gradient-to-r from-amber-50 to-yellow-50">
                    <div class="flex flex-col items-start justify-between gap-3 lg:flex-row lg:items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-amber-900">Lotes pendentes de homologação</h3>
                            <p class="mt-1 text-xs text-amber-700">
                                @if ($temLotesPendentes)
                                    {{ $lotesPendentes->count() }} lote(s) ainda não vinculado(s) a uma homologação.
                                @elseif (!$temHomologacao)
                                    Nenhuma homologação cadastrada. Crie a primeira para vincular os lotes.
                                @else
                                    Todos os lotes deste processo já foram homologados.
                                @endif
                            </p>
                        </div>
                        @if ($podeCriarNovaHomologacao && $temLotesPendentes)
                            <button type="button"
                                    id="btn-gerar-nova-homologacao"
                                    onclick="gerarNovaHomologacao('{{ $processo->id }}')"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-md shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                @if (!$temHomologacao)
                                    ➕ Gerar Primeira Homologação
                                @else
                                    ➕ Gerar Nova Homologação
                                @endif
                            </button>
                        @endif
                    </div>
                </div>

                @if ($temLotesPendentes)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Vencedor</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Lote</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Item</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Descrição</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-600 uppercase">Qtd</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-600 uppercase">Vl. Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($lotesPendentes as $lote)
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">{{ $lote->vencedor->razao_social ?? '—' }}</td>
                                        <td class="px-3 py-2 text-gray-700">{{ $lote->lote ?? '—' }}{{ $lote->lote_nome ? ' — ' . $lote->lote_nome : '' }}</td>
                                        <td class="px-3 py-2 text-gray-700">{{ $lote->item }}</td>
                                        <td class="px-3 py-2 text-gray-700">
                                            <div class="max-w-xs truncate" title="{{ $lote->descricao }}">{{ $lote->descricao }}</div>
                                        </td>
                                        <td class="px-3 py-2 text-right text-gray-700">{{ $lote->quantidade_formatada }}</td>
                                        <td class="px-3 py-2 text-right text-gray-700">R$ {{ number_format($lote->vl_total, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

<!-- Botão para Baixar Todos os PDFs -->
        <div class="flex flex-col items-center gap-3 p-4 mt-6 border-t border-gray-200 bg-gray-50 rounded-lg">
            <div class="flex flex-wrap items-center justify-center gap-3">
                <button type="button"
                        id="btn-baixar-todos-finalizar"
                        onclick="iniciarDownloadTodos('{{ $processo->id }}', 'finalizar')"
                        class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white transition-colors duration-200 bg-green-600 rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <svg id="icon-download-finalizar" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <svg id="spinner-finalizar" xmlns="http://www.w3.org/2000/svg" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span id="label-baixar-todos-finalizar">📥 Baixar Todos os PDFs</span>
                </button>

                @if ($podeExportarTce)
                    <button type="button"
                            onclick="abrirModalExportarTce()"
                            class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white transition-colors duration-200 bg-[#009496] rounded-lg shadow-sm hover:bg-[#007b85] focus:outline-none focus:ring-2 focus:ring-[#009496] focus:ring-offset-2"
                            title="Planilha de itens homologados de um lote para importar no Licitações Web (TCE)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h6v6m-9 4h12a2 2 0 002-2V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span>📊 Exportar Planilha para o TCE</span>
                    </button>
                @endif
            </div>

            {{-- Área de progresso --}}
            <div id="progresso-finalizar" class="hidden w-full max-w-md">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-gray-600" id="msg-progresso-finalizar">Preparando arquivo...</span>
                </div>
                <div class="w-full h-2 overflow-hidden bg-gray-200 rounded-full">
                    <div class="h-2 bg-green-500 rounded-full animate-pulse" style="width: 100%"></div>
                </div>
                <p class="mt-2 text-xs text-center text-gray-500">
                    ⚠️ Arquivos com muitas páginas podem levar alguns minutos. Não feche esta aba.
                </p>
            </div>
        </div>


        @if(
            in_array(
                $processo->modalidade,
                [
                    \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO,
                    \App\Enums\ModalidadeEnum::DISPENSA,
                ]
            )
            && $processo->tipo_procedimento !== \App\Enums\TipoProcedimentoEnum::OBRA
        )
            <!-- Seção de Vencedores -->
            <div class="mb-8">
                <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                        <div class="flex flex-col items-start justify-between lg:flex-row lg:items-center">
                            <h3 class="text-xl font-semibold text-gray-800">Vencedores do Processo</h3>
                            <button type="button"
                                    onclick="abrirModalVencedor()"
                                    class="px-4 py-2 mt-2 text-sm font-medium text-white bg-green-600 rounded-md lg:mt-0 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                ➕ Adicionar Vencedor
                            </button>
                        </div>
                    </div>

                    <!-- Tabela de Vencedores -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Razão Social
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        CNPJ
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Representante
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        CPF
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Itens/Lotes
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="vencedores-tbody">
                                @if(isset($processo->vencedores) && count($processo->vencedores) > 0)
                                    @foreach($processo->vencedores as $index => $vencedor)
                                    <tr class="vencedor-row" data-vencedor-id="{{ $vencedor->id ?? '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $vencedor->razao_social }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $vencedor->cnpj }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $vencedor->representante }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $vencedor->cpf }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                @if(isset($vencedor->lotes) && count($vencedor->lotes) > 0)
                                                    {{ count($vencedor->lotes) }} {{ $processo->tipo_contratacao === 'LOTE' ? 'lotes' : 'itens' }}
                                                @else
                                                    <span class="text-gray-400">Nenhum</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex justify-center space-x-2">
                                                <button type="button"
                                                        onclick="editarVencedor({{ $index }})"
                                                        class="px-3 py-1 text-sm text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    ✏️ Editar
                                                </button>
                                                <button type="button"
                                                        onclick="importarItensVencedor({{ $index }})"
                                                        class="px-3 py-1 text-sm text-green-600 bg-green-100 rounded-md hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500">
                                                    📊 Importar Itens
                                                </button>
                                                <button type="button"
                                                        onclick="removerVencedor({{ $index }})"
                                                        class="px-3 py-1 text-sm text-red-600 bg-red-100 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                    🗑️ Remover
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Linha expansível com os itens/lotes do vencedor -->
                                    @if(isset($vencedor->lotes) && count($vencedor->lotes) > 0)
                                    <tr class="bg-gray-50">
                                        <td colspan="6" class="px-6 py-4">
                                            <div class="lotes-container">
                                                <div class="flex items-center justify-between mb-3">
                                                    <h4 class="text-lg font-semibold text-gray-800">
                                                        {{ $processo->tipo_contratacao === 'LOTE' ? 'Lotes' : 'Itens' }} do Vencedor
                                                    </h4>
                                                    <button type="button"
                                                            onclick="toggleLotes({{ $index }})"
                                                            class="flex items-center text-sm text-blue-600 hover:text-blue-800">
                                                        <span id="toggle-text-{{ $index }}">Mostrar Detalhes</span>
                                                        <svg id="toggle-icon-{{ $index }}" class="w-4 h-4 ml-1 transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    </button>
                                                </div>

                                                <div id="lotes-details-{{ $index }}" class="hidden">
                                                    @if($processo->tipo_contratacao === \App\Enums\TipoContratacaoEnum::LOTE)
                                                        <!-- Estrutura para LOTE - Agrupar por número do lote -->
                                                        @php
                                                            $lotesAgrupados = $vencedor->lotes->groupBy('lote');
                                                        @endphp

                                                        @foreach($lotesAgrupados as $numeroLote => $itensLote)
                                                        <div class="mb-6 border border-gray-200 rounded-lg">
                                                            <div class="px-4 py-3 bg-gray-100 border-b border-gray-200">
                                                                <h5 class="font-semibold text-gray-800">
                                                                    LOTE {{ $numeroLote }} {{ !empty($itensLote->first()->lote_nome) ? ' - ' . $itensLote->first()->lote_nome : '' }}
                                                                </h5>
                                                            </div>
                                                            <div class="overflow-x-auto">
                                                                <table class="min-w-full divide-y divide-gray-200">
                                                                    <thead class="bg-gray-50">
                                                                        <tr>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                Status
                                                                            </th>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                Item
                                                                            </th>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                Descrição
                                                                            </th>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                UNIDADE
                                                                            </th>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                Marca
                                                                            </th>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                Modelo
                                                                            </th>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                Quantidade
                                                                            </th>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                Vl. Unit
                                                                            </th>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                Vl. Total
                                                                            </th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                                        @foreach($itensLote as $lote)
                                                                        <tr class="hover:bg-gray-50">
                                                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                                                    @if($lote->status === 'HOMOLOGADO') bg-green-100 text-green-800
                                                                                    @elseif($lote->status === 'ADJUDICADO') bg-blue-100 text-blue-800
                                                                                    @else bg-gray-100 text-gray-800 @endif">
                                                                                    {{ $lote->status }}
                                                                                </span>
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                                                {{ $lote->item }}
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                                                <div class="max-w-xs truncate" title="{{ $lote->descricao }}">
                                                                                    {{ $lote->descricao }}
                                                                                </div>
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                                                {{ $lote->unidade }}
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                                                {{ $lote->marca }}
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                                                {{ $lote->modelo }}
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                                {{ $lote->quantidade_formatada }}
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                                R$ {{ $lote->valor_unitario_formatado }}
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm font-semibold text-right text-gray-900">
                                                                                R$ {{ number_format($lote->vl_total, 2, ',', '.') }}
                                                                            </td>
                                                                        </tr>
                                                                        @endforeach
                                                                        <!-- Linha de totais do lote -->
                                                                        <tr class="font-semibold bg-gray-100">
                                                                            <td class="px-4 py-2 text-sm text-gray-900" colspan="6">
                                                                                TOTAL DO LOTE {{ $numeroLote }} {{ !empty($itensLote->first()->lote_nome) ? ' - ' . $itensLote->first()->lote_nome : '' }}
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                                {{ number_format($itensLote->sum('quantidade'), 0, ',', '.') }}
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                                -
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-right text-green-700">
                                                                                R$ {{ number_format($itensLote->sum('vl_total'), 2, ',', '.') }}
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        @endforeach

                                                        <!-- Total geral do vencedor -->
                                                        <div class="p-4 mt-4 border border-blue-200 rounded-lg bg-blue-50">
                                                            <div class="flex items-center justify-between">
                                                                <span class="text-lg font-bold text-blue-800">TOTAL GERAL DO VENCEDOR</span>
                                                                <span class="text-lg font-bold text-blue-800">
                                                                    R$ {{ number_format($vencedor->lotes->sum('vl_total'), 2, ',', '.') }}
                                                                </span>
                                                            </div>
                                                        </div>

                                                    @else
                                                        <!-- Estrutura para ITEM - Listar todos os itens -->
                                                        <div class="overflow-x-auto">
                                                            <table class="min-w-full divide-y divide-gray-200">
                                                                <thead class="bg-gray-100">
                                                                    <tr>
                                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                            Status
                                                                        </th>
                                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                            Item
                                                                        </th>
                                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                            Descrição
                                                                        </th>
                                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                            UNIDADE
                                                                        </th>
                                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                            Marca
                                                                        </th>
                                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                            Modelo
                                                                        </th>
                                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                            Quantidade
                                                                        </th>
                                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                            Vl. Unit
                                                                        </th>
                                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                            Vl. Total
                                                                        </th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="bg-white divide-y divide-gray-200">
                                                                    @foreach($vencedor->lotes as $lote)
                                                                    <tr class="hover:bg-gray-50">
                                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                                                @if($lote->status === 'HOMOLOGADO') bg-green-100 text-green-800
                                                                                @elseif($lote->status === 'ADJUDICADO') bg-blue-100 text-blue-800
                                                                                @else bg-gray-100 text-gray-800 @endif">
                                                                                {{ $lote->status }}
                                                                            </span>
                                                                        </td>
                                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                                            {{ $lote->item }}
                                                                        </td>
                                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                                            <div class="max-w-xs truncate" title="{{ $lote->descricao }}">
                                                                                {{ $lote->descricao }}
                                                                            </div>
                                                                        </td>
                                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                                            {{ $lote->unidade }}
                                                                        </td>
                                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                                            {{ $lote->marca }}
                                                                        </td>
                                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                                            {{ $lote->modelo }}
                                                                        </td>
                                                                        <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                            {{ $lote->quantidade_formatada }}
                                                                        </td>
                                                                        <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                            R$ {{ $lote->valor_unitario_formatado }}
                                                                        </td>
                                                                        <td class="px-4 py-2 text-sm font-semibold text-right text-gray-900">
                                                                            R$ {{ number_format($lote->vl_total, 2, ',', '.') }}
                                                                        </td>
                                                                    </tr>
                                                                    @endforeach
                                                                    <!-- Linha de totais -->
                                                                    <tr class="font-semibold bg-gray-100">
                                                                        <td class="px-4 py-2 text-sm text-gray-900" colspan="6">
                                                                            TOTAL GERAL
                                                                        </td>
                                                                        <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                            {{ number_format($vencedor->lotes->sum('quantidade'), 0, ',', '.') }}
                                                                        </td>
                                                                        <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                            -
                                                                        </td>
                                                                        <td class="px-4 py-2 text-sm text-right text-green-700">
                                                                            R$ {{ number_format($vencedor->lotes->sum('vl_total'), 2, ',', '.') }}
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                @else
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-sm text-center text-gray-500">
                                        Nenhum vencedor cadastrado
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Seção de Reservas -->
            <div class="mb-8">
                <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-yellow-100">
                        <div class="flex flex-col items-start justify-between lg:flex-row lg:items-center">
                            <h3 class="text-xl font-semibold text-gray-800">Empresas Reservas do Processo</h3>
                            <button type="button"
                                    onclick="abrirModalReserva()"
                                    class="px-4 py-2 mt-2 text-sm font-medium text-white bg-yellow-600 rounded-md lg:mt-0 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                ➕ Adicionar Reserva
                            </button>
                        </div>
                    </div>

                    <!-- Tabela de Reservas -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Razão Social
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        CNPJ
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Endereço
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Telefone
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        E-mail
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Representante Legal
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="reservas-tbody">
                                @if(isset($processo->reservas) && count($processo->reservas) > 0)
                                    @foreach($processo->reservas as $index => $reserva)
                                    <tr class="reserva-row" data-reserva-id="{{ $reserva->id ?? '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $reserva->razao_social }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $reserva->cnpj }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="max-w-xs text-sm text-gray-900 truncate" title="{{ $reserva->endereco }}">
                                                {{ $reserva->endereco ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $reserva->telefone ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $reserva->email ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $reserva->representante_legal ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex justify-center space-x-2">
                                                <button type="button"
                                                        onclick="editarReserva({{ $index }})"
                                                        class="px-3 py-1 text-sm text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    ✏️ Editar
                                                </button>
                                                <button type="button"
                                                        onclick="removerReserva({{ $index }})"
                                                        class="px-3 py-1 text-sm text-red-600 bg-red-100 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                    🗑️ Remover
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500">
                                        Nenhuma empresa reserva cadastrada
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal para Adicionar/Editar Vencedor -->
    <div id="vencedorModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-4 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="fecharModal()"></div>

            <div class="inline-block w-full max-w-2xl overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:my-8">
                <div class="px-6 py-4 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modalTitle">
                        Adicionar Vencedor
                    </h3>
                </div>

                <form id="vencedorForm" onsubmit="salvarVencedor(event)">
                    <div class="px-6 py-4">
                        <input type="hidden" id="vencedorIndex" value="">
                        <input type="hidden" id="vencedorId" value="">

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label for="razao_social" class="block text-sm font-medium text-gray-700">Razão Social *</label>
                                <input type="text"
                                    id="razao_social"
                                    name="razao_social"
                                    required
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Razão Social">
                            </div>
                            <div>
                                <label for="cnpj" class="block text-sm font-medium text-gray-700">CNPJ *</label>
                                <input type="text"
                                    id="cnpj"
                                    name="cnpj"
                                    required
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 cnpj-mask"
                                    placeholder="00.000.000/0000-00">
                            </div>
                            <div>
                                <label for="representante" class="block text-sm font-medium text-gray-700">Representante *</label>
                                <input type="text"
                                    id="representante"
                                    name="representante"
                                    required
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Nome do Representante">
                            </div>
                            <div>
                                <label for="cpf" class="block text-sm font-medium text-gray-700">CPF *</label>
                                <input type="text"
                                    id="cpf"
                                    name="cpf"
                                    required
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 cpf-mask"
                                    placeholder="000.000.000-00">
                            </div>
                            <div>
                                <label for="endereco" class="block text-sm font-medium text-gray-700">Endereco *</label>
                                <input type="text"
                                    id="endereco"
                                    name="endereco"
                                    required
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Endereço completo">
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Salvar
                        </button>
                        <button type="button"
                                onclick="fecharModal()"
                                class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Registrar Desistência/Abandono de Ata -->
    <div id="desistenciaModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-4 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="fecharModalDesistencia()"></div>

            <div class="inline-block w-full max-w-lg overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:my-8">
                <div class="px-6 py-4 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                        Registrar Desistência/Abandono de Ata
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        A empresa selecionada perde o direito à contratação; o saldo dos lotes dela
                        nesta homologação será zerado e um Termo de Registro e Decisão
                        Administrativa será gerado automaticamente.
                    </p>
                </div>

                <div class="px-6 py-4 space-y-4">
                    <input type="hidden" id="desistenciaHomologacaoId">

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Empresa vencedora</label>
                        <select id="desistenciaVencedorId"
                                class="block w-full mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500">
                            <option value="">Selecione a empresa</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Data da solicitação de assinatura (convocação)</label>
                        <input type="date" id="desistenciaDataSolicitacao"
                               class="block w-full mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500">
                        <p class="mt-1 text-xs text-gray-500">Data em que a Ata foi encaminhada à empresa para assinatura.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Data do Termo de Decisão Administrativa</label>
                        <input type="date" id="desistenciaDataDecisao"
                               class="block w-full mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500">
                        <p class="mt-1 text-xs text-gray-500">Data em que a decisão foi tomada (ex.: após o prazo de resposta vencido) — é a data que sai no rodapé do Termo, independente de quando o PDF for gerado no sistema.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Observação (opcional)</label>
                        <textarea id="desistenciaObservacao" rows="2"
                                  class="block w-full mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Comprovações (convocação, ausência de resposta etc.)
                        </label>
                        <input type="file" id="desistenciaAnexos" multiple accept=".pdf,.jpg,.jpeg,.png"
                               class="block w-full mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500">
                        <p class="mt-1 text-xs text-gray-500">É possível anexar mais de um arquivo (PDF, JPG ou PNG).</p>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 sm:flex sm:flex-row-reverse">
                    <button type="button"
                            id="btn-confirmar-desistencia"
                            onclick="enviarDesistencia()"
                            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-amber-600 border border-transparent rounded-md shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Registrar Desistência
                    </button>
                    <button type="button"
                            onclick="fecharModalDesistencia()"
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Desistência/Abandono de Ata (corrige datas/observação e regera o Termo) -->
    <div id="editarDesistenciaModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-4 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="fecharEditarDesistencia()"></div>

            <div class="inline-block w-full max-w-lg overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:my-8">
                <div class="px-6 py-4 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                        Editar Desistência — <span id="editarDesistenciaVencedorNome"></span>
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Corrige as datas/observação deste registro e regera automaticamente o Termo
                        de Registro e Decisão Administrativa (o PDF antigo é substituído). Empresa e
                        anexos de comprovação não podem ser alterados aqui.
                    </p>
                </div>

                <div class="px-6 py-4 space-y-4">
                    <input type="hidden" id="editarDesistenciaHomologacaoId">
                    <input type="hidden" id="editarDesistenciaId">

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Data da solicitação de assinatura (convocação)</label>
                        <input type="date" id="editarDesistenciaDataSolicitacao"
                               class="block w-full mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Data do Termo de Decisão Administrativa</label>
                        <input type="date" id="editarDesistenciaDataDecisao"
                               class="block w-full mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500">
                        <p class="mt-1 text-xs text-gray-500">É a data que sai no rodapé do Termo regerado.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Observação (opcional)</label>
                        <textarea id="editarDesistenciaObservacao" rows="2"
                                  class="block w-full mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500"></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 sm:flex sm:flex-row-reverse">
                    <button type="button"
                            id="btn-salvar-edicao-desistencia"
                            onclick="salvarEdicaoDesistencia()"
                            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-amber-600 border border-transparent rounded-md shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Salvar e Regerar Termo
                    </button>
                    <button type="button"
                            onclick="fecharEditarDesistencia()"
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Empresas vencedoras por homologação (com base nos lotes vinculados), usado
        // para popular o <select> do modal de Desistência/Abandono de Ata. Empresas que
        // já têm desistência registrada nesta homologação não entram na lista.
        window.homologacaoVencedoresMap = @json(
            $processo->homologacoes->mapWithKeys(fn ($h) => [
                $h->id => $h->lotes->pluck('vencedor')->filter()->unique('id')
                    ->reject(fn ($v) => $h->desistencias->pluck('vencedor_id')->contains($v->id))
                    ->map(fn ($v) => ['id' => $v->id, 'razao_social' => $v->razao_social])
                    ->values(),
            ])
        );

        // Lotes já homologados neste processo, usado para popular o <select> do
        // modal "Exportar Planilha para o TCE" (a exportação é sempre lote a lote).
        window.lotesDisponiveisTce = @json($lotesDisponiveisTce ?? []);
    </script>

    {{-- Modal para Exportar Planilha para o TCE --}}
    <div id="modalExportarTce" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-4 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="fecharModalExportarTce()"></div>

            <div class="inline-block w-full max-w-lg overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:my-8">
                <div class="px-6 py-4 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                        Exportar Planilha para o TCE
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        O Tribunal de Contas importa a planilha lote a lote. Selecione o lote desejado
                        para gerar o arquivo já preenchido (número, descrição, quantidade, unidade,
                        valor previsto, valor homologado e CNPJ do vencedor).
                    </p>
                </div>

                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label for="tceLoteSelecionado" class="block text-sm font-medium text-gray-700">Lote</label>
                        <select id="tceLoteSelecionado"
                                class="block w-full mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                            <option value="">Selecione o lote</option>
                        </select>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 sm:flex sm:flex-row-reverse">
                    <button type="button"
                            onclick="exportarPlanilhaTce()"
                            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-[#009496] border border-transparent rounded-md shadow-sm hover:bg-[#007b85] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#009496] sm:ml-3 sm:w-auto sm:text-sm">
                        Exportar
                    </button>
                    <button type="button"
                            onclick="fecharModalExportarTce()"
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function abrirModalExportarTce() {
            const select = document.getElementById('tceLoteSelecionado');
            select.innerHTML = '<option value="">Selecione o lote</option>';

            (window.lotesDisponiveisTce || []).forEach(lote => {
                const option = document.createElement('option');
                option.value = lote.valor;
                option.textContent = lote.label;
                select.appendChild(option);
            });

            document.getElementById('modalExportarTce').classList.remove('hidden');
        }

        function fecharModalExportarTce() {
            document.getElementById('modalExportarTce').classList.add('hidden');
        }

        function exportarPlanilhaTce() {
            const select = document.getElementById('tceLoteSelecionado');

            if (!select.value) {
                showMessage('Selecione um lote para exportar.', 'error');
                return;
            }

            window.location.href = '{{ route("admin.processos.finalizacao.exportar-tce", $processo) }}?lote=' + encodeURIComponent(select.value);
            fecharModalExportarTce();
        }
    </script>

    <!-- Modal para Importação de Itens por Vencedor -->
    <div id="importarItensModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-4 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="fecharImportarModal()"></div>

            <div class="inline-block w-full max-w-2xl overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:my-8">
                <div class="px-6 py-4 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="importarModalTitle">
                        Importar {{ $processo->tipo_contratacao === 'LOTE' ? 'Lotes' : 'Itens' }} para Vencedor
                    </h3>
                </div>

                <div class="px-6 py-4">
                    <input type="hidden" id="importarVencedorIndex">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Selecione o arquivo Excel:
                        </label>
                        <input type="file"
                            id="excelFileVencedor"
                            accept=".xlsx,.xls,.csv"
                            class="block w-full mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">
                            Formatos suportados: .xlsx, .xls, .csv
                        </p>
                    </div>

                    <!-- No modal de importação, adicione este script após o input do arquivo -->
                    <script>
                        document.getElementById('excelFileVencedor').addEventListener('change', function(e) {
                            const file = e.target.files[0];
                            const fileSize = file ? file.size : 0;
                            const maxSize = 10 * 1024 * 1024; // 10MB

                            if (fileSize > maxSize) {
                                showMessage('Arquivo muito grande. Tamanho máximo: 10MB', 'error');
                                e.target.value = '';
                                return;
                            }

                            // Verificar extensão
                            const allowedExtensions = ['xlsx', 'xls', 'csv'];
                            const extension = file.name.split('.').pop().toLowerCase();
                            if (!allowedExtensions.includes(extension)) {
                                showMessage('Extensão não permitida. Use .xlsx, .xls ou .csv', 'error');
                                e.target.value = '';
                                return;
                            }
                        });
                    </script>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="sobrescreverVencedor" class="text-blue-600 border-gray-300 rounded shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Sobrescrever dados existentes</span>
                        </label>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 sm:flex sm:flex-row-reverse">
                    <button type="button"
                            onclick="processarExcelVencedor()"
                            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Importar
                    </button>
                    <button type="button"
                            onclick="fecharImportarModal()"
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Adicionar/Editar Reserva -->
    <div id="reservaModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-4 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="fecharModalReserva()"></div>

            <div class="inline-block w-full max-w-2xl overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:my-8">
                <div class="px-6 py-4 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="reservaModalTitle">
                        Adicionar Reserva
                    </h3>
                </div>

                <form id="reservaForm" onsubmit="salvarReserva(event)">
                    <div class="px-6 py-4">
                        <input type="hidden" id="reservaIndex" value="">
                        <input type="hidden" id="reservaId" value="">

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label for="reserva_razao_social" class="block text-sm font-medium text-gray-700">Razão Social *</label>
                                <input type="text"
                                    id="reserva_razao_social"
                                    name="razao_social"
                                    required
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                    placeholder="Razão Social">
                            </div>
                            <div>
                                <label for="reserva_cnpj" class="block text-sm font-medium text-gray-700">CNPJ *</label>
                                <input type="text"
                                    id="reserva_cnpj"
                                    name="cnpj"
                                    required
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 cnpj-mask"
                                    placeholder="00.000.000/0000-00">
                            </div>
                            <div>
                                <label for="reserva_telefone" class="block text-sm font-medium text-gray-700">Telefone</label>
                                <input type="text"
                                    id="reserva_telefone"
                                    name="telefone"
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 telefone-mask"
                                    placeholder="(00) 00000-0000">
                            </div>
                            <div class="md:col-span-2">
                                <label for="reserva_endereco" class="block text-sm font-medium text-gray-700">Endereço</label>
                                <input type="text"
                                    id="reserva_endereco"
                                    name="endereco"
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                    placeholder="Endereço completo">
                            </div>
                            <div>
                                <label for="reserva_email" class="block text-sm font-medium text-gray-700">E-mail</label>
                                <input type="email"
                                    id="reserva_email"
                                    name="email"
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                    placeholder="email@exemplo.com">
                            </div>
                            <div>
                                <label for="reserva_representante_legal" class="block text-sm font-medium text-gray-700">Representante Legal</label>
                                <input type="text"
                                    id="reserva_representante_legal"
                                    name="representante_legal"
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                    placeholder="Nome do Representante Legal">
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-yellow-600 border border-transparent rounded-md shadow-sm hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Salvar
                        </button>
                        <button type="button"
                                onclick="fecharModalReserva()"
                                class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Dados das reservas
        let reservas = @json($processo->reservas ?? []);

        // Funções do Modal de Reserva
        function abrirModalReserva() {
            document.getElementById('reservaModalTitle').textContent = 'Adicionar Reserva';
            document.getElementById('reservaIndex').value = '';
            document.getElementById('reservaId').value = '';
            document.getElementById('reservaForm').reset();

            const modal = document.getElementById('reservaModal');
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        }

        function editarReserva(index) {
            const reserva = reservas[index];

            document.getElementById('reservaModalTitle').textContent = 'Editar Reserva';
            document.getElementById('reservaIndex').value = index;
            document.getElementById('reservaId').value = reserva.id || '';
            document.getElementById('reserva_razao_social').value = reserva.razao_social;
            document.getElementById('reserva_cnpj').value = reserva.cnpj;
            document.getElementById('reserva_endereco').value = reserva.endereco || '';
            document.getElementById('reserva_telefone').value = reserva.telefone || '';
            document.getElementById('reserva_email').value = reserva.email || '';
            document.getElementById('reserva_representante_legal').value = reserva.representante_legal || '';

            const modal = document.getElementById('reservaModal');
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        }

        function fecharModalReserva() {
            const modal = document.getElementById('reservaModal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }

        // Função para salvar reserva
        function salvarReserva(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const reservaIndex = document.getElementById('reservaIndex').value;
            const reservaId = document.getElementById('reservaId').value;

            const reservaData = {
                id: reservaId,
                razao_social: formData.get('razao_social'),
                cnpj: formData.get('cnpj'),
                endereco: formData.get('endereco'),
                telefone: formData.get('telefone'),
                email: formData.get('email'),
                representante_legal: formData.get('representante_legal')
            };

            // Criar uma nova lista de reservas que inclui todas as existentes + a nova/editada
            let reservasAtualizadas = [];

            if (reservaIndex !== '') {
                // Se está editando, substitui a reserva na posição correta
                reservasAtualizadas = [...reservas];
                reservasAtualizadas[reservaIndex] = reservaData;
            } else {
                // Se está adicionando nova, adiciona ao final
                reservasAtualizadas = [...reservas, reservaData];
            }

            // Enviar para o servidor TODAS as reservas
            fetch('{{ route("admin.processos.finalizacao.reservas.store", $processo) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    reservas: reservasAtualizadas,
                    reserva_index: reservaIndex
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Reserva salva com sucesso!', 'success');
                    fecharModalReserva();
                    atualizarTabelaReservas();
                } else {
                    showMessage('Erro ao salvar reserva: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Erro ao salvar reserva: ' + error, 'error');
            });
        }

        // Função para remover reserva
        function removerReserva(index) {
            if (!confirm('Tem certeza que deseja remover esta reserva?')) {
                return;
            }

            const reservaId = reservas[index]?.id;

            // Criar nova lista sem a reserva removida
            const reservasAtualizadas = reservas.filter((_, i) => i !== index);

            fetch('{{ route("admin.processos.finalizacao.reservas.store", $processo) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    reservas: reservasAtualizadas,
                    remover_reserva: reservaId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Reserva removida com sucesso!', 'success');
                    atualizarTabelaReservas();
                } else {
                    showMessage('Erro ao remover reserva: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Erro ao remover reserva: ' + error, 'error');
            });
        }

        // Atualizar tabela de reservas
        function atualizarTabelaReservas() {
            fetch('{{ route("admin.processos.finalizacao.reservas.get", $processo) }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        reservas = data.reservas;
                        const tbody = document.getElementById('reservas-tbody');

                        if (reservas.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500">
                                        Nenhuma empresa reserva cadastrada
                                    </td>
                                </tr>
                            `;
                            return;
                        }

                        tbody.innerHTML = reservas.map((reserva, index) => {
                            return `
                                <tr class="reserva-row" data-reserva-id="${reserva.id || ''}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">${reserva.razao_social}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${reserva.cnpj}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="max-w-xs text-sm text-gray-900 truncate" title="${reserva.endereco || ''}">
                                            ${reserva.endereco || '-'}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${reserva.telefone || '-'}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${reserva.email || '-'}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${reserva.representante_legal || '-'}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex justify-center space-x-2">
                                            <button type="button"
                                                    onclick="editarReserva(${index})"
                                                    class="px-3 py-1 text-sm text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                ✏️ Editar
                                            </button>
                                            <button type="button"
                                                    onclick="removerReserva(${index})"
                                                    class="px-3 py-1 text-sm text-red-600 bg-red-100 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                🗑️ Remover
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        }).join('');
                    }
                })
                .catch(error => {
                    console.error('Erro ao atualizar reservas:', error);
                });
        }

        // Adicionar máscaras
        function aplicarMascarasAdicionais() {
            // Máscara de telefone
            document.querySelectorAll('.telefone-mask').forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 11) {
                        value = value.substring(0, 11);
                    }

                    if (value.length > 10) {
                        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                    } else if (value.length > 6) {
                        value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                    } else if (value.length > 2) {
                        value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
                    } else if (value.length > 0) {
                        value = value.replace(/(\d{0,2})/, '($1');
                    }
                    e.target.value = value;
                });
            });
        }

        // Inicializar as máscaras adicionais
        document.addEventListener('DOMContentLoaded', function() {
            aplicarMascarasAdicionais();
        });
    </script>

    <script>
        // Dados dos vencedores
        let vencedores = @json($processo->vencedores ?? []);
        let editandoIndex = null;

        // Funções do Modal de Vencedor
        function abrirModalVencedor() {
            document.getElementById('modalTitle').textContent = 'Adicionar Vencedor';
            document.getElementById('vencedorIndex').value = '';
            document.getElementById('vencedorId').value = '';
            document.getElementById('vencedorForm').reset();

            const modal = document.getElementById('vencedorModal');
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        }

        function editarVencedor(index) {
            const vencedor = vencedores[index];

            document.getElementById('modalTitle').textContent = 'Editar Vencedor';
            document.getElementById('vencedorIndex').value = index;
            document.getElementById('vencedorId').value = vencedor.id || '';
            document.getElementById('razao_social').value = vencedor.razao_social;
            document.getElementById('cnpj').value = vencedor.cnpj;
            document.getElementById('representante').value = vencedor.representante;
            document.getElementById('cpf').value = vencedor.cpf;
            document.getElementById('endereco').value = vencedor.endereco;

            const modal = document.getElementById('vencedorModal');
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        }

        function fecharModal() {
            const modal = document.getElementById('vencedorModal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }

        // Funções do Modal de Importação
        function importarItensVencedor(vencedorIndex) {
            document.getElementById('importarVencedorIndex').value = vencedorIndex;
            document.getElementById('importarModalTitle').textContent =
                'Importar {{ $processo->tipo_contratacao === 'LOTE' ? 'Lotes' : 'Itens' }} para Vencedor ' + (parseInt(vencedorIndex) + 1);

            const modal = document.getElementById('importarItensModal');
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        }

        function fecharImportarModal() {
            const modal = document.getElementById('importarItensModal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }

        // Função para salvar vencedor
        function salvarVencedor(event) {
            event.preventDefault();

            console.log('=== DEBUG INICIO salvarVencedor ===');

            const formData = new FormData(event.target);

            const vencedorIndex = document.getElementById('vencedorIndex').value;
            const vencedorId = document.getElementById('vencedorId').value;

            // Criar objeto apenas com os dados do vencedor atual
            const vencedorData = {
                id: vencedorId || null, // Envia null se for novo
                razao_social: formData.get('razao_social'),
                cnpj: formData.get('cnpj'),
                representante: formData.get('representante'),
                cpf: formData.get('cpf'),
                endereco: formData.get('endereco'),
                lotes: []
            };

            console.log('VencedorData a ser enviado:', vencedorData);

            // Se está editando, preserva os lotes existentes
            if (vencedorIndex !== '' && vencedores[vencedorIndex]) {
                vencedorData.lotes = vencedores[vencedorIndex].lotes || [];
                console.log('Preservando lotes existentes:', vencedorData.lotes);
            }

            // Preparar dados para enviar
            let requestData = {};

            if (vencedorIndex !== '') {
                // Se está editando, envia apenas o vencedor específico
                requestData = {
                    vencedor_id: vencedorId, // ID do vencedor sendo editado
                    vencedor_data: vencedorData,
                    vencedor_index: vencedorIndex,
                    operacao: 'editar'
                };
                console.log('Editando vencedor:', requestData);
            } else {
                // Se está adicionando novo
                requestData = {
                    vencedor_data: vencedorData,
                    operacao: 'adicionar'
                };
                console.log('Adicionando novo vencedor:', requestData);
            }

            console.log('=== DEBUG FIM ===');

            // Enviar para o servidor APENAS o vencedor atual
            fetch('{{ route("admin.processos.finalizacao.vencedores.store", $processo) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            })
            .then(response => {
                console.log('Resposta bruta:', response);
                return response.json();
            })
            .then(data => {
                console.log('Resposta JSON:', data);
                if (data.success) {
                    showMessage('Vencedor salvo com sucesso!', 'success');
                    fecharModal();
                    atualizarTabelaVencedores(); // Isso vai recarregar todos os vencedores do servidor
                } else {
                    showMessage('Erro ao salvar vencedor: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                showMessage('Erro ao salvar vencedor: ' + error, 'error');
            });
        }
        // Função para remover vencedor
        function removerVencedor(index) {
            if (!confirm('Tem certeza que deseja remover este vencedor?')) {
                return;
            }

            const vencedorId = vencedores[index]?.id;

            // Criar nova lista sem o vencedor removido
            const vencedoresAtualizados = vencedores.filter((_, i) => i !== index);

            fetch('{{ route("admin.processos.finalizacao.vencedores.store", $processo) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    vencedores: vencedoresAtualizados,
                    remover_vencedor: vencedorId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Vencedor removido com sucesso!', 'success');
                    atualizarTabelaVencedores();
                } else {
                    showMessage('Erro ao remover vencedor: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Erro ao remover vencedor: ' + error, 'error');
            });
        }

        // Função para processar Excel do vencedor
        function processarExcelVencedor() {
            const vencedorIndex = document.getElementById('importarVencedorIndex').value;
            const fileInput = document.getElementById('excelFileVencedor');
            const file = fileInput.files[0];

            if (!file) {
                showMessage('Por favor, selecione um arquivo Excel.', 'error');
                return;
            }

            const allowedTypes = ['.xlsx', '.xls', '.csv'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
            if (!allowedTypes.includes(fileExtension)) {
                showMessage('Tipo de arquivo não permitido. Use .xlsx, .xls ou .csv.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('excel_file', file);
            formData.append('processo_id', {{ $processo->id }});
            formData.append('tipo_contratacao', '{{ $processo->tipo_contratacao }}');
            formData.append('vencedor_index', vencedorIndex);
            formData.append('_token', '{{ csrf_token() }}');

            const importButton = document.querySelector('#importarItensModal button[onclick="processarExcelVencedor()"]');
            const originalText = importButton.textContent;
            importButton.textContent = 'Importando...';
            importButton.disabled = true;

            showMessage('Processando arquivo Excel...', 'info');

            // Adicionar timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 60000); // 60 segundos

            fetch("{{ route('admin.processos.finalizacao.importar-excel', $processo) }}", {
                method: 'POST',
                body: formData,
                signal: controller.signal
            })
                .then(response => {
                    clearTimeout(timeoutId);

                    if (!response.ok) {
                        if (response.status === 413) {
                            throw new Error('Arquivo muito grande. Tamanho máximo: 10MB');
                        }
                        return response.text().then(text => {
                            try {
                                const data = JSON.parse(text);
                                throw new Error(`HTTP ${response.status}: ${data.message || text}`);
                            } catch {
                                throw new Error(`HTTP ${response.status}: ${text}`);
                            }
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        fecharImportarModal();
                        atualizarTabelaVencedores();

                        // Limpar o input de arquivo
                        fileInput.value = '';
                    } else {
                        showMessage('Erro: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    clearTimeout(timeoutId);

                    let errorMessage = 'Erro ao processar arquivo: ';
                    if (error.name === 'AbortError') {
                        errorMessage += 'Tempo esgotado (60 segundos). O arquivo pode ser muito grande ou complexo.';
                    } else {
                        errorMessage += error.message;
                    }

                    console.error('Erro completo:', error);
                    showMessage(errorMessage, 'error');
                })
                .finally(() => {
                    importButton.textContent = originalText;
                    importButton.disabled = false;
                });
        }
        // Atualizar tabela de vencedores
        function atualizarTabelaVencedores() {
            fetch('{{ route("admin.processos.finalizacao.vencedores.get", $processo) }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        vencedores = data.vencedores;
                        const tbody = document.getElementById('vencedores-tbody');

                        if (vencedores.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-sm text-center text-gray-500">
                                        Nenhum vencedor cadastrado
                                    </td>
                                </tr>
                            `;
                            return;
                        }

                        tbody.innerHTML = vencedores.map((vencedor, index) => {
                            const hasLotes = vencedor.lotes && vencedor.lotes.length > 0;
                            const lotesCount = hasLotes ? vencedor.lotes.length : 0;
                            const tipoItem = '{{ $processo->tipo_contratacao === 'LOTE' ? 'lotes' : 'itens' }}';

                            let lotesHtml = '';
                            if (hasLotes) {
                                // Agrupar por lote se for tipo LOTE
                                if ('{{ $processo->tipo_contratacao === 'LOTE' }}') {
                                    const lotesAgrupados = vencedor.lotes.reduce((acc, lote) => {
                                        const loteNumero = lote.lote || 'Sem Lote';
                                        if (!acc[loteNumero]) {
                                            acc[loteNumero] = [];
                                        }
                                        acc[loteNumero].push(lote);
                                        return acc;
                                    }, {});

                                    const lotesAgrupadosHtml = Object.entries(lotesAgrupados).map(([numeroLote, itensLote]) => {
                                        const totalLote = itensLote.reduce((sum, item) => sum + parseFloat(item.vl_total), 0);
                                        const quantidadeLote = itensLote.reduce((sum, item) => sum + parseFloat(item.quantidade), 0);

                                        return `
                                            <div class="mb-6 border border-gray-200 rounded-lg">
                                                <div class="px-4 py-3 bg-gray-100 border-b border-gray-200">
                                                    <h5 class="font-semibold text-gray-800">
                                                        LOTE ${numeroLote} ${itensLote[0].lote_nome ? ' - ' + itensLote[0].lote_nome : ''}
                                                    </h5>
                                                </div>
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-gray-200">
                                                        <thead class="bg-gray-50">
                                                            <tr>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    Status
                                                                </th>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    Item
                                                                </th>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    Descrição
                                                                </th>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    UNIDADE
                                                                </th>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    Marca
                                                                </th>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    Modelo
                                                                </th>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    Quantidade
                                                                </th>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    Vl. Unit
                                                                </th>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    Vl. Total
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                            ${itensLote.map(lote => `
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                                        ${lote.status === 'HOMOLOGADO' ? 'bg-green-100 text-green-800' :
                                                                        lote.status === 'ADJUDICADO' ? 'bg-blue-100 text-blue-800' :
                                                                        'bg-gray-100 text-gray-800'}">
                                                                        ${lote.status}
                                                                    </span>
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                    ${lote.item}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                    <div class="max-w-xs truncate" title="${lote.descricao}">
                                                                        ${lote.descricao}
                                                                    </div>
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                    ${lote.unidade}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                    ${lote.marca || '-'}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                    ${lote.modelo || '-'}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                    ${parseFloat(lote.quantidade).toLocaleString('pt-BR')}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                    ${parseFloat(lote.vl_unit).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL', minimumFractionDigits: 2, maximumFractionDigits: 4})}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm font-semibold text-right text-gray-900">
                                                                    R$ ${parseFloat(lote.vl_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                                                                </td>
                                                            </tr>
                                                            `).join('')}
                                                            <!-- Linha de totais do lote -->
                                                            <tr class="font-semibold bg-gray-100">
                                                                <td class="px-4 py-2 text-sm text-gray-900" colspan="6">
                                                                    TOTAL DO LOTE ${numeroLote}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                    ${quantidadeLote.toLocaleString('pt-BR')}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                    -
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-right text-green-700">
                                                                    R$ ${totalLote.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        `;
                                    }).join('');

                                    const totalGeral = vencedor.lotes.reduce((sum, lote) => sum + parseFloat(lote.vl_total), 0);

                                    lotesHtml = `
                                        ${lotesAgrupadosHtml}
                                        <div class="p-4 mt-4 border border-blue-200 rounded-lg bg-blue-50">
                                            <div class="flex items-center justify-between">
                                                <span class="text-lg font-bold text-blue-800">TOTAL GERAL DO VENCEDOR</span>
                                                <span class="text-lg font-bold text-blue-800">
                                                    R$ ${totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                                                </span>
                                            </div>
                                        </div>
                                    `;
                                } else {
                                    // Estrutura para ITEM
                                    const totalGeral = vencedor.lotes.reduce((sum, lote) => sum + parseFloat(lote.vl_total), 0);
                                    const quantidadeGeral = vencedor.lotes.reduce((sum, lote) => sum + parseFloat(lote.quantidade), 0);

                                    lotesHtml = `
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-100">
                                                    <tr>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            Status
                                                        </th>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            Item
                                                        </th>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            Descrição
                                                        </th>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            UNIDADE
                                                        </th>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            Marca
                                                        </th>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            Modelo
                                                        </th>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            Quantidade
                                                        </th>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            Vl. Unit
                                                        </th>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            Vl. Total
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    ${vencedor.lotes.map(lote => `
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                                ${lote.status === 'HOMOLOGADO' ? 'bg-green-100 text-green-800' :
                                                                lote.status === 'ADJUDICADO' ? 'bg-blue-100 text-blue-800' :
                                                                'bg-gray-100 text-gray-800'}">
                                                                ${lote.status}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                            ${lote.item}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                            <div class="max-w-xs truncate" title="${lote.descricao}">
                                                                ${lote.descricao}
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                            ${lote.unidade}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                            ${lote.marca || '-'}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                            ${lote.modelo || '-'}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                            ${parseFloat(lote.quantidade).toLocaleString('pt-BR')}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                            ${parseFloat(lote.vl_unit).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL', minimumFractionDigits: 2, maximumFractionDigits: 4})}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm font-semibold text-right text-gray-900">
                                                            R$ ${parseFloat(lote.vl_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                                                        </td>
                                                    </tr>
                                                    `).join('')}
                                                    <!-- Linha de totais -->
                                                    <tr class="font-semibold bg-gray-100">
                                                        <td class="px-4 py-2 text-sm text-gray-900" colspan="6">
                                                            TOTAL GERAL
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                            ${quantidadeGeral.toLocaleString('pt-BR')}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                            -
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-right text-green-700">
                                                            R$ ${totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    `;
                                }
                            }

                            return `
                                <tr class="vencedor-row" data-vencedor-id="${vencedor.id || ''}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">${vencedor.razao_social}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${vencedor.cnpj}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${vencedor.representante}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${vencedor.cpf}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            ${hasLotes ?
                                                `${lotesCount} ${tipoItem}` :
                                                '<span class="text-gray-400">Nenhum</span>'}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex justify-center space-x-2">
                                            <button type="button"
                                                    onclick="editarVencedor(${index})"
                                                    class="px-3 py-1 text-sm text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                ✏️ Editar
                                            </button>
                                            <button type="button"
                                                    onclick="importarItensVencedor(${index})"
                                                    class="px-3 py-1 text-sm text-green-600 bg-green-100 rounded-md hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500">
                                                📊 Importar Itens
                                            </button>
                                            <button type="button"
                                                    onclick="removerVencedor(${index})"
                                                    class="px-3 py-1 text-sm text-red-600 bg-red-100 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                🗑️ Remover
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                ${hasLotes ? `
                                <tr class="bg-gray-50">
                                    <td colspan="6" class="px-6 py-4">
                                        <div class="lotes-container">
                                            <div class="flex items-center justify-between mb-3">
                                                <h4 class="text-lg font-semibold text-gray-800">
                                                    ${tipoItem === 'lotes' ? 'Lotes' : 'Itens'} do Vencedor
                                                </h4>
                                                <button type="button"
                                                        onclick="toggleLotes(${index})"
                                                        class="flex items-center text-sm text-blue-600 hover:text-blue-800">
                                                    <span id="toggle-text-${index}">Mostrar Detalhes</span>
                                                    <svg id="toggle-icon-${index}" class="w-4 h-4 ml-1 transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <div id="lotes-details-${index}" class="hidden">
                                                ${lotesHtml}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                ` : ''}
                            `;
                        }).join('');
                    }
                })
                .catch(error => {
                    console.error('Erro ao atualizar vencedores:', error);
                });
        }

        // Função para mostrar/ocultar lotes
        function toggleLotes(index) {
            const details = document.getElementById(`lotes-details-${index}`);
            const toggleText = document.getElementById(`toggle-text-${index}`);
            const toggleIcon = document.getElementById(`toggle-icon-${index}`);

            if (details.classList.contains('hidden')) {
                details.classList.remove('hidden');
                toggleText.textContent = 'Ocultar Detalhes';
                toggleIcon.classList.add('rotate-180');
            } else {
                details.classList.add('hidden');
                toggleText.textContent = 'Mostrar Detalhes';
                toggleIcon.classList.remove('rotate-180');
            }
        }

        // Funções auxiliares
        function aplicarMascaras() {
            // Máscara de CNPJ
            document.querySelectorAll('.cnpj-mask').forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length <= 14) {
                        value = value.replace(/(\d{2})(\d)/, '$1.$2')
                                    .replace(/(\d{3})(\d)/, '$1.$2')
                                    .replace(/(\d{3})(\d)/, '$1/$2')
                                    .replace(/(\d{4})(\d)/, '$1-$2');
                        e.target.value = value;
                    }
                });
            });

            // Máscara de CPF
            document.querySelectorAll('.cpf-mask').forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length <= 11) {
                        value = value.replace(/(\d{3})(\d)/, '$1.$2')
                                    .replace(/(\d{3})(\d)/, '$1.$2')
                                    .replace(/(\d{3})(\d)/, '$1-$2');
                        e.target.value = value;
                    }
                });
            });
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            aplicarMascaras();
        });
    </script>

    <script>
        // Funções existentes para documentos (mantidas da view original)
        document.addEventListener('DOMContentLoaded', function() {
            // Inicialização da funcionalidade de acordeão
            const collapseButtons = document.querySelectorAll('[data-collapse-toggle]');
            if (collapseButtons.length > 0) {
                collapseButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const targetId = button.getAttribute('data-collapse-toggle');
                        const targetEl = document.getElementById(targetId);
                        const isExpanded = button.getAttribute('aria-expanded') === 'true';
                        const span = button.querySelector('.collapse-text');

                        if (isExpanded) {
                            targetEl.classList.add('hidden');
                            button.setAttribute('aria-expanded', 'false');
                            span.textContent = 'Definir Campos e Assinantes';
                        } else {
                            targetEl.classList.remove('hidden');
                            button.setAttribute('aria-expanded', 'true');
                            span.textContent = 'Ocultar Campos e Assinantes';
                        }
                    });
                });
            }

            // Inicialização do TinyMCE
            document.querySelectorAll('textarea[x-ref$="_editor"]').forEach(textarea => {
                tinymce.init({
                    selector: '#' + textarea.id,
                    plugins: 'advlist lists link table code charmap emoticons',
                    toolbar: 'undo redo | bold italic underline | bullist numlist | styleselect | link table | emoticons charmap | code',
                    menubar: false,
                    branding: false,
                    height: 300,
                    advlist_bullet_styles: 'default,circle,square',
                    advlist_number_styles: 'default,lower-alpha,upper-alpha,lower-roman,upper-roman',
                    setup: function (editor) {
                        editor.on('change keyup', function () {
                            textarea.value = editor.getContent();
                            textarea.dispatchEvent(new Event('input', { bubbles: true }));
                        });
                    }
                });
            });
        });

        // Funções para gerenciar assinantes
        function adicionarAssinante(tipoDocumento) {
            const container = document.getElementById(`assinantes-container-${tipoDocumento}`);
            const novoAssinante = document.createElement('div');
            novoAssinante.className = 'assinante-item flex flex-col gap-3 p-4 mb-3 bg-white border border-gray-200 rounded-lg';
            novoAssinante.innerHTML = `
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    {{-- Select da Unidade --}}
                    <div class="flex-1 min-w-[180px]">
                        <label class="block mb-1 text-xs font-medium text-gray-600">
                            Unidade
                        </label>
                        <select name="assinante_unidade[]"
                                class="block w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 unidade-select"
                                onchange="updateResponsavel(this, '${tipoDocumento}')">
                            <option value="">Selecione a Unidade</option>
                            @foreach ($processo->prefeitura->unidades as $unidade)
                                <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Campos do Responsável e Portaria --}}
                    <div class="flex flex-col flex-1 gap-2 sm:flex-row sm:items-center sm:gap-3">
                        {{-- Nome do Responsável --}}
                        <div class="flex-1 min-w-[200px]">
                            <label class="block mb-1 text-xs font-medium text-gray-600">
                                Responsável
                            </label>
                            <input type="text" name="assinante_responsavel[]"
                                placeholder="Nome do Responsável" readonly
                                class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm responsavel-input">
                        </div>

                        {{-- Número da Portaria --}}
                        <div class="flex-1 min-w-[150px]">
                            <label class="block mb-1 text-xs font-medium text-gray-600">
                                Nº Portaria
                            </label>
                            <input type="text" name="assinante_portaria[]"
                                placeholder="Número da Portaria" readonly
                                class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm portaria-input">
                        </div>

                        {{-- Data da Portaria --}}
                        <div class="flex-1 min-w-[150px]">
                            <label class="block mb-1 text-xs font-medium text-gray-600">
                                Data Portaria
                            </label>
                            <input type="text" name="assinante_data_portaria[]"
                                placeholder="Data da Portaria" readonly
                                class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm data-portaria-input">
                        </div>
                    </div>

                    {{-- Botão Remover --}}
                    <div class="flex items-end sm:pt-6">
                        <button type="button" onclick="removerAssinante(this, '${tipoDocumento}')"
                                class="p-2 text-white bg-red-500 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                            🗑 Remover
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(novoAssinante);
        }

        function removerAssinante(botao, tipoDocumento) {
            const container = document.getElementById(`assinantes-container-${tipoDocumento}`);
            const assinanteDiv = botao.closest('.assinante-item');
            const todosAssinantes = container.querySelectorAll('.assinante-item');

            if (todosAssinantes.length > 1) {
                assinanteDiv.style.transition = 'opacity 0.3s ease';
                assinanteDiv.style.opacity = '0';
                setTimeout(() => assinanteDiv.remove(), 300);
            } else {
                showMessage('É obrigatório ter pelo menos um assinante.', 'error');
            }
        }

        function updateResponsavel(select, tipoDocumento) {
            const selectedUnidadeId = select.value;
            const selectedUnidade = unidadesAssinantes.find(u => u.id == selectedUnidadeId);
            const assinanteDiv = select.closest('.assinante-item') || select.closest('.flex.items-center');

            if (selectedUnidade) {
                // Preenche o campo responsável
                const responsavelInput = assinanteDiv.querySelector('.responsavel-input');
                if (responsavelInput) {
                    responsavelInput.value = selectedUnidade.servidor_responsavel || '';
                }

                // Preenche o número da portaria (se existir o campo)
                const portariaInput = assinanteDiv.querySelector('.portaria-input');
                if (portariaInput) {
                    portariaInput.value = selectedUnidade.numero_portaria || '';
                }

                // Preenche a data da portaria (se existir o campo)
                const dataPortariaInput = assinanteDiv.querySelector('.data-portaria-input');
                if (dataPortariaInput) {
                    dataPortariaInput.value = selectedUnidade.data_portaria || '';
                }
            } else {
                // Limpa os campos se nenhuma unidade for selecionada
                const responsavelInput = assinanteDiv.querySelector('.responsavel-input');
                if (responsavelInput) responsavelInput.value = '';

                const portariaInput = assinanteDiv.querySelector('.portaria-input');
                if (portariaInput) portariaInput.value = '';

                const dataPortariaInput = assinanteDiv.querySelector('.data-portaria-input');
                if (dataPortariaInput) dataPortariaInput.value = '';
            }
        }

        // Função auxiliar para obter os dados dos assinantes
        function getAssinantes(tipoDocumento) {
            // First try to get from the new inline component state
            if (window.assinaturaConfig && window.assinaturaConfig[tipoDocumento] && window.assinaturaConfig[tipoDocumento].assinantes) {
                if (window.assinaturaConfig[tipoDocumento].assinantes.length > 0) {
                    return window.assinaturaConfig[tipoDocumento].assinantes;
                }
            }

            const container = document.getElementById(`assinantes-container-${tipoDocumento}`);
            if (!container) return [];
            const selects = container.querySelectorAll('select[name="assinante_unidade[]"]');
            const assinantes = [];

            selects.forEach((select, index) => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption.value) {
                    const unidade = unidadesAssinantes.find(u => u.id == select.value);
                    if (unidade) {
                        // Busca os valores dos inputs correspondentes
                        const assinanteDiv = select.closest('.assinante-item');
                        const responsavelInput = assinanteDiv.querySelector('input[name="assinante_responsavel[]"]');
                        const portariaInput = assinanteDiv.querySelector('input[name="assinante_portaria[]"]');
                        const dataPortariaInput = assinanteDiv.querySelector('input[name="assinante_data_portaria[]"]');

                        assinantes.push({
                            unidade_id: unidade.id,
                            unidade_nome: unidade.nome,
                            responsavel: responsavelInput?.value || unidade.servidor_responsavel,
                            numero_portaria: portariaInput?.value || unidade.numero_portaria,
                            data_portaria: dataPortariaInput?.value || unidade.data_portaria,
                        });
                    }
                }
            });
            return assinantes;
        }

        // Função para gerar PDF sem assinatura (para documentos que não requerem assinatura)
        // Salva um campo da Ata por vencedor (numero_ata_registro_precos, cargo_controle_interno
        // ou data_doc_ata_registro_precos). Bypass do Alpine state para não conflitar com
        // múltiplas linhas (uma por vencedor) renderizadas na mesma homologação.
        async function saveCampoAta(campo, valor, homologacaoId, vencedorId) {
            try {
                const formData = new FormData();
                formData.append('processo_id', {{ $processo->id }});
                formData.append('homologacao_id', homologacaoId);
                formData.append('vencedor_id', vencedorId);
                formData.append(campo, valor ?? '');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const response = await fetch("{{ route('admin.processos.finalizacao.store', $processo) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                if (!response.ok) {
                    console.warn('Falha ao salvar campo da Ata', campo, response.status);
                }
            } catch (err) {
                console.error('Erro ao salvar campo da Ata', err);
            }
        }

        function gerarPdfSemAssinatura(processoId, documento, event, homologacaoId, vencedorId = null) {
            const button = event.currentTarget;
            const originalText = button.textContent;

            button.textContent = 'Gerando...';
            button.disabled = true;

            let url = `/admin/finalizacao/processos/${processoId}/pdf?documento=${documento}`;
            if (homologacaoId) {
                url += `&homologacao_id=${homologacaoId}`;
            }
            if (vencedorId) {
                url += `&vencedor_id=${vencedorId}`;
            }

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    showMessage('Erro ao gerar PDF: ' + error, 'error');
                })
                .finally(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                });
        }

        // Modificar a função gerarPdf existente para incluir validação de assinantes
        function gerarPdf(processoId, documento, data, event, idSuffix, homologacaoId, vencedorId = null) {
            if (!data) {
                showMessage('Por favor, selecione uma data antes de gerar o PDF.', 'error');
                return;
            }

            const sufixo = idSuffix || '';
            const containerKey = documento + sufixo;
            const parecer = document.getElementById('parecer_select_' + containerKey)?.value
                || document.getElementById('parecer_select_' + documento)?.value
                || '';
            const assinantes = getAssinantes(containerKey);

            // Verificar se há pelo menos um assinante
            if (assinantes.length < 1) {
                showMessage('Você deve adicionar pelo menos um assinante antes de gerar o PDF.', 'error');
                return;
            }

            const assinantesJson = JSON.stringify(assinantes);
            const assinantesEncoded = encodeURIComponent(assinantesJson);

            let url = `/admin/finalizacao/processos/${processoId}/pdf?documento=${documento}&data=${data}`;

            if (parecer) {
                url += `&parecer=${parecer}`;
            }
            if (assinantes.length > 0) {
                url += `&assinantes=${assinantesEncoded}`;
            }
            if (homologacaoId) {
                url += `&homologacao_id=${homologacaoId}`;
            }
            if (vencedorId) {
                url += `&vencedor_id=${vencedorId}`;
            }

            const button = event.currentTarget;
            const originalText = button.textContent;

            button.textContent = 'Gerando...';
            button.disabled = true;

            // PERSISTE a seleção em documento_selecao_assinantes ANTES de gerar.
            // Sem isso "Solicitar Assinatura" não acharia os assinantes salvos.
            const persistirSelecao = (assinantes.length > 0 && typeof window.salvarSelecaoAntesDeGerar === 'function')
                ? window.salvarSelecaoAntesDeGerar(processoId, documento, homologacaoId || null, vencedorId || null, { assinantes })
                : Promise.resolve(null);

            persistirSelecao
                .catch(() => null)
                .then(() => fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let msg = data.message;
                        if (data.assinatura) {
                            msg += ` Rodada de assinatura digital iniciada com ${data.assinatura.total_solicitacoes} solicitação(ões).`;
                        }
                        showMessage(msg, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    showMessage('Erro ao gerar PDF: ' + error, 'error');
                })
                .finally(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                });
        }

        // Gerar nova homologação para os lotes pendentes
        function gerarNovaHomologacao(processoId) {
            if (!confirm('Confirma a criação de uma nova homologação com todos os lotes pendentes?')) {
                return;
            }

            const button = document.getElementById('btn-gerar-nova-homologacao');
            if (button) {
                button.disabled = true;
                button.textContent = 'Criando...';
            }

            fetch(`/admin/processos/${processoId}/finalizacao/homologacoes`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
                .then(r => r.json().then(data => ({ ok: r.ok, data })))
                .then(({ ok, data }) => {
                    if (ok && data.success) {
                        showMessage(data.message || 'Homologação criada.', 'success');
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        showMessage(data.message || 'Erro ao criar homologação.', 'error');
                        if (button) {
                            button.disabled = false;
                            button.textContent = '➕ Gerar Nova Homologação';
                        }
                    }
                })
                .catch(err => {
                    showMessage('Erro de rede ao criar homologação: ' + err.message, 'error');
                    if (button) {
                        button.disabled = false;
                        button.textContent = '➕ Gerar Nova Homologação';
                    }
                });
        }

        // Deletar homologação existente
        function deletarHomologacao(processoId, homologacaoId) {
            if (!confirm('Atenção: Ao excluir esta homologação, todos os documentos e atas vinculados a ela serão apagados. Os lotes voltarão a ficar "pendentes" no processo.\n\nTem certeza que deseja excluir esta homologação?')) {
                return;
            }

            fetch(`/admin/processos/${processoId}/finalizacao/homologacoes/${homologacaoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
                .then(r => r.json().then(data => ({ ok: r.ok, data })))
                .then(({ ok, data }) => {
                    if (ok && data.success) {
                        showMessage(data.message || 'Homologação excluída com sucesso.', 'success');
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        showMessage(data.message || 'Erro ao excluir homologação.', 'error');
                    }
                })
                .catch(err => {
                    showMessage('Erro de rede ao excluir homologação: ' + err.message, 'error');
                });
        }

        // Abre o modal de Desistência/Abandono de Ata, populando o <select> de
        // empresas vencedoras com base nos lotes vinculados a esta homologação.
        function abrirModalDesistencia(homologacaoId) {
            document.getElementById('desistenciaHomologacaoId').value = homologacaoId;
            document.getElementById('desistenciaDataSolicitacao').value = '';
            document.getElementById('desistenciaDataDecisao').value = '';
            document.getElementById('desistenciaObservacao').value = '';
            document.getElementById('desistenciaAnexos').value = '';

            const select = document.getElementById('desistenciaVencedorId');
            select.innerHTML = '<option value="">Selecione a empresa</option>';
            const vencedores = (window.homologacaoVencedoresMap || {})[homologacaoId] || [];
            vencedores.forEach(v => {
                const option = document.createElement('option');
                option.value = v.id;
                option.textContent = v.razao_social;
                select.appendChild(option);
            });

            document.getElementById('desistenciaModal').classList.remove('hidden');
        }

        function fecharModalDesistencia() {
            document.getElementById('desistenciaModal').classList.add('hidden');
        }

        function enviarDesistencia() {
            const homologacaoId = document.getElementById('desistenciaHomologacaoId').value;
            const vencedorId = document.getElementById('desistenciaVencedorId').value;
            const dataSolicitacao = document.getElementById('desistenciaDataSolicitacao').value;
            const dataDecisao = document.getElementById('desistenciaDataDecisao').value;
            const observacao = document.getElementById('desistenciaObservacao').value;
            const arquivos = document.getElementById('desistenciaAnexos').files;

            if (!vencedorId) {
                showMessage('Selecione a empresa que desistiu/abandonou a assinatura.', 'error');
                return;
            }
            if (!dataSolicitacao) {
                showMessage('Informe a data da solicitação de assinatura.', 'error');
                return;
            }
            if (!dataDecisao) {
                showMessage('Informe a data do Termo de Decisão Administrativa.', 'error');
                return;
            }
            if (arquivos.length === 0) {
                showMessage('Anexe ao menos um comprovante de convocação.', 'error');
                return;
            }

            if (!confirm('Confirma o registro da desistência? O saldo dos lotes desta empresa nesta homologação será zerado e um Termo de Registro e Decisão Administrativa será gerado.')) {
                return;
            }

            const formData = new FormData();
            formData.append('vencedor_id', vencedorId);
            formData.append('data_solicitacao_assinatura', dataSolicitacao);
            formData.append('data_decisao', dataDecisao);
            formData.append('observacao', observacao);
            for (const arquivo of arquivos) {
                formData.append('anexos[]', arquivo);
            }
            formData.append('_token', '{{ csrf_token() }}');

            const btn = document.getElementById('btn-confirmar-desistencia');
            const textoOriginal = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Registrando...';

            fetch(`/admin/processos/{{ $processo->id }}/finalizacao/homologacoes/${homologacaoId}/desistencias`, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            })
                .then(r => r.json().then(data => ({ ok: r.ok, data })))
                .then(({ ok, data }) => {
                    if (ok && data.success) {
                        showMessage(data.message || 'Desistência registrada com sucesso.', 'success');
                        fecharModalDesistencia();
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        showMessage(data.message || 'Erro ao registrar desistência.', 'error');
                        btn.disabled = false;
                        btn.textContent = textoOriginal;
                    }
                })
                .catch(err => {
                    showMessage('Erro de rede ao registrar desistência: ' + err.message, 'error');
                    btn.disabled = false;
                    btn.textContent = textoOriginal;
                });
        }

        // Abre o modal de edição pré-preenchido com os dados do botão "Editar"
        // (data-* attributes), lidos via `this.dataset` — evita quebrar o onclick
        // com aspas/caracteres especiais que possam vir da observação.
        function abrirEditarDesistencia(botao) {
            document.getElementById('editarDesistenciaHomologacaoId').value = botao.dataset.homologacaoId;
            document.getElementById('editarDesistenciaId').value = botao.dataset.desistenciaId;
            document.getElementById('editarDesistenciaVencedorNome').textContent = botao.dataset.vencedor || '';
            document.getElementById('editarDesistenciaDataSolicitacao').value = botao.dataset.dataSolicitacao || '';
            document.getElementById('editarDesistenciaDataDecisao').value = botao.dataset.dataDecisao || '';
            document.getElementById('editarDesistenciaObservacao').value = botao.dataset.observacao || '';

            document.getElementById('editarDesistenciaModal').classList.remove('hidden');
        }

        function fecharEditarDesistencia() {
            document.getElementById('editarDesistenciaModal').classList.add('hidden');
        }

        function salvarEdicaoDesistencia() {
            const homologacaoId = document.getElementById('editarDesistenciaHomologacaoId').value;
            const desistenciaId = document.getElementById('editarDesistenciaId').value;
            const dataSolicitacao = document.getElementById('editarDesistenciaDataSolicitacao').value;
            const dataDecisao = document.getElementById('editarDesistenciaDataDecisao').value;
            const observacao = document.getElementById('editarDesistenciaObservacao').value;

            if (!dataSolicitacao || !dataDecisao) {
                showMessage('Informe as duas datas antes de salvar.', 'error');
                return;
            }

            const btn = document.getElementById('btn-salvar-edicao-desistencia');
            const textoOriginal = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Salvando...';

            fetch(`/admin/processos/{{ $processo->id }}/finalizacao/homologacoes/${homologacaoId}/desistencias/${desistenciaId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    data_solicitacao_assinatura: dataSolicitacao,
                    data_decisao: dataDecisao,
                    observacao: observacao
                })
            })
                .then(r => r.json().then(data => ({ ok: r.ok, data })))
                .then(({ ok, data }) => {
                    if (ok && data.success) {
                        showMessage(data.message || 'Desistência atualizada com sucesso.', 'success');
                        fecharEditarDesistencia();
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        showMessage(data.message || 'Erro ao atualizar desistência.', 'error');
                        btn.disabled = false;
                        btn.textContent = textoOriginal;
                    }
                })
                .catch(err => {
                    showMessage('Erro de rede ao atualizar desistência: ' + err.message, 'error');
                    btn.disabled = false;
                    btn.textContent = textoOriginal;
                });
        }

        function showMessage(message, type) {
            const container = document.getElementById('message-container');
            const bgColor = type === 'success' ? 'bg-green-100 border-green-400' :
                type === 'error' ? 'bg-red-100 border-red-400' :
                    type === 'info' ? 'bg-blue-100 border-blue-400' :
                        'bg-gray-100 border-gray-400';
            const textColor = type === 'success' ? 'text-green-800' :
                type === 'error' ? 'text-red-800' :
                    type === 'info' ? 'text-blue-800' :
                        'text-gray-800';
            const icon = type === 'success' ? '✅' :
                type === 'error' ? '❌' :
                    type === 'info' ? 'ℹ️' :
                        '⚠️';

            container.innerHTML = `
        <div class="p-4 mb-4 border-l-4 rounded-md ${bgColor} ${textColor}">
            <div class="flex items-center">
                <span class="mr-2 text-lg">${icon}</span>
                <div>
                    <span class="font-semibold">${message}</span>
                    ${type === 'error' ? '<div class="mt-1 text-sm opacity-75">Verifique o formato do arquivo e tente novamente.</div>' : ''}
                </div>
            </div>
        </div>
    `;

            // Auto-remover após 8 segundos para erros, 5 para outros
            const timeout = type === 'error' ? 8000 : 5000;
            setTimeout(() => {
                container.innerHTML = '';
            }, timeout);
        }

        // Alpine.js Component
        function formField(existing = {}) {
            const initialData = {};
            const docDatesConfirmed = {};
            Object.keys(existing || {}).forEach(key => {
                if (key.startsWith('data_doc_')) {
                    initialData[key] = existing[key];
                    docDatesConfirmed[key] = !!existing[key];
                }
            });

            return {
                ...initialData,
                homologacao_id: existing?.homologacao_id ?? null,
                // Campos do formulário
                anexo_atos_sessao: existing?.anexo_atos_sessao ?? '',
                anexo_proposta: existing?.anexo_proposta ?? '',
                anexo_proposta_readequada: existing?.anexo_proposta_readequada ?? '',
                anexo_habilitacao: existing?.anexo_habilitacao ?? '',
                anexo_recurso_contratacoes: existing?.anexo_recurso_contratacoes ?? '',
                anexo_publicacoes: existing?.anexo_publicacoes ?? '',
                orgao_responsavel: existing?.orgao_responsavel ?? '',
                cnpj: existing?.cnpj ?? '',
                endereco: existing?.endereco ?? '',
                responsavel: existing?.responsavel ?? '',
                cpf_responsavel: existing?.cpf_responsavel ?? '',
                razao_social: existing?.razao_social ?? '',
                cnpj_empresa_vencedora: existing?.cnpj_empresa_vencedora ?? '',
                endereco_empresa_vencedora: existing?.endereco_empresa_vencedora ?? '',
                representante_legal_empresa: existing?.representante_legal_empresa ?? '',
                cpf_representante: existing?.cpf_representante ?? '',
                valor_total: existing?.valor_total ?? '',
                numero_ata_registro_precos: existing?.numero_ata_registro_precos ?? '',
                cargo_controle_interno: existing?.cargo_controle_interno ?? '',
                cargo_responsavel: existing?.cargo_responsavel ?? '',
                merenda_escolar: existing?.merenda_escolar ?? '',
                veiculos: existing?.veiculos ?? '',
                valor_melhor_proposta: existing?.valor_melhor_proposta ?? '',
                empresas_participantes: existing?.empresas_participantes ?? '',

                // Controle de confirmação
                confirmed: {
                    ...docDatesConfirmed,
                    anexo_atos_sessao: !!existing?.anexo_atos_sessao,
                    anexo_proposta: !!existing?.anexo_proposta,
                    anexo_proposta_readequada: !!existing?.anexo_proposta_readequada,
                    anexo_habilitacao: !!existing?.anexo_habilitacao,
                    anexo_recurso_contratacoes: !!existing?.anexo_recurso_contratacoes,
                    anexo_publicacoes: !!existing?.anexo_publicacoes,
                    orgao_responsavel: !!existing?.orgao_responsavel,
                    cnpj: !!existing?.cnpj,
                    endereco: !!existing?.endereco,
                    responsavel: !!existing?.responsavel,
                    cpf_responsavel: !!existing?.cpf_responsavel,
                    razao_social: !!existing?.razao_social,
                    cnpj_empresa_vencedora: !!existing?.cnpj_empresa_vencedora,
                    endereco_empresa_vencedora: !!existing?.endereco_empresa_vencedora,
                    representante_legal_empresa: !!existing?.representante_legal_empresa,
                    cpf_representante: !!existing?.cpf_representante,
                    valor_total: !!existing?.valor_total,
                    numero_ata_registro_precos: !!existing?.numero_ata_registro_precos,
                    cargo_controle_interno: !!existing?.cargo_controle_interno,
                    cargo_responsavel: !!existing?.cargo_responsavel,
                    merenda_escolar: !!existing?.merenda_escolar,
                    veiculos: !!existing?.veiculos,
                    valor_melhor_proposta: !!existing?.valor_melhor_proposta,
                    empresas_participantes: !!existing?.empresas_participantes,
                },

                toggleConfirm(field) {
                    if (!this.confirmed[field]) {
                        this.saveField(field);
                    } else {
                        this.confirmed[field] = false;
                    }
                },

                async saveField(field) {
                    console.log('Salvando campo:', field);

                    // Evita envios duplicados simultâneos do MESMO campo. Uploads grandes
                    // demoram; sem isso o usuário reclica e dispara vários uploads em
                    // paralelo (todos travados), sem nenhum feedback.
                    this._uploading = this._uploading || {};
                    if (this._uploading[field]) {
                        console.warn('Envio já em andamento para', field, '- ignorando clique.');
                        showMessage('Envio de ' + field + ' já em andamento, aguarde...', 'info');
                        return;
                    }

                    // Lista de campos permitidos - INCLUIR TODOS OS CAMPOS NECESSÁRIOS
                    const allowedFields = [
                        // Campos de arquivo
                        'anexo_atos_sessao',
                        'anexo_proposta',
                        'anexo_proposta_readequada',
                        'anexo_habilitacao',
                        'anexo_recurso_contratacoes',
                        'anexo_planilha',
                        'anexo_publicacoes',

                        // Campos de texto
                        'orgao_responsavel',
                        'cnpj',
                        'endereco',
                        'responsavel',
                        'cpf_responsavel',
                        'razao_social',
                        'cnpj_empresa_vencedora',
                        'endereco_empresa_vencedora',
                        'representante_legal_empresa',
                        'cpf_representante',
                        'valor_total',
                        'numero_ata_registro_precos',
                        'cargo_controle_interno',
                        'cargo_responsavel',
                        'merenda_escolar',
                        'veiculos',
                        'valor_melhor_proposta',
                        'empresas_participantes',
                    ];

                    if (!allowedFields.includes(field) && !field.startsWith('data_doc_')) {
                        console.error('Campo não permitido:', field);
                        showMessage('Campo não permitido: ' + field, 'error');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('processo_id', {{ $processo->id }});
                    if (this.homologacao_id) {
                        formData.append('homologacao_id', this.homologacao_id);
                    }
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                    // Verificar se é um campo de arquivo ou texto
                    if (this.isFileField(field)) {
                        const fileInput = document.getElementById(field);
                        if (fileInput && fileInput.files.length > 0) {
                            formData.append(field, fileInput.files[0]);
                            console.log('Arquivo selecionado:', fileInput.files[0].name);
                        } else {
                            formData.append(field, '');
                            console.log('Nenhum arquivo selecionado, limpando campo');
                        }
                    } else {
                        // Campo de texto - usar o valor do Alpine.js
                        formData.append(field, this[field]);
                        console.log('Valor do campo texto:', this[field]);
                    }

                    this._uploading[field] = true;

                    // Feedback imediato — uploads grandes podem levar minutos.
                    if (this.isFileField(field)) {
                        const fi = document.getElementById(field);
                        const nome = (fi && fi.files.length) ? fi.files[0].name : '';
                        const mb = (fi && fi.files.length) ? Math.round(fi.files[0].size / 1048576) : 0;
                        showMessage('Enviando ' + (nome || field) + (mb ? ' (' + mb + ' MB)' : '') + '... isso pode levar alguns minutos, não feche a página.', 'info');
                    }

                    // Timeout de segurança: se o servidor/proxy "engolir" o upload sem
                    // responder, aborta e mostra erro em vez de travar para sempre.
                    const ctrl = new AbortController();
                    const timeoutId = setTimeout(() => ctrl.abort(), 20 * 60 * 1000);

                    try {
                        const response = await fetch("{{ route('admin.processos.finalizacao.store', $processo) }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData,
                            signal: ctrl.signal
                        });

                        // Lê como texto e tenta JSON — assim respostas de erro do servidor
                        // (413/504/500 em HTML) viram mensagem clara com o status, em vez
                        // de cair silenciosamente em "nada aconteceu".
                        const raw = await response.text();
                        let responseData = null;
                        try { responseData = raw ? JSON.parse(raw) : null; } catch (e) { responseData = null; }
                        console.log('Resposta do servidor:', response.status, responseData ?? raw.slice(0, 300));

                        if (response.ok && responseData && responseData.success) {
                            this.confirmed[field] = true;

                            if (responseData.data && responseData.data[field]) {
                                this[field] = responseData.data[field];
                            }

                            if (this.isFileField(field)) {
                                const fileInput = document.getElementById(field);
                                if (fileInput && fileInput.files.length > 0) {
                                    showMessage('Arquivo ' + fileInput.files[0].name + ' salvo com sucesso!', 'success');
                                } else {
                                    showMessage('Campo ' + field + ' limpo com sucesso!', 'success');
                                }
                            } else {
                                showMessage('Alterações salvas', 'success');
                            }
                        } else {
                            this.confirmed[field] = false;
                            console.error('Erro ao salvar campo:', field, 'HTTP', response.status, raw.slice(0, 500));
                            const errorMessage = (responseData && responseData.message)
                                ? responseData.message
                                : ('Falha ao salvar ' + field + ' (HTTP ' + response.status + '). Arquivo grande demais ou bloqueado pelo servidor.');
                            showMessage(errorMessage, 'error');
                        }
                    } catch (error) {
                        this.confirmed[field] = false;
                        if (error.name === 'AbortError') {
                            console.error('Upload abortado por timeout:', field);
                            showMessage('O envio de ' + field + ' demorou demais e foi cancelado. Verifique o tamanho do arquivo e os limites do servidor.', 'error');
                        } else {
                            console.error('Erro de rede ao salvar campo:', field, error);
                            showMessage('Erro de rede ao salvar ' + field, 'error');
                        }
                    } finally {
                        clearTimeout(timeoutId);
                        this._uploading[field] = false;
                    }
                },

                isFileField(field) {
                    const fileFields = [
                        'anexo_atos_sessao',
                        'anexo_proposta',
                        'anexo_proposta_readequada',
                        'anexo_habilitacao',
                        'anexo_recurso_contratacoes',
                        'anexo_publicacoes',
                    ];
                    return fileFields.includes(field);
                },

                submitForm() {
                    this.$el.submit();
                }
            };
        }
    </script>

    <script>
        /**
         * Inicia o download de todos os PDFs com feedback visual de progresso.
         * Reutiliza as mesmas funções definidas em iniciar.blade.php caso ambas
         * estejam na mesma sessão — caso contrário, as funções estão duplicadas aqui.
         */
        // Gestão do Loader Full Screen - Premium Redesign
        function getOverlay() {
            let overlay = document.getElementById('fs-download-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'fs-download-overlay';
                overlay.className = 'fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden transition-all duration-500 opacity-0';
                overlay.innerHTML = `
                    <!-- Glass modal -->
                    <div class="relative flex flex-col items-center w-full max-w-md p-10 mx-4 overflow-hidden bg-white/95 shadow-2xl backdrop-blur-xl rounded-[2rem] border border-white/60 transform transition-all duration-500 translate-y-12 opacity-0" id="fs-download-modal">
                        
                        <!-- Decorative Glow -->
                        <div class="absolute top-0 w-full h-32 opacity-60 bg-gradient-to-b from-blue-50 to-transparent"></div>
                        
                        <!-- Icon Container -->
                        <div class="relative flex items-center justify-center w-28 h-28 mb-6">
                            <!-- Pulsing background rings -->
                            <div id="fs-ring-bounce" class="absolute inset-0 border-4 border-blue-100 rounded-full animate-ping opacity-60"></div>
                            <div id="fs-ring-static" class="absolute inset-0 bg-gradient-to-tr from-blue-50 to-emerald-50 rounded-full animate-pulse shadow-inner"></div>
                            
                            <!-- Rotating dashed ring -->
                            <svg id="fs-spinner-ring" class="absolute inset-0 w-full h-full text-blue-500 animate-[spin_4s_linear_infinite]" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="46" fill="transparent" stroke="currentColor" stroke-width="2.5" stroke-dasharray="25 15" stroke-linecap="round"></circle>
                            </svg>

                            <!-- Center Icon (Document) -->
                            <div class="relative z-10 text-blue-600 transition-colors duration-300" id="fs-center-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path>
                                </svg>
                            </div>
                        </div>

                        <h3 class="relative z-10 mb-2 text-2xl font-extrabold tracking-tight text-slate-800 text-center" id="fs-loader-title">Preparando Documentos</h3>
                        
                        <p class="relative z-10 w-full mb-6 text-[15px] font-medium text-center text-slate-500 min-h-[44px] flex items-center justify-center leading-tight" id="fs-loader-msg">Conectando à fila do servidor...</p>
                        
                        <!-- Progress Bar -->
                        <div class="relative z-10 w-full h-2.5 mb-4 overflow-hidden bg-slate-100 rounded-full shadow-inner" id="progress-container">
                            <div id="fs-progress-bar" class="h-full rounded-full bg-gradient-to-r from-blue-500 via-indigo-400 to-teal-400" style="width: 100%; background-size: 200% 100%; animation: fs-gradient-x 2s linear infinite;"></div>
                        </div>
                        
                        <!-- Info Alert -->
                        <div class="relative z-10 flex items-start p-4 mt-2 mb-2 rounded-xl bg-amber-50/80 border border-amber-200/60 text-amber-800 shadow-sm transition-opacity duration-300" id="fs-alert-box">
                            <svg class="w-5 h-5 mr-3 flex-shrink-0 text-amber-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <p class="text-[13px] leading-relaxed font-medium">Por favor, mantenha esta aba aberta. O processamento de processos complexos pode demorar <strong>alguns minutos</strong>.</p>
                        </div>
                        
                        <!-- Action Button -->
                        <button id="fs-loader-close" class="relative z-10 hidden w-full px-6 py-3.5 mt-4 text-[15px] font-bold text-white transition-all transform shadow-md shadow-slate-200 bg-slate-800 rounded-xl hover:bg-slate-700 hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2" onclick="fecharOverlay()">
                            Voltar e Tentar Novamente
                        </button>
                    </div>
                `;
                
                // Adiciona os keyframes de gradiente customizados
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes fs-gradient-x {
                        0%, 100% { background-position: 0% 50%; }
                        50% { background-position: 100% 50%; }
                    }
                `;
                document.head.appendChild(style);
                document.body.appendChild(overlay);
            }
            return overlay;
        }

        function mostrarOverlay() {
            const overlay = getOverlay();
            const modal = document.getElementById('fs-download-modal');
            const closeBtn = document.getElementById('fs-loader-close');
            
            // Start state
            closeBtn.classList.add('hidden');
            document.getElementById('fs-alert-box').classList.remove('hidden', 'opacity-0');
            document.getElementById('progress-container').classList.remove('hidden');
            
            document.getElementById('fs-loader-title').textContent = 'Processando em Lote';
            document.getElementById('fs-loader-title').className = 'relative z-10 mb-2 text-2xl font-extrabold tracking-tight text-slate-800 text-center';
            
            document.getElementById('fs-center-icon').className = 'relative z-10 text-blue-600 transition-colors duration-300';
            document.getElementById('fs-center-icon').innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path>
                </svg>
            `;
            
            document.getElementById('fs-spinner-ring').classList.remove('hidden');
            document.getElementById('fs-ring-bounce').classList.remove('hidden');
            
            // Show overlay
            overlay.classList.remove('hidden');
            
            // Trigger animation
            requestAnimationFrame(() => {
                overlay.classList.remove('opacity-0');
                overlay.classList.add('opacity-100');
                
                modal.classList.remove('translate-y-12', 'opacity-0');
                modal.classList.add('translate-y-0', 'opacity-100');
            });
        }

        function fecharOverlay() {
            const overlay = document.getElementById('fs-download-overlay');
            const modal = document.getElementById('fs-download-modal');
            if (overlay) {
                overlay.classList.remove('opacity-100');
                overlay.classList.add('opacity-0');
                
                modal.classList.remove('translate-y-0', 'opacity-100');
                modal.classList.add('translate-y-12', 'opacity-0');
                
                setTimeout(() => {
                    overlay.classList.add('hidden');
                }, 500); // Matches duration-500
            }
        }

        function atualizarOverlayText(msg) {
            const msgEl = document.getElementById('fs-loader-msg');
            if (msgEl) msgEl.innerHTML = msg;
        }

        function erroOverlay(msg) {
            document.getElementById('fs-loader-title').textContent = 'Ocorreu um Erro';
            document.getElementById('fs-loader-title').className = 'relative z-10 mb-2 text-2xl font-extrabold tracking-tight text-rose-600 text-center';
            document.getElementById('fs-loader-msg').innerHTML = `<span class="text-rose-600">${msg}</span>`;
            
            // Hide progress
            document.getElementById('progress-container').classList.add('hidden');
            document.getElementById('fs-alert-box').classList.add('hidden');
            
            // Update Icon
            document.getElementById('fs-spinner-ring').classList.add('hidden');
            document.getElementById('fs-ring-bounce').classList.add('hidden');
            document.getElementById('fs-center-icon').className = 'relative z-10 text-rose-500';
            document.getElementById('fs-center-icon').innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-14 h-14 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            `;
            
            document.getElementById('fs-loader-close').classList.remove('hidden');
        }

        if (typeof iniciarDownloadTodos === 'undefined') {
            function iniciarDownloadTodos(processoId, fase) {
                mostrarOverlay();
                atualizarOverlayText('Enviando solicitação para a fila...');

                const urlMap = {
                    iniciar:   `/admin/processos/${processoId}/documentos/baixar-todos`,
                    finalizar: `/admin/processos/${processoId}/finalizacao/documentos/baixar-todos`,
                };
                const url = urlMap[fase];

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.token) {
                            monitorarStatusDownload(data.token, fase);
                        } else {
                            throw new Error(data.message || 'Erro desconhecido ao iniciar processo.');
                        }
                    })
                    .catch(err => {
                         erroOverlay(err.message);
                    });
            }

            function monitorarStatusDownload(token, fase) {
                let tempoDecorrido = 0;
                atualizarOverlayText('Aguardando servidor mesclar os arquivos...');

                const intervalo = setInterval(() => {
                    fetch(`/admin/documentos-async/status/${token}`)
                        .then(res => res.json())
                        .then(data => {
                            tempoDecorrido += 5;

                            if (data.status === 'pronto') {
                                clearInterval(intervalo);
                                atualizarOverlayText('Pronto! Iniciando download...');
                                
                                setTimeout(() => {
                                    fecharOverlay();
                                    window.location.href = `/admin/documentos-async/download/${token}`;
                                }, 1500);
                            } else if (data.status === 'erro') {
                                clearInterval(intervalo);
                                erroOverlay(data.message || 'Erro no processamento em lote.');
                            } else {
                                const minutos = Math.floor(tempoDecorrido / 60);
                                const segundos = tempoDecorrido % 60;
                                const tempoFmt = minutos > 0 ? `${minutos}m ${segundos}s` : `${segundos}s`;
                                
                                const pontos = '.'.repeat((tempoDecorrido / 5) % 4);
                                atualizarOverlayText(`Gerando PDF em Background${pontos} \n(${tempoFmt} decorridos)`);
                            }
                        })
                        .catch(err => console.warn('Erro temporário no polling', err));
                }, 5000);
            }
        }
    </script>

    @include('Admin.Processos.partials.flash-toast')
@endsection
