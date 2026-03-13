@extends('layouts.app')
@section('page-title', 'Detalhes do PCA')
@section('page-subtitle', 'Visualização do Plano de Contratação Anual: ' . ($pca->numero_pca ?? $pca->id))

@section('content')
<div class="py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">PCA {{ $pca->numero_pca ?? $pca->id }}</h2>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.pcas.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0596A2]">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            
            <a href="{{ route('admin.pcas.pdf', $pca->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white transition-colors bg-gray-600 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500" target="_blank">
                <i class="fas fa-file-pdf"></i> Baixar PDF
            </a>

            @if(in_array($pca->status, ['pendente', 'em_analise']))
            <a href="{{ route('admin.pcas.edit', $pca->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white transition-colors bg-yellow-600 rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                <i class="fas fa-edit"></i> Editar PCA
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Coluna Esquerda: Dados Gerais e Equipe -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Card: Informações GERAIS -->
            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Informações do Plano</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Status</dt>
                            <dd class="text-sm text-gray-900 col-span-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    @if($pca->status === 'pendente') bg-yellow-100 text-yellow-800
                                    @elseif($pca->status === 'em_analise') bg-blue-100 text-blue-800
                                    @elseif($pca->status === 'aprovado') bg-green-100 text-green-800
                                    @elseif($pca->status === 'recusado') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $pca->status)) }}
                                </span>
                            </dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Prefeitura</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $pca->prefeitura->nome ?? 'N/I' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Nº PCA</dt>
                            <dd class="text-sm font-semibold text-gray-900 col-span-2">{{ $pca->numero_pca ?? 'S/N' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Exercício</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $pca->exercicio }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Período Elab.</dt>
                            <dd class="text-sm text-gray-900 col-span-2">
                                @if($pca->periodo_elaboracao_inicio && $pca->periodo_elaboracao_fim)
                                    {{ $pca->periodo_elaboracao_inicio->format('d/m/Y') }} a {{ $pca->periodo_elaboracao_fim->format('d/m/Y') }}
                                @else
                                    <span class="text-gray-400">Não informado</span>
                                @endif
                            </dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Criado em</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $pca->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Card: EQUIPE DE ELABORAÇÃO -->
            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Equipe de Elaboração</h3>
                </div>
                <div class="p-0">
                    <ul class="divide-y divide-gray-100">
                        @if(!empty($pca->equipe_elaboracao) && is_array($pca->equipe_elaboracao))
                            @foreach($pca->equipe_elaboracao as $membro)
                                <li class="p-6 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mt-1">
                                            <i class="fas fa-user-circle text-gray-400 text-2xl"></i>
                                        </div>
                                        <div class="ml-4">
                                            <h4 class="text-sm font-bold text-gray-900">{{ $membro['responsavel'] ?? 'N/I' }}</h4>
                                            <p class="text-sm text-gray-500 mt-1">Unidade: {{ \App\Models\Unidade::find($membro['unidade_id'])->nome ?? 'N/I' }}</p>
                                            @if(!empty($membro['numero_portaria']))
                                                <p class="text-xs text-gray-400 mt-1">Portaria nº {{ $membro['numero_portaria'] }}
                                                @if(!empty($membro['data_portaria']))
                                                    - {{ \Carbon\Carbon::parse($membro['data_portaria'])->format('d/m/Y') }}
                                                @endif
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @else
                            <li class="p-6 text-center text-gray-400 text-sm">Nenhum membro informado.</li>
                        @endif
                    </ul>
                </div>
            </div>

        </div>

        <!-- Coluna Direita: DETALHAMENTO DOS ITENS -->
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden min-h-full">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Itens do Plano ({{ $pca->itens->count() }})</h3>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-[#0596A2] text-white">
                        Total: R$ {{ number_format($pca->itens->sum('valor_estimado'), 2, ',', '.') }}
                    </span>
                </div>
                
                <div class="p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">#</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unid Requisitante</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modalidade</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Objeto</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Est. (R$)</th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Prorrog?</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($pca->itens as $index => $item)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-900">{{ $index + 1 }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-700">{{ $item->unidade->nome ?? 'N/I' }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-700">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $item->modalidade ?? 'N/I' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-900">
                                            {{ $item->descricao_classe_grupo }}
                                            
                                            <div class="mt-2 flex items-center space-x-2">
                                                @switch($item->grau_prioridade)
                                                    @case('alto')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-800 uppercase">Prioridade Alta</span>
                                                        @break
                                                    @case('medio')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-yellow-100 text-yellow-800 uppercase">Prioridade Média</span>
                                                        @break
                                                    @case('baixo')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-800 uppercase">Prioridade Baixa</span>
                                                        @break
                                                @endswitch
                                            </div>

                                            @if($item->data_inicio_providencias || $item->data_desejada_conclusao)
                                                <div class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                                                    <i class="far fa-calendar-alt"></i>
                                                    Prov: {{ $item->data_inicio_providencias ? $item->data_inicio_providencias->format('d/m/Y') : '-' }} | 
                                                    Concl: {{ $item->data_desejada_conclusao ? $item->data_desejada_conclusao->format('d/m/Y') : '-' }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-right font-bold text-green-600">
                                            {{ number_format($item->valor_estimado, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                                            {!! $item->prorrogacao_contrato ? '<i class="fas fa-check-circle text-green-500 text-lg" title="Sim"></i>' : '<i class="fas fa-times-circle text-red-400 text-lg" title="Não"></i>' !!}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                            <i class="fas fa-box-open text-3xl mb-3 d-block block"></i>
                                            <p class="text-sm">Nenhum item adicionado a este plano de contratação.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
