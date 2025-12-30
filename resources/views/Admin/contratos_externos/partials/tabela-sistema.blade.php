<div class="overflow-x-auto">
    <table class="min-w-full text-left border-collapse">
        <thead>
            <tr class="border-b border-gray-200 bg-white text-xs font-semibold text-gray-500 uppercase tracking-wider">
                @if(auth()->user()->hasRole('admin'))
                    <th class="px-6 py-4">Prefeitura</th>
                @endif
                <th class="px-6 py-4">Número do Processo</th>
                <th class="px-6 py-4">Modalidade</th>
                <th class="px-6 py-4">Contrato</th>
                <th class="px-6 py-4 text-center">Data Geração</th>
                <th class="px-6 py-4 text-center">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white">
            @forelse($contratos as $processo)
                <tr class="hover:bg-gray-50 transition-colors duration-150 group">
                    @if(auth()->user()->hasRole('admin'))
                        <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm text-gray-900">
                            <div class="font-medium text-gray-900 max-w-[150px] truncate">
                                {{ $processo->prefeitura->nome ?? '-' }}
                            </div>
                        </td>
                    @endif

                    <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm text-gray-900">
                        <div class="font-bold text-gray-800">{{ $processo->numero_processo }}</div>
                        @if($processo->numero_procedimento)
                            <div class="text-xs text-gray-500">Proc: {{ $processo->numero_procedimento }}</div>
                        @endif
                    </td>

                    <td class="px-6 pt-5 pb-3 whitespace-nowrap">
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-semibold text-gray-700 uppercase">
                                {{ $processo->modalidade ? $processo->modalidade->getDisplayName() : 'N/A' }}
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ $processo->tipo_procedimento_nome ?? '-' }}
                            </span>
                        </div>
                    </td>

                    <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm text-gray-900">
                        @if($processo->contrato)
                            <div class="font-bold text-gray-800">
                                Contr: {{ $processo->contrato->numero_contrato ?? 'N/A' }}
                            </div>
                            @if($processo->contrato->data_assinatura_contrato)
                                <div class="text-xs text-gray-500">
                                    Assinatura:
                                    {{ $processo->contrato->data_assinatura_contrato->format('d/m/Y') }}
                                </div>
                            @endif
                        @else
                            <span class="text-xs text-gray-500 italic">Contrato não gerado</span>
                        @endif
                    </td>

                    <td class="px-6 pt-5 pb-3 whitespace-nowrap text-center text-sm text-gray-500">
                        {{ $processo->created_at->format('d/m/Y') }}
                    </td>

                    <td class="px-6 pt-5 pb-3 whitespace-nowrap text-center text-sm font-medium">
                        @if($processo->contrato)

                           
                                {{-- Contrato gerado pelo SISTEMA --}}
                                <a href="{{ route('admin.processos.contrato.download', ['processo' => $processo->id]) }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white
                                        transition-colors duration-200 bg-cyan-600 rounded-lg hover:bg-cyan-700 shadow-sm">
                                    <i class="fas fa-download"></i> Baixar
                                </a>
                        @endif
                    </td>

                </tr>
                <tr class="border-b border-gray-200">
                   
                    <td colspan="5"
                        class="bg-[#F8FAFC] px-6 py-3 text-sm text-gray-600 leading-relaxed whitespace-normal">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-info-circle text-gray-400 mt-0.5 flex-shrink-0"></i>
                            <div class="min-w-0 break-words">
                                <span class="font-bold text-gray-800 text-xs uppercase mr-1">
                                    Objeto:
                                </span>
                                <span class="italic">
                                    {!! $processo->objeto ?? 'Não informado' !!}
                                </span>
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
                            <i class="fas fa-cogs text-4xl text-gray-300 mb-3"></i>
                            <p class="font-medium">Nenhum contrato do sistema encontrado.</p>
                            <p class="text-sm mt-1">Os contratos são gerados automaticamente após a finalização dos processos.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>