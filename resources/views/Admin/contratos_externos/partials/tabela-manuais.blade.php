<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
        <tr class="border-b border-gray-200 bg-white text-xs font-semibold text-gray-500 uppercase tracking-wider">
            @if(!$isPrefeituraUser)
                <th class="px-4 py-3">Prefeitura</th>
            @endif
            <th class="px-4 py-3">Processo</th>
            <th class="px-4 py-3">Modalidade</th>
            <th class="px-4 py-3">Contrato</th>
            <th class="px-4 py-3">Empresa</th>
            <th class="px-4 py-3">Valor Total</th>
            <th class="px-4 py-3 text-center">Ações</th>
        </tr>
        </thead>
        <tbody class="bg-white">
        @forelse($contratos as $contrato)
            <!-- Linha principal -->
            <tr class="hover:bg-gray-50 transition-colors duration-150">
                @if(!$isPrefeituraUser)
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900 max-w-[120px] truncate">
                            {{ $contrato->prefeitura->nome ?? '-' }}
                        </div>
                    </td>
                @endif

                <td class="px-4 py-3">
                    <div class="text-sm font-bold text-gray-800">
                        {{ $contrato->numero_processo }}
                    </div>
                </td>

                <td class="px-4 py-3">
                    <div class="flex flex-col">
                            <span class="text-xs font-semibold text-gray-700">
                                {{ $contrato->modalidade ? $contrato->modalidade->getDisplayName() : 'N/A' }}
                            </span>
                        @if($contrato->tipo_contrato)
                            <span class="text-xs text-gray-500 mt-0.5">
                                    {{ $contrato->tipo_contrato }}
                                </span>
                        @endif
                    </div>
                </td>

                <td class="px-4 py-3">
                    <div class="flex flex-col">
                            <span class="text-sm font-bold text-gray-800">
                                {{ $contrato->numero_contrato ?? 'N/A' }}
                            </span>
                        @if($contrato->data_assinatura)
                            <span class="text-xs text-gray-500 mt-0.5">
                                    {{ $contrato->data_assinatura->format('d/m/Y') }}
                                </span>
                        @endif
                    </div>
                </td>

                <td class="px-4 py-3">
                    <div class="flex flex-col max-w-[150px]">
                            <span class="text-sm font-medium text-gray-900 truncate">
                                {{ $contrato->empresa->razao_social ?? '-' }}
                            </span>
                        @if($contrato->empresa->cnpj ?? false)
                            <span class="text-xs text-gray-500 mt-0.5">
                                    {{ preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $contrato->empresa->cnpj) }}
                                </span>
                        @endif
                    </div>
                </td>

                <td class="px-4 py-3">
                    <div class="text-sm font-bold text-green-700 text-center">
                        R$ {{ number_format($contrato->valor_total, 2, ',', '.') }}
                    </div>
                </td>

                <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-1 justify-center">
                        <a href="{{ route('admin.contratos.show.manual', $contrato->id) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-white bg-blue-500 rounded hover:bg-blue-600 transition-colors"
                           title="Visualizar detalhes">
                            <i class="fas fa-eye text-[10px]"></i>
                            <span>Ver</span>
                        </a>

                        @if($contrato->arquivo_contrato)
                            <a href="{{ asset($contrato->arquivo_contrato) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-white bg-green-500 rounded hover:bg-green-600 transition-colors"
                               title="Baixar PDF">
                                <i class="fas fa-download text-[10px]"></i>
                                <span>PDF</span>
                            </a>
                        @endif

                        <a href="{{ route('admin.contratos.edit', $contrato->id) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-white bg-amber-500 rounded hover:bg-amber-600 transition-colors">
                            <i class="fas fa-edit text-[10px]"></i>
                            <span>Editar</span>
                        </a>

                        <form action="{{ route('admin.contratos.destroy', $contrato->id) }}"
                              id="delete-form{{$contrato->id}}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    onclick="confirmDelete{{$contrato->id}}()"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700 transition-colors">
                                <i class="fas fa-trash-alt text-[10px]"></i>
                                <span>Excluir</span>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>

            <!-- Linha do objeto (expandida) -->
            <tr class="border-b border-gray-200">
                <td colspan="{{ $isPrefeituraUser ? 7 : 8 }}"
                    class="bg-gray-50 px-4 py-3">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-gray-400 mt-0.5 mr-2 flex-shrink-0"></i>
                        <div class="text-sm text-gray-700">
                                <span class="font-semibold text-gray-800 mr-2">
                                    Objeto:
                                </span>
                            <span class="text-gray-600">
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
                    $colspan = $isPrefeituraUser ? 7 : 8;
                @endphp
                <td colspan="{{ $colspan }}" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                        <i class="fas fa-file-signature text-4xl text-gray-300 mb-3"></i>
                        <p class="font-medium">Nenhum contrato manual encontrado</p>
                        <p class="text-sm mt-1 max-w-md text-gray-400">
                            Clique em "Novo Contrato Manual" para adicionar um novo contrato.
                        </p>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
