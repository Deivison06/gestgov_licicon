<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
        <tr class="border-b border-[var(--line)] bg-[var(--bg)] text-xs font-semibold text-[var(--ink-2)] uppercase tracking-wider">
            @if(!$isPrefeituraUser)
                <th class="px-4 py-3">Prefeitura</th>
            @endif
            <th class="px-4 py-3">Processo</th>
            <th class="px-4 py-3">Modalidade</th>
            <th class="px-4 py-3">Contrato</th>
            <th class="px-4 py-3">Empresa</th>
            <th class="px-4 py-3">Valor Total</th>
            <th class="px-4 py-3 text-center">Situação</th>
            <th class="px-4 py-3 text-center">Ações</th>
        </tr>
        </thead>
        <tbody class="bg-[var(--card)] divide-y divide-[var(--card-3)]">
        @forelse($contratos as $contrato)
            <!-- Linha principal -->
            <tr class="hover:bg-[var(--hover-bg)] transition-colors duration-150">
                @if(!$isPrefeituraUser)
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="text-sm font-medium text-[var(--ink)] max-w-[120px] truncate">
                            {{ $contrato->prefeitura->nome ?? '-' }}
                        </div>
                    </td>
                @endif

                <td class="px-4 py-3">
                    <div class="text-sm font-bold text-[var(--ink)]">
                        {{ $contrato->numero_processo }}
                    </div>
                    @if($contrato->numero_procedimento)
                        <div class="text-xs text-[var(--ink-2)] mt-0.5">
                            Proc: {{ $contrato->numero_procedimento }}
                        </div>
                    @endif
                </td>

                <td class="px-4 py-3">
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-[var(--ink)]">
                            {{ $contrato->modalidade ? $contrato->modalidade->getDisplayName() : 'N/A' }}
                        </span>
                        @if($contrato->tipo_contrato)
                            <span class="text-xs text-[var(--ink-2)] mt-0.5">
                                {{ $contrato->tipo_contrato }}
                            </span>
                        @endif
                    </div>
                </td>

                <td class="px-4 py-3">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-[var(--ink)]">
                            {{ $contrato->numero_contrato ?? 'N/A' }}
                        </span>
                        @if($contrato->data_assinatura)
                            <span class="text-xs text-[var(--ink-2)] mt-0.5">
                                {{ $contrato->data_assinatura->format('d/m/Y') }}
                            </span>
                        @endif
                    </div>
                </td>

                <td class="px-4 py-3">
                    <div class="flex flex-col max-w-[150px]">
                        <span class="text-sm font-medium text-[var(--ink)] truncate">
                            {{ $contrato->empresa->razao_social ?? '-' }}
                        </span>
                        @if($contrato->empresa->cnpj ?? false)
                            <span class="text-xs text-[var(--ink-2)] mt-0.5">
                                {{ preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $contrato->empresa->cnpj) }}
                            </span>
                        @endif
                    </div>
                </td>

                <td class="px-4 py-3">
                    <div class="text-sm font-bold text-emerald-400 text-center">
                        R$ {{ number_format($contrato->valor_total, 2, ',', '.') }}
                    </div>
                </td>

                <td class="px-4 py-3 text-center">
                    @php
                        $situacao = $contrato->situacao;
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
                </td>

                <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-1.5 justify-center">
                        <a href="{{ route('admin.contratos.show.manual', $contrato->id) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-[var(--on-mint)] bg-[var(--mint)] rounded hover:bg-[var(--ok)] transition-colors"
                           title="Visualizar detalhes">
                            <i class="fas fa-eye text-[10px]"></i>
                            <span>Ver</span>
                        </a>

                        @if($contrato->arquivo_contrato)
                            <a href="{{ asset($contrato->arquivo_contrato) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-white bg-red-700/80 border border-red-600 rounded hover:bg-red-700 transition-colors"
                               title="Baixar PDF">
                                <i class="fas fa-download text-[10px]"></i>
                                <span>PDF</span>
                            </a>
                        @endif

                        <a href="{{ route('admin.contratos.edit', $contrato->id) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-white bg-amber-600/80 border border-amber-500/40 rounded hover:bg-amber-600 transition-colors">
                            <i class="fas fa-edit text-[10px]"></i>
                            <span>Editar</span>
                        </a>

                        <form action="{{ route('admin.contratos.destroy', $contrato->id) }}"
                              id="delete-form{{$contrato->id}}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    onclick="confirmDelete{{$contrato->id}}()"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-white bg-red-700/80 border border-red-600/40 rounded hover:bg-red-700 transition-colors">
                                <i class="fas fa-trash-alt text-[10px]"></i>
                                <span>Excluir</span>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>

            <!-- Linha do objeto (expandida) -->
            <tr class="border-b border-[var(--line)] hover:bg-[var(--hover-bg)] transition-colors duration-150">
                <td colspan="{{ $isPrefeituraUser ? 8 : 9 }}"
                    class="bg-[var(--card-2)] hover:bg-[var(--hover-bg)] transition-colors duration-150 px-4 py-3">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-[var(--mint)] mt-0.5 mr-2 flex-shrink-0"></i>
                        <div class="text-sm text-[var(--ink)]">
                            <span class="font-bold text-[var(--mint)] text-xs uppercase mr-2">
                                Objeto:
                            </span>
                            <span class="text-[var(--ink-2)]">
                                {!! $contrato->objeto ?? 'Não informado' !!}
                            </span>
                        </div>
                    </div>
                </td>
            </tr>

            <script>
                function confirmDelete{{$contrato->id}}() {
                    Swal.fire({
                        title: 'Excluir Contrato?',
                        text: "Esta ação não pode ser desfeita!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Excluir',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('delete-form{{$contrato->id}}').submit();
                        }
                    });
                }
            </script>
        @empty
            <tr>
                @php
                    $colspan = $isPrefeituraUser ? 8 : 9;
                @endphp
                <td colspan="{{ $colspan }}" class="px-6 py-12 text-center text-[var(--ink-2)]">
                    <div class="flex flex-col items-center justify-center">
                        <i class="fas fa-file-signature text-4xl text-[var(--line)] mb-3"></i>
                        <p class="font-medium text-white">Nenhum contrato manual encontrado</p>
                        <p class="text-sm mt-1 max-w-md text-[var(--ink-2)]">
                            Clique em "Novo Contrato Manual" para adicionar um novo contrato.
                        </p>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
