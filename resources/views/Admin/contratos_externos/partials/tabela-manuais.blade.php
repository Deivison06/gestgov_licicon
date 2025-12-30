<div class="overflow-x-auto">
    <table class="min-w-full text-left border-collapse">
        <thead>
            <tr class="border-b border-gray-200 bg-white text-xs font-semibold text-gray-500 uppercase tracking-wider">
                @if(auth()->user()->hasRole('admin'))
                <th class="px-6 py-4">Prefeitura</th>
                @endif
                <th class="px-6 py-4">Empresa</th>
                <th class="px-6 py-4">Contratante</th>
                <th class="px-6 py-4">Modalidade / Tipo</th>
                <th class="px-6 py-4">Processo / Contrato</th>
                <th class="px-6 py-4 text-center">Vigência</th>
                <th class="px-6 py-4 text-center">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white">
            @forelse($contratos as $contrato)
                <tr class="hover:bg-gray-50 transition-colors duration-150 group">
                    {{-- Prefeitura (só mostra para admin) --}}
                    @if(auth()->user()->hasRole('admin'))
                    <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm text-gray-900">
                        <div class="font-medium text-gray-900 max-w-[150px] truncate" title="{{ $contrato->prefeitura->nome ?? 'N/A' }}">
                            {{ $contrato->prefeitura->nome ?? '-' }}
                        </div>
                    </td>
                    @endif

                    {{-- Empresa --}}
                    <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm text-gray-900">
                        <div class="font-medium text-gray-900 max-w-[200px] truncate" title="{{ $contrato->empresa->razao_social ?? 'N/A' }}">
                            {{ $contrato->empresa->razao_social ?? 'Empresa não vinculada' }}
                        </div>
                        <div class="text-xs text-gray-500">
                            CNPJ: {{ $contrato->empresa->cnpj_formatado ?? '-' }}
                        </div>
                    </td>

                    {{-- Contratante (Secretaria) --}}
                    <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm text-gray-900">
                        <div class="max-w-[180px] truncate" title="{{ $contrato->secretaria->nome ?? '' }}">
                            {{ $contrato->secretaria->nome ?? 'Não Informado' }}
                        </div>
                    </td>

                    {{-- Modalidade e Tipo --}}
                    <td class="px-6 pt-5 pb-3 whitespace-nowrap">
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-semibold text-gray-700 uppercase">
                                {{ $contrato->modalidade ? $contrato->modalidade->getDisplayName() : 'N/A' }}
                            </span>
                            {{-- Badge do Tipo de Contrato --}}
                            <span class="inline-flex w-fit items-center px-2 py-0.5 rounded text-xs font-medium {{ $contrato->tipo_contrato == 'Serviço' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $contrato->tipo_contrato }}
                            </span>
                        </div>
                    </td>

                    {{-- Números --}}
                    <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm text-gray-900">
                        <div class="font-bold text-gray-800">Proc: {{ $contrato->numero_processo }}</div>
                        @if($contrato->numero_contrato)
                            <div class="text-xs text-gray-500">Contr: {{ $contrato->numero_contrato }}</div>
                        @endif
                    </td>

                    {{-- Status e Vigência --}}
                    <td class="px-6 pt-5 pb-3 whitespace-nowrap text-center">
                        @php
                            // Definição de Cores Simples baseada no Status
                            $statusColors = [
                                'VIGENTE' => 'bg-green-100 text-green-800',
                                'VENCIDO' => 'bg-red-100 text-red-800',
                                'PENDENTE' => 'bg-yellow-100 text-yellow-800',
                            ];
                            $cor = $statusColors[$contrato->situacao] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cor }}">
                            {{ $contrato->situacao }}
                        </span>
                        <div class="text-xs text-gray-500 mt-1">
                            Fim: {{ $contrato->data_finalizacao ? $contrato->data_finalizacao->format('d/m/Y') : '-' }}
                        </div>
                    </td>

                    {{-- Ações --}}
                    <td class="px-6 pt-5 pb-3 whitespace-nowrap text-center text-sm font-medium">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="{{ route('admin.contratos.edit', $contrato->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white transition-colors duration-200 bg-amber-500 rounded-lg hover:bg-amber-600 shadow-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>

                            <form action="{{ route('admin.contratos.destroy', $contrato->id) }}" id="delete-form{{$contrato->id}}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        onclick="confirmDelete{{$contrato->id}}()"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white transition-colors duration-200 bg-red-600 rounded-lg hover:bg-red-700 shadow-sm">
                                    <i class="fas fa-trash-alt"></i> Excluir
                                </button>
                            </form>
                        </div>

                        <script>
                            function confirmDelete{{$contrato->id}}() {
                                Swal.fire({
                                    title: 'Excluir Contrato?',
                                    text: "Você excluirá este contrato! Esta ação não pode ser desfeita.",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#d33',
                                    cancelButtonColor: '#3085d6',
                                    confirmButtonText: 'Sim, excluir!',
                                    cancelButtonText: 'Cancelar',
                                    reverseButtons: true
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        document.getElementById('delete-form{{$contrato->id}}').submit();
                                    }
                                });
                            }
                        </script>
                    </td>
                </tr>

                {{-- Linha do Objeto --}}
                <tr class="border-b border-gray-200">
                    @php
                        $colspan = auth()->user()->hasRole('admin') ? 7 : 6;
                    @endphp
                    <td colspan="{{ $colspan }}" class="bg-[#F8FAFC] px-6 py-3 text-sm text-gray-600 leading-relaxed whitespace-normal">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-info-circle text-gray-400 mt-0.5 flex-shrink-0"></i>
                            <div class="min-w-0 break-words">
                                <span class="font-bold text-gray-800 text-xs uppercase mr-1">Objeto:</span>
                                <span class="italic">{{ $contrato->objeto }}</span>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    @php
                        $colspan = auth()->user()->hasRole('admin') ? 7 : 6;
                    @endphp
                    <td colspan="{{ $colspan }}" class="px-6 py-10 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-file-contract text-4xl text-gray-300 mb-3"></i>
                            <p class="font-medium">Nenhum contrato manual encontrado.</p>
                            <p class="text-sm mt-1">Clique em "Novo Contrato Manual" para começar.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>