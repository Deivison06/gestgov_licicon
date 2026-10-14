@extends('layouts.app')
@section('page-title', 'Detalhes do PCA')
@section('page-subtitle', 'Visualização do Plano de Contratação Anual: ' . ($pca->numero_pca ?? $pca->id))

@section('content')
<div class="py-8">
    <div class="flex items-center justify-between mb-8 flex-wrap gap-4">
        <div>
            <h2 class="text-2xl font-bold text-[#e6f0ee]">PCA {{ $pca->numero_pca ?? $pca->id }}</h2>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.pcas.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-[#a2b9b5] transition-colors bg-[#142825] border border-[#24403c] rounded-lg hover:bg-[#1a332f] hover:text-[#e6f0ee] focus:outline-none shadow-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            
            <a href="{{ route('admin.pcas.pdf', $pca->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-[#e6f0ee] transition-colors bg-[#142825] border border-[#24403c] rounded-lg hover:bg-[#1c3834] focus:outline-none shadow-sm" target="_blank">
                <i class="fas fa-file-pdf text-red-400"></i> Baixar PDF
            </a>

            @if(in_array($pca->status, ['pendente', 'em_analise']))
            <a href="{{ route('admin.pcas.edit', $pca->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-[#041d1a] transition-all bg-[#2DC197] rounded-lg hover:bg-[#26aa84] focus:outline-none shadow-sm">
                <i class="fas fa-edit"></i> Editar PCA
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Coluna Esquerda: Dados Gerais e Equipe -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Card: Informações GERAIS -->
            <div class="card !rounded-2xl overflow-hidden shadow-sm">
                <div class="card-h">
                    <h3 class="text-lg font-bold text-[#e6f0ee]">Informações do Plano</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-[#a2b9b5] col-span-1">Status</dt>
                            <dd class="text-sm col-span-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    @if($pca->status === 'pendente') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                    @elseif($pca->status === 'em_analise') bg-blue-500/10 text-blue-400 border border-blue-500/20
                                    @elseif($pca->status === 'aprovado') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                    @elseif($pca->status === 'recusado') bg-red-500/10 text-red-400 border border-red-500/20
                                    @else bg-gray-500/10 text-gray-400 border border-gray-500/20 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $pca->status)) }}
                                </span>
                            </dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-[#24403c] pt-3">
                            <dt class="text-sm font-medium text-[#a2b9b5] col-span-1">Prefeitura</dt>
                            <dd class="text-sm text-[#e6f0ee] col-span-2">{{ $pca->prefeitura->nome ?? 'N/I' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-[#24403c] pt-3">
                            <dt class="text-sm font-medium text-[#a2b9b5] col-span-1">Nº PCA</dt>
                            <dd class="text-sm font-semibold text-[#2DC197] col-span-2">{{ $pca->numero_pca ?? 'S/N' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-[#24403c] pt-3">
                            <dt class="text-sm font-medium text-[#a2b9b5] col-span-1">Exercício</dt>
                            <dd class="text-sm text-[#e6f0ee] col-span-2">{{ $pca->exercicio }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-[#24403c] pt-3">
                            <dt class="text-sm font-medium text-[#a2b9b5] col-span-1">Período Elab.</dt>
                            <dd class="text-sm text-[#e6f0ee] col-span-2">
                                @if($pca->periodo_elaboracao_inicio && $pca->periodo_elaboracao_fim)
                                    {{ $pca->periodo_elaboracao_inicio->format('d/m/Y') }} a {{ $pca->periodo_elaboracao_fim->format('d/m/Y') }}
                                @else
                                    <span class="text-[#527771]">Não informado</span>
                                @endif
                            </dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-[#24403c] pt-3">
                            <dt class="text-sm font-medium text-[#a2b9b5] col-span-1">Criado em</dt>
                            <dd class="text-sm text-[#a2b9b5] col-span-2">{{ $pca->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Card: EQUIPE DE ELABORAÇÃO -->
            <div class="card !rounded-2xl overflow-hidden shadow-sm">
                <div class="card-h">
                    <h3 class="text-lg font-bold text-[#e6f0ee]">Equipe de Elaboração</h3>
                </div>
                <div class="p-0">
                    <ul class="divide-y divide-[#24403c]">
                        @if(!empty($pca->equipe_elaboracao) && is_array($pca->equipe_elaboracao))
                            @foreach($pca->equipe_elaboracao as $membro)
                                <li class="p-5 hover:bg-[#142825] transition-colors">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mt-1">
                                            <i class="fas fa-user-circle text-[#2DC197] text-2xl"></i>
                                        </div>
                                        <div class="ml-4">
                                            <h4 class="text-sm font-bold text-[#e6f0ee]">{{ $membro['responsavel'] ?? 'N/I' }}</h4>
                                            <p class="text-sm text-[#a2b9b5] mt-0.5">Unidade: {{ \App\Models\Unidade::find($membro['unidade_id'])->nome ?? 'N/I' }}</p>
                                            @if(!empty($membro['numero_portaria']))
                                                <p class="text-xs text-[#527771] mt-1">Portaria nº {{ $membro['numero_portaria'] }}
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
                            <li class="p-6 text-center text-[#527771] text-sm">Nenhum membro informado.</li>
                        @endif
                    </ul>
                </div>
            </div>

        </div>

        <!-- Coluna Direita: DETALHAMENTO DOS ITENS -->
        <div class="lg:col-span-2">
            <div class="card !rounded-2xl overflow-hidden min-h-full shadow-sm">
                <div class="card-h flex justify-between items-center">
                    <h3 class="text-lg font-bold text-[#e6f0ee]">Itens do Plano ({{ $pca->itens->count() }})</h3>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-[#142825] border border-[#24403c] text-[#2DC197]">
                        Total: R$ {{ number_format($pca->itens->sum('valor_estimado'), 2, ',', '.') }}
                    </span>
                </div>
                
                <div class="p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[#24403c]">
                            <thead class="bg-[#0d1b19] border-b border-[#24403c]">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-[#a2b9b5] uppercase tracking-wider text-center">#</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-[#a2b9b5] uppercase tracking-wider">Unid Requisitante</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-[#a2b9b5] uppercase tracking-wider">Modalidade</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-[#a2b9b5] uppercase tracking-wider">Objeto</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-[#a2b9b5] uppercase tracking-wider">Valor Est. (R$)</th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-[#a2b9b5] uppercase tracking-wider">Prorrog?</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#24403c] bg-[#10201e]">
                                @forelse($pca->itens as $index => $item)
                                    <tr class="hover:bg-[#142825] transition-colors">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center font-medium text-[#527771]">{{ $index + 1 }}</td>
                                        <td class="px-4 py-4 text-sm text-[#e6f0ee]">{{ $item->unidade->nome ?? 'N/I' }}</td>
                                        <td class="px-4 py-4 text-sm text-[#a2b9b5]">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-[#142825] border border-[#24403c] text-[#a2b9b5]">
                                                {{ $item->modalidade ?? 'N/I' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-[#e6f0ee]">
                                            <p class="leading-relaxed">{{ $item->descricao_classe_grupo }}</p>
                                            
                                            <div class="mt-2 flex items-center space-x-2">
                                                @switch($item->grau_prioridade)
                                                    @case('alto')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-red-500/10 border border-red-500/20 text-red-400 uppercase">Prioridade Alta</span>
                                                        @break
                                                    @case('medio')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-500/10 border border-amber-500/20 text-amber-400 uppercase">Prioridade Média</span>
                                                        @break
                                                    @case('baixo')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-500/10 border border-blue-500/20 text-blue-400 uppercase">Prioridade Baixa</span>
                                                        @break
                                                @endswitch
                                            </div>

                                            @if($item->data_inicio_providencias || $item->data_desejada_conclusao)
                                                <div class="text-xs text-[#527771] mt-2 flex items-center gap-1">
                                                    <i class="far fa-calendar-alt"></i>
                                                    Prov: {{ $item->data_inicio_providencias ? $item->data_inicio_providencias->format('d/m/Y') : '-' }} | 
                                                    Concl: {{ $item->data_desejada_conclusao ? $item->data_desejada_conclusao->format('d/m/Y') : '-' }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-right font-bold text-[#2DC197]">
                                            {{ number_format($item->valor_estimado, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                                            {!! $item->prorrogacao_contrato ? '<i class="fas fa-check-circle text-[#2DC197] text-lg" title="Sim"></i>' : '<i class="fas fa-times-circle text-red-400 text-lg" title="Não"></i>' !!}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-[#527771]">
                                            <i class="fas fa-box-open text-3xl mb-3 block"></i>
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
