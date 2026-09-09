@extends('layouts.app')

@section('page-title', 'Detalhes do Contrato do Sistema')
@section('page-subtitle', 'Informações completas do contrato gerado automaticamente')

@section('content')

    <div class="max-w-7xl mx-auto">
        {{-- Botão Voltar --}}
        <div class="mb-6">
            <a href="{{ route('admin.contratos.index', ['tipo' => 'sistema']) }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar para Lista
            </a>
        </div>

        {{-- Cabeçalho --}}
        <div class="mb-6 bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Contrato do Sistema</h2>
                        <p class="mt-1 text-sm text-gray-600">Processo: {{ $processo->numero_processo }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Botão Concluir --}}
                        @if($processo->contrato && $processo->contrato->situacao !== 'CONCLUÍDO')
                            <form action="{{ route('admin.contratos.concluir.sistema', $processo->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700" onclick="return confirm('Deseja realmente concluir este contrato? Esta ação não poderá ser desfeita.')">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Concluir Contrato
                                </button>
                            </form>
                        @endif
                        {{-- Botão Download --}}
                        @if($processo->contrato)
                            <a href="{{ route('admin.processos.contrato.download', ['processo' => $processo->id]) }}"
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                                <i class="fas fa-download mr-2"></i>
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
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Informações do Contrato</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Prefeitura</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $processo->prefeitura->nome ?? '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Número do Processo</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $processo->numero_processo }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Número do Contrato</label>
                                <p class="mt-1 text-sm text-gray-900">{{ optional($processo->contrato)->numero_contrato ?? '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Modalidade</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $processo->modalidade ? $processo->modalidade->getDisplayName() : '-' }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Tipo de Procedimento</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $processo->tipo_procedimento_nome ?? '-' }}
                                </span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Valor Total Estimado do Processo</label>
                                <p class="mt-1 text-lg font-bold text-gray-900">R$ {{ number_format($processo->valor_total_vencedores ?? 0, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Coluna 2: Vigência --}}
                    <div>
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Vigência</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Data de Assinatura</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ optional(optional($processo->contrato)->data_assinatura_contrato)->format('d/m/Y') ?? '-' }}
                                </p>
                            </div>

                            @if($processo->contrato)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Situação</label>
                                    @php
                                        $situacao = $processo->contrato->situacao;
                                        $cores = [
                                            'VIGENTE' => 'bg-green-100 text-green-800',
                                            'VENCIDO' => 'bg-red-100 text-red-800',
                                            'CONCLUÍDO' => 'bg-blue-100 text-blue-800',
                                            'PENDENTE' => 'bg-yellow-100 text-yellow-800'
                                        ];
                                        $cor = $cores[$situacao] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $cor }}">
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
                                <label class="block text-sm font-medium text-gray-500">Prazo de Vigência</label>
                                <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                    {{ $textoVigencia }}
                                </span>
                            </div>

                            @if($objeto_continuado == 'sim')
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Prorrogação</label>
                                <p class="mt-1 text-sm text-gray-900">Admite prorrogação (Objeto Continuado)</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Objeto --}}
                <div class="mt-8">
                    <h3 class="mb-2 text-lg font-semibold text-gray-900">Objeto do Contrato</h3>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-700">{!! $processo->objeto ?? 'Não informado' !!}</p>
                    </div>
                </div>

                {{-- Dados da Empresa Vencedora --}}
                <div class="mt-8">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Dados das Empresas Vencedoras</h3>

                    @if($processo->vencedores->isNotEmpty())
                        <div class="grid grid-cols-1 gap-6">
                            @foreach($processo->vencedores as $vencedor)
                                <div class="p-5 border border-gray-200 rounded-lg shadow-sm bg-white">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Razão Social</label>
                                            <p class="mt-1 text-sm text-gray-900 font-medium">{{ $vencedor->razao_social ?? '-' }}</p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">CPF/CNPJ</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ $vencedor->cpf_cnpj_formatado ?? ($vencedor->cpf_cnpj ?? ($vencedor->cnpj ?? '-')) }}</p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Valor Contratado</label>
                                            <p class="mt-1 text-sm font-bold text-green-700">R$ {{ number_format($vencedor->valor_vencedor ?? $vencedor->valor_total, 2, ',', '.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 bg-yellow-50 text-yellow-800 rounded-lg border border-yellow-200">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Nenhuma empresa vencedora registrada para este processo.
                        </div>
                    @endif
                </div>

                @if($processo->contrato)
                    {{-- Incidentes Contratuais (Aditivos) --}}
                    <div class="mt-8 pt-8 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-file-contract text-blue-600"></i>
                                Incidentes Contratuais (Aditivos)
                            </h3>
                            @can('fiscalizar contratos')
                                <button type="button"
                                        onclick="document.getElementById('modalNovoAditivoSistema').classList.remove('hidden')"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-plus mr-2"></i>
                                    Registrar Incidente Contratual
                                </button>
                            @endcan
                        </div>

                        {{-- Lista de Aditivos --}}
                        @if($processo->contrato->incidentes && $processo->contrato->incidentes->isNotEmpty())
                            <div class="space-y-2 mb-6">
                                @foreach($processo->contrato->incidentes as $incidente)
                                    <div class="flex items-center justify-between p-3 text-sm bg-white border border-gray-200 rounded-lg">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-gray-800">
                                                {{ ucfirst(str_replace('_', ' e ', $incidente->tipo)) }}
                                                <span class="ml-1 text-xs font-normal text-gray-400">
                                                    ({{ $incidente->categoria === 'obras' ? 'Obras' : 'Compras e Serviços' }})
                                                </span>
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                Registrado em {{ $incidente->created_at->format('d/m/Y') }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-1 text-[10px] font-bold text-[#009496] bg-[#009496]/10 rounded-full">
                                                Aditivo
                                            </span>
                                            <a href="{{ route('admin.incidentes.documentos', ['contrato_id' => $processo->contrato->id, 'incidente_id' => $incidente->id]) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#009496] bg-[#009496]/10 border border-[#009496]/20 rounded-md hover:bg-[#009496] hover:text-white transition-colors"
                                               title="Gerenciar Documentos do Aditivo">
                                                <i class="fas fa-file-alt"></i> Documentos
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-gray-50/50 rounded-xl p-5 border border-gray-200 mb-6 text-center text-gray-500">
                                Nenhum incidente contratual registrado.
                            </div>
                        @endif
                    </div>

                    {{-- Modal: Iniciar Novo Aditivo --}}
                    @can('fiscalizar contratos')
                        <div id="modalNovoAditivoSistema" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title-aditivo-sistema" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"
                                     onclick="document.getElementById('modalNovoAditivoSistema').classList.add('hidden')"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <form method="POST" action="{{ route('admin.incidentes.store', $processo->contrato->id) }}">
                                        @csrf
                                        <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                                            <div class="sm:flex sm:items-start">
                                                <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-teal-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                                    <i class="text-teal-600 fas fa-file-contract"></i>
                                                </div>
                                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title-aditivo-sistema">
                                                        Iniciar Novo Aditivo
                                                    </h3>
                                                    <div class="mt-4 space-y-4">
                                                        <div>
                                                            <label for="aditivo_tipo_sistema" class="block text-sm font-medium text-gray-700">Tipo de Aditivo <span class="text-red-500">*</span></label>
                                                            <select id="aditivo_tipo_sistema" name="tipo" required class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496] sm:text-sm">
                                                                <option value="">Selecione...</option>
                                                                <option value="prazo">Prorrogação de Prazo</option>
                                                                <option value="valor">Acréscimo de Valor</option>
                                                                <option value="prazo_valor">Prazo e Valor</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label for="aditivo_categoria_sistema" class="block text-sm font-medium text-gray-700">Categoria do Contrato <span class="text-red-500">*</span></label>
                                                            <select id="aditivo_categoria_sistema" name="categoria" required class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496] sm:text-sm">
                                                                <option value="">Selecione...</option>
                                                                <option value="compras_servicos">Compras e Serviços</option>
                                                                <option value="obras">Obras</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                                            <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-[#009496] border border-transparent rounded-md shadow-sm hover:bg-[#007779] focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                                Iniciar Aditivo
                                            </button>
                                            <button type="button" onclick="document.getElementById('modalNovoAditivoSistema').classList.add('hidden')" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                Cancelar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endcan

                    {{-- Acompanhamento da Execução Contratual --}}
                    <div class="mt-8 pt-8 border-t border-gray-100">
                        <h3 class="mb-6 text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-chart-line text-[#009496]"></i>
                            Acompanhamento da Execução (Fiscalizações & Ocorrências)
                        </h3>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {{-- Painel de Fiscalizações --}}
                            <div class="bg-gray-50/50 rounded-xl p-5 border border-gray-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-medium text-gray-800 flex items-center gap-2">
                                        <i class="fas fa-clipboard-check text-blue-600"></i>
                                        Fiscalizações ({{ $processo->contrato->fiscalizacoes->count() }})
                                    </h4>
                                    @can('fiscalizar contratos')
                                        <a href="{{ route('admin.fiscalizacoes.create', ['id' => $processo->contrato->id, 'type' => get_class($processo->contrato)]) }}"
                                           class="text-xs font-semibold text-[#009496] hover:underline flex items-center gap-1">
                                            <i class="fas fa-plus"></i> Nova Fiscalização
                                        </a>
                                    @endcan
                                </div>

                                @if($processo->contrato->fiscalizacoes->isNotEmpty())
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-xs text-left">
                                            <thead class="text-gray-500 uppercase bg-gray-100/50">
                                                <tr>
                                                    <th class="px-3 py-2 rounded-l-lg">Nº</th>
                                                    <th class="px-3 py-2">Data</th>
                                                    <th class="px-3 py-2">Conclusão</th>
                                                    <th class="px-3 py-2 text-right rounded-r-lg">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($processo->contrato->fiscalizacoes as $fisc)
                                                    <tr class="hover:bg-gray-50/80 transition-colors">
                                                        <td class="px-3 py-2.5 font-medium text-gray-900">
                                                            {{ $fisc->numero_fiscalizacao }}
                                                        </td>
                                                        <td class="px-3 py-2.5 text-gray-600">
                                                            {{ $fisc->data_fiscalizacao?->format('d/m/Y') ?? '—' }}
                                                        </td>
                                                        <td class="px-3 py-2.5">
                                                            <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $fisc->conclusao_badge_class }}">
                                                                {{ $fisc->conclusao_fiscal?->getDisplayName() ?? '—' }}
                                                            </span>
                                                        </td>
                                                        <td class="px-3 py-2.5 text-right">
                                                            <div class="flex items-center justify-end gap-1.5 font-medium">
                                                                <a href="{{ route('admin.fiscalizacoes.show', $fisc->id) }}"
                                                                   class="p-1 text-blue-600 hover:bg-blue-50 rounded" title="Visualizar">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="{{ route('admin.fiscalizacoes.edit', $fisc->id) }}"
                                                                   class="p-1 text-yellow-600 hover:bg-yellow-50 rounded" title="Editar">
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
                                    <div class="text-center py-6 text-gray-400">
                                        <i class="fas fa-clipboard-check text-2xl mb-2"></i>
                                        <p class="text-xs">Nenhuma fiscalização registrada para este contrato.</p>
                                    </div>
                                @endif
                            </div>

                            {{-- Painel de Ocorrências --}}
                            <div class="bg-gray-50/50 rounded-xl p-5 border border-gray-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-medium text-gray-800 flex items-center gap-2">
                                        <i class="fas fa-triangle-exclamation text-amber-600"></i>
                                        Ocorrências ({{ $processo->contrato->ocorrencias->count() }})
                                    </h4>
                                    @can('fiscalizar contratos')
                                        <a href="{{ route('admin.ocorrencias.create', ['id' => $processo->contrato->id, 'type' => get_class($processo->contrato)]) }}"
                                           class="text-xs font-semibold text-[#009496] hover:underline flex items-center gap-1">
                                            <i class="fas fa-plus"></i> Registrar Ocorrência
                                        </a>
                                    @endcan
                                </div>

                                @if($processo->contrato->ocorrencias->isNotEmpty())
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-xs text-left">
                                            <thead class="text-gray-500 uppercase bg-gray-100/50">
                                                <tr>
                                                    <th class="px-3 py-2 rounded-l-lg">Nº</th>
                                                    <th class="px-3 py-2">Data</th>
                                                    <th class="px-3 py-2">Status</th>
                                                    <th class="px-3 py-2 text-right rounded-r-lg">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($processo->contrato->ocorrencias as $ocor)
                                                    <tr class="hover:bg-gray-50/80 transition-colors">
                                                        <td class="px-3 py-2.5 font-medium text-gray-900">
                                                            {{ $ocor->numero_ocorrencia }}
                                                        </td>
                                                        <td class="px-3 py-2.5 text-gray-600">
                                                            {{ $ocor->data_ocorrencia?->format('d/m/Y') ?? '—' }}
                                                        </td>
                                                        <td class="px-3 py-2.5">
                                                            <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $ocor->status_badge_class }}">
                                                                {{ $ocor->status_texto }}
                                                            </span>
                                                        </td>
                                                        <td class="px-3 py-2.5 text-right font-medium">
                                                            <div class="flex items-center justify-end gap-1.5 font-medium">
                                                                <a href="{{ route('admin.ocorrencias.show', $ocor->id) }}"
                                                                   class="p-1 text-blue-600 hover:bg-blue-50 rounded" title="Visualizar">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                @if($ocor->status->value !== 'concluida')
                                                                    <a href="{{ route('admin.ocorrencias.edit', $ocor->id) }}"
                                                                       class="p-1 text-yellow-600 hover:bg-yellow-50 rounded" title="Editar">
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
                                    <div class="text-center py-6 text-gray-400">
                                        <i class="fas fa-triangle-exclamation text-2xl mb-2"></i>
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
