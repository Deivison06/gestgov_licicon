<div class="overflow-x-auto">
    <table class="min-w-full text-left border-collapse">
        <thead>
        <tr class="border-b border-[var(--line)] bg-[var(--bg)] text-xs font-semibold text-[var(--ink-2)] uppercase tracking-wider">
            @if(!$isPrefeituraUser)
                <th class="px-6 py-4">Prefeitura</th>
            @endif
            <th class="px-6 py-4">Número do Processo</th>
            <th class="px-6 py-4">Modalidade</th>
            <th class="px-6 py-4">Contrato</th>
            <th class="px-6 py-4">Empresa</th>
            <th class="px-6 py-4">Valor Total</th>
            <th class="px-6 py-4 text-center">Situação</th>
            <th class="px-6 py-4 text-center">Ações</th>
        </tr>
        </thead>
        <tbody class="bg-[var(--card)] divide-y divide-[var(--card-3)]">
        @forelse($contratos as $processo)
            <tr class="hover:bg-[var(--hover-bg)] transition-colors duration-150 group">
                @if(!$isPrefeituraUser)
                    <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm text-[var(--ink)]">
                        <div class="font-medium text-[var(--ink)] max-w-[150px] truncate">
                            {{ $processo->prefeitura->nome ?? '-' }}
                        </div>
                    </td>
                @endif

                <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm">
                    <div class="font-bold text-[var(--ink)]">{{ $processo->numero_processo }}</div>
                    @if($processo->numero_procedimento)
                        <div class="text-xs text-[var(--ink-2)]">Proc: {{ $processo->numero_procedimento }}</div>
                    @endif
                </td>

                <td class="px-6 pt-5 pb-3 whitespace-nowrap">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-[var(--ink)] uppercase">
                            {{ $processo->modalidade ? $processo->modalidade->getDisplayName() : 'N/A' }}
                        </span>
                        <span class="text-xs text-[var(--ink-2)]">
                            {{ $processo->tipo_procedimento_nome ?? '-' }}
                        </span>
                    </div>
                </td>

                <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm">
                    @if($processo->contrato)
                        <div class="font-bold text-[var(--ink)]">
                            Contr: {{ $processo->contrato->numero_contrato ?? 'N/A' }}
                        </div>
                        @if($processo->contrato->data_assinatura_contrato)
                            <div class="text-xs text-[var(--ink-2)]">
                                Assinatura:
                                {{ $processo->contrato->data_assinatura_contrato->format('d/m/Y') }}
                            </div>
                        @endif
                    @else
                        <span class="text-xs text-[var(--ink-2)] italic">Contrato não gerado</span>
                    @endif
                </td>

                {{-- Empresa (vencedor) --}}
                <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm">
                    @php $vencedor = $processo->vencedores->first(); @endphp
                    @if($vencedor)
                        <div class="font-medium text-[var(--ink)] max-w-[180px] truncate" title="{{ $vencedor->razao_social }}">
                            {{ $vencedor->razao_social ?? '-' }}
                        </div>
                        @if($vencedor->cnpj)
                            <div class="text-xs text-[var(--ink-2)]">
                                {{ preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $vencedor->cnpj) }}
                            </div>
                        @endif
                        @if($processo->vencedores->count() > 1)
                            <div class="text-[10px] text-[var(--ink-2)]">+{{ $processo->vencedores->count() - 1 }} vencedor(es)</div>
                        @endif
                    @else
                        <span class="text-xs text-[var(--ink-2)] italic">-</span>
                    @endif
                </td>

                {{-- Valor Total (homologação do contrato ou soma dos vencedores) --}}
                <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm">
                    @php
                        $valorContrato = $processo->contrato?->homologacao?->valor_total
                            ?? $processo->vencedores->sum('valor_total');
                    @endphp
                    <span class="font-semibold text-emerald-400">
                        R$ {{ number_format($valorContrato ?? 0, 2, ',', '.') }}
                    </span>
                </td>

                <td class="px-6 pt-5 pb-3 whitespace-nowrap text-center text-sm">
                    @if($processo->contrato)
                        @php
                            $situacao = $processo->contrato->situacao;
                            $cores = [
                                'VIGENTE' => 'bg-emerald-900/40 text-emerald-300 border border-emerald-500/30',
                                'VENCIDO' => 'bg-red-900/40 text-red-300 border border-red-500/30',
                                'CONCLUÍDO' => 'bg-blue-900/40 text-blue-300 border border-blue-500/30',
                                'PENDENTE' => 'bg-amber-900/40 text-amber-300 border border-amber-500/30'
                            ];
                            $cor = $cores[$situacao] ?? 'bg-[var(--hover-bg)] text-[var(--ink-2)] border border-[var(--line)]';
                        @endphp
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $cor }}">
                            {{ $situacao }}
                        </span>
                    @else
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-[var(--hover-bg)] text-[var(--ink-2)] border border-[var(--line)]">
                            N/A
                        </span>
                    @endif
                </td>

                <td class="px-6 pt-5 pb-3 whitespace-nowrap text-center text-sm font-medium">
                    <div class="flex flex-wrap gap-1.5 justify-center">
                        {{-- Botão Ver --}}
                        <a href="{{ route('admin.contratos.show.sistema', $processo->id) }}"
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-[var(--on-mint)] bg-[var(--mint)] rounded-md hover:bg-[var(--ok)] transition-colors"
                        title="Visualizar detalhes">
                            <i class="fas fa-eye text-[11px]"></i>
                            <span>Ver</span>
                        </a>

                        @if($processo->contrato)
                            {{-- Contrato gerado pelo SISTEMA --}}
                            <a href="{{ route('admin.processos.contrato.download', ['processo' => $processo->id]) }}"
                                target="_blank"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-red-700/80 border border-red-600 rounded-md hover:bg-red-700 transition-colors"
                                title="Baixar PDF">
                                <i class="fas fa-download text-[11px]"></i>
                                <span>PDF</span>
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
            <tr class="border-b border-[var(--line)] hover:bg-[var(--hover-bg)] transition-colors duration-150">
                <td colspan="{{ $isPrefeituraUser ? 7 : 8 }}"
                    class="bg-[var(--card-2)] hover:bg-[var(--hover-bg)] transition-colors duration-150 px-6 py-3 text-sm text-[var(--ink)] leading-relaxed whitespace-normal">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-info-circle text-[var(--mint)] mt-0.5 flex-shrink-0"></i>
                        <div class="min-w-0 break-words">
                            <span class="font-bold text-[var(--mint)] text-xs uppercase mr-1">
                                Objeto:
                            </span>
                            <span class="text-[var(--ink-2)]">
                                {!! $processo->objeto ?? 'Não informado' !!}
                            </span>
                        </div>
                    </div>
                </td>
            </tr>

        @empty
            <tr>
                @php
                    $colspan = $isPrefeituraUser ? 7 : 8;
                @endphp
                <td colspan="{{ $colspan }}" class="px-6 py-10 text-center text-[var(--ink-2)]">
                    <div class="flex flex-col items-center justify-center">
                        <i class="fas fa-cogs text-4xl text-[var(--line)] mb-3"></i>
                        <p class="font-medium text-white">Nenhum contrato do sistema encontrado.</p>
                        <p class="text-sm mt-1 text-[var(--ink-2)]">Os contratos são gerados automaticamente após a finalização dos processos.</p>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
