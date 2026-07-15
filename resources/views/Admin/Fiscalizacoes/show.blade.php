@extends('layouts.app')
@section('page-title', 'Detalhes da Fiscalização')
@section('page-subtitle', 'Relatório de Fiscalização Nº ' . ($fiscalizacao->numero_fiscalizacao ?? $fiscalizacao->id))

@section('content')

@php
    $tipo = $fiscalizacao->tipo_contrato?->value;
    $info = $fiscalizacao->contrato_info;

    // Labels dinâmicas por tipo (mantidas da lógica anterior)
    $labelExecucao = match($tipo) {
        'compras' => 'Execução Física do Objeto',
        'servicos' => 'Execução do Objeto',
        'obras' => 'Execução do Objeto',
        default => 'Execução do Objeto',
    };
    $labelQualidade = match($tipo) {
        'compras' => 'Qualidade dos Produtos entregues',
        'servicos' => 'Qualidade dos serviços Prestados',
        'obras' => 'Qualidade dos serviços Executados',
        default => 'Qualidade das Entregas',
    };
@endphp

<div class="py-8">
    {{-- BARRA DE AÇÕES --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Fiscalização {{ $fiscalizacao->numero_fiscalizacao }}</h2>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            
            <a href="{{ route('admin.fiscalizacoes.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0596A2] shadow-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>

            <a href="{{ route('admin.fiscalizacoes.edit', $fiscalizacao->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 transition-colors bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 shadow-sm">
                <i class="fas fa-edit text-amber-500"></i> Editar Dados
            </a>

            <div class="hidden md:block w-px h-8 bg-gray-300 mx-1"></div> {{-- Separador visual --}}

            <a href="{{ route('admin.fiscalizacoes.relatorio-tecnico', $fiscalizacao->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white transition-colors bg-[#009496] rounded-lg hover:bg-[#062F43] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#009496] shadow-sm" target="_blank">
                <i class="fas fa-file-contract"></i> Relatório Técnico
            </a>

            <a href="{{ route('admin.fiscalizacoes.notificacoes', $fiscalizacao->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white transition-colors bg-red-600 rounded-lg hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-sm" target="_blank">
                <i class="fas fa-exclamation-triangle"></i> Notificações e Recomendações
            </a>
            
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-6">

            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Dados do Relatório</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Resultado</dt>
                            <dd class="text-sm text-gray-900 col-span-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $fiscalizacao->conclusao_badge_class }}">
                                    {{ $fiscalizacao->conclusao_texto }}
                                </span>
                            </dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Data</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $fiscalizacao->data_fiscalizacao?->format('d/m/Y') }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Tipo</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $fiscalizacao->tipo_contrato?->getDisplayName() ?? 'N/I' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Fiscal</dt>
                            <dd class="text-sm text-gray-900 col-span-2 font-medium">{{ $fiscalizacao->user->name ?? 'Sistema' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Criado em</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $fiscalizacao->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Contrato Vinculado</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Empresa Contratada</dt>
                            <dd class="text-sm font-bold text-gray-900 mt-1">{{ $info['razao_social'] }}</dd>
                            <dd class="text-xs text-gray-500">{{ $info['cnpj'] }}</dd>
                        </div>
                        <div class="pt-3 border-t border-gray-50">
                            <dt class="text-xs font-medium text-gray-400 uppercase">Nº Contrato / Processo</dt>
                            <dd class="text-sm text-gray-700 mt-1">{{ $info['numero_contrato'] }} ({{ $info['numero_processo'] }})</dd>
                        </div>
                        <div class="pt-3 border-t border-gray-50">
                            <dt class="text-xs font-medium text-gray-400 uppercase">Secretaria</dt>
                            <dd class="text-sm text-gray-700 mt-1">{{ $info['secretaria'] }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if($fiscalizacao->relatorio_fotografico)
                <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-800">Relatório Fotográfico</h3>
                    </div>
                    <div class="p-6">
                        <a href="{{ asset($fiscalizacao->relatorio_fotografico) }}" target="_blank">
                            <img src="{{ asset($fiscalizacao->relatorio_fotografico) }}" alt="Relatório Fotográfico"
                                 class="w-full rounded-xl border border-gray-200 hover:opacity-90 transition-opacity">
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden min-h-full">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Avaliação da Execução</h3>
                </div>

                <div class="p-8 space-y-8">
                    @php
                        $campos = [
                            ['icon' => 'fa-microscope', 'label' => 'Metodologia Aplicada', 'value' => $fiscalizacao->metodologia_fiscalizacao, 'show' => in_array($tipo, ['servicos', 'obras'])],
                            ['icon' => 'fa-box', 'label' => $labelExecucao, 'value' => $fiscalizacao->execucao_objeto, 'show' => true],
                            ['icon' => 'fa-star', 'label' => $labelQualidade, 'value' => $fiscalizacao->qualidade_entregas, 'show' => true],
                            ['icon' => 'fa-clock', 'label' => 'Pontualidade e Prazos', 'value' => $fiscalizacao->pontualidade_prazos, 'show' => true],
                            ['icon' => 'fa-file-invoice-dollar', 'label' => 'Regularidade Fiscal/Trabalhista', 'value' => $fiscalizacao->regularidade_fiscal_trabalhista, 'show' => true],
                            ['icon' => 'fa-comments', 'label' => 'Comunicação e Atendimento', 'value' => $fiscalizacao->comunicacao_atendimento, 'show' => true],
                            ['icon' => 'fa-exclamation-triangle', 'label' => 'Irregularidades Observadas', 'value' => $fiscalizacao->irregularidade_observada, 'show' => true],
                            ['icon' => 'fa-lightbulb', 'label' => 'Recomendações ao Gestor', 'value' => $fiscalizacao->recomendacoes_gestor, 'show' => true],
                            ['icon' => 'fa-building', 'label' => 'Recomendações à Empresa', 'value' => $fiscalizacao->recomendacoes_empresa, 'show' => true],
                        ];
                    @endphp

                    @foreach($campos as $campo)
                        @if($campo['show'] && $campo['value'])
                            <div class="relative pl-8">
                                <div class="absolute left-0 top-1 text-[#0596A2]">
                                    <i class="fas {{ $campo['icon'] }} text-lg"></i>
                                </div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ $campo['label'] }}</h4>
                                <div class="mt-2 text-sm text-gray-700 leading-relaxed bg-gray-50/50 p-4 rounded-xl border border-gray-50">
                                    {!! nl2br(e($campo['value'])) !!}
                                </div>
                            </div>
                        @endif
                    @endforeach

                    {{-- CONCLUSÃO FINAL --}}
                    <div class="mt-12 p-6 rounded-2xl border-2 {{ $fiscalizacao->conclusao_badge_class }} border-current bg-opacity-5">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full bg-white shadow-sm text-xl font-bold">
                                {{ $fiscalizacao->conclusao_fiscal?->value }}
                            </div>
                            <div>
                                <h4 class="text-sm font-bold uppercase">Conclusão Final do Fiscal</h4>
                                <p class="mt-1 text-sm leading-relaxed font-medium">{{ $fiscalizacao->conclusao_texto }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- ASSINANTES DO RELATÓRIO (assinatura física — sem assinatura eletrônica) --}}
    {{-- ============================================================== --}}
    @php
        $unidadesAssinantes = $fiscalizacao->prefeitura?->unidades->map(fn($u) => [
            'id' => $u->id,
            'nome' => $u->nome,
            'servidor_responsavel' => $u->servidor_responsavel,
        ])->values() ?? collect();
    @endphp
    <div class="mt-6 bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden"
         x-data="assinantesFiscalizacao({
            unidades: {{ $unidadesAssinantes->toJson() }},
            iniciais: {{ json_encode($fiscalizacao->assinantes ?? []) }},
            saveUrl: '{{ route('admin.fiscalizacoes.assinantes', $fiscalizacao->id) }}'
         })">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-file-signature text-[#0596A2] mr-2"></i>Assinantes do Relatório
            </h3>
            <span class="text-xs font-medium text-gray-500" x-show="assinantes.length > 0">
                <span x-text="assinantes.length"></span> assinante(s)
            </span>
        </div>

        <div class="p-6 space-y-5">
            <p class="text-sm text-gray-500">
                Selecione os servidores responsáveis que assinarão fisicamente o relatório. Os assinantes aparecem no rodapé do relatório impresso, sem assinatura eletrônica.
            </p>

            {{-- Formulário de adição --}}
            <div class="flex flex-col gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg sm:flex-row sm:items-end">
                <div class="flex-1 min-w-[180px]">
                    <label class="block mb-1 text-xs font-medium text-gray-600">Unidade / Secretaria</label>
                    <select x-model="selUnidadeId" @change="aoSelecionarUnidade()"
                            class="block w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#009496] focus:border-[#009496]">
                        <option value="">Selecione (ou preencha manualmente)</option>
                        <template x-for="u in unidades" :key="u.id">
                            <option :value="u.id" x-text="u.nome"></option>
                        </template>
                    </select>
                </div>
                <div class="flex-1 min-w-[160px]">
                    <label class="block mb-1 text-xs font-medium text-gray-600">Nome do Servidor <span class="text-red-500">*</span></label>
                    <input type="text" x-model="formNome" placeholder="Nome completo"
                           class="block w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:ring-[#009496]">
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="block mb-1 text-xs font-medium text-gray-600">Cargo / Função</label>
                    <input type="text" x-model="formCargo" placeholder="Ex: Fiscal de Contrato"
                           class="block w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:ring-[#009496]">
                </div>
                <div>
                    <button type="button" @click="adicionar()" :disabled="!formNome.trim()"
                            class="flex items-center justify-center w-full px-4 py-2 text-sm font-semibold text-white transition-colors bg-[#009496] rounded-md shadow disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[#007779]">
                        + Adicionar
                    </button>
                </div>
            </div>

            {{-- Lista de selecionados --}}
            <div class="border border-emerald-200 rounded-lg overflow-hidden bg-white">
                <div class="px-4 py-2.5 bg-emerald-50 border-b border-emerald-200">
                    <span class="text-xs font-semibold text-emerald-800 uppercase tracking-wide">Assinantes Selecionados</span>
                </div>
                <template x-if="assinantes.length === 0">
                    <div class="p-6 text-sm text-center text-gray-500">Nenhum assinante adicionado.</div>
                </template>
                <ul class="divide-y divide-emerald-100">
                    <template x-for="(a, idx) in assinantes" :key="idx">
                        <li class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate" x-text="a.nome"></p>
                                <p class="text-xs text-gray-500 truncate"
                                   x-text="[a.cargo, a.unidade].filter(Boolean).join(' — ')"></p>
                            </div>
                            <button type="button" @click="remover(idx)"
                                    class="px-2.5 py-1.5 text-xs font-medium text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md transition-colors">
                                Remover
                            </button>
                        </li>
                    </template>
                </ul>
            </div>

            {{-- Form de salvamento (hidden inputs gerados a partir do estado) --}}
            <form method="POST" :action="saveUrl" @submit="salvando = true">
                @csrf
                <template x-for="(a, idx) in assinantes" :key="'h'+idx">
                    <span>
                        <input type="hidden" :name="`assinantes[${idx}][nome]`" :value="a.nome">
                        <input type="hidden" :name="`assinantes[${idx}][cargo]`" :value="a.cargo">
                        <input type="hidden" :name="`assinantes[${idx}][unidade]`" :value="a.unidade">
                    </span>
                </template>
                <div class="flex justify-end">
                    <button type="submit" :disabled="salvando"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-[#062F43] rounded-lg hover:bg-[#0596A2] transition-colors disabled:opacity-60 shadow-sm">
                        <i class="fas fa-save"></i> Salvar Assinantes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- RELATÓRIO FOTOGRÁFICO (múltiplas imagens) --}}
    {{-- ============================================================== --}}
    <div class="mt-6 bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-camera text-[#0596A2] mr-2"></i>Relatório Fotográfico
            </h3>
            <span class="text-xs font-medium text-gray-500">{{ $fiscalizacao->fotos->count() }} imagem(ns)</span>
        </div>

        <div class="p-6 space-y-6">
            <p class="text-sm text-gray-500">
                Adicione imagens da fiscalização. Todas serão anexadas automaticamente ao final do relatório impresso, na seção “Relatório Fotográfico”.
            </p>

            {{-- Upload --}}
            <form method="POST" action="{{ route('admin.fiscalizacoes.fotos.upload', $fiscalizacao->id) }}"
                  enctype="multipart/form-data"
                  class="flex flex-col gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg sm:flex-row sm:items-center">
                @csrf
                <input type="file" name="fotos[]" multiple accept="image/jpeg,image/png,image/webp" required
                       class="flex-1 text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#009496] file:text-white hover:file:bg-[#007779] cursor-pointer">
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007779] transition-colors shadow-sm">
                    <i class="fas fa-upload"></i> Enviar Fotos
                </button>
            </form>

            {{-- Galeria --}}
            @if($fiscalizacao->fotos->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($fiscalizacao->fotos as $foto)
                        <div class="relative group border border-gray-200 rounded-xl overflow-hidden">
                            <a href="{{ asset($foto->caminho) }}" target="_blank">
                                <img src="{{ asset($foto->caminho) }}" alt="{{ $foto->legenda ?? 'Foto da fiscalização' }}"
                                     class="w-full h-32 object-cover group-hover:opacity-90 transition-opacity">
                            </a>
                            <form method="POST" action="{{ route('admin.fiscalizacoes.fotos.delete', [$fiscalizacao->id, $foto->id]) }}"
                                  onsubmit="return confirm('Remover esta foto?');"
                                  class="absolute top-1.5 right-1.5">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-7 h-7 flex items-center justify-center rounded-full bg-red-600 text-white text-xs shadow hover:bg-red-800 opacity-0 group-hover:opacity-100 transition-opacity"
                                        title="Remover foto">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-sm text-center text-gray-500 border border-dashed border-gray-300 rounded-xl">
                    Nenhuma foto adicionada ainda.
                </div>
            @endif
        </div>
    </div>
</div>

@once
<script>
    function assinantesFiscalizacao({ unidades, iniciais, saveUrl }) {
        return {
            unidades: unidades || [],
            assinantes: (iniciais || []).map(a => ({
                nome: a.nome ?? '',
                cargo: a.cargo ?? '',
                unidade: a.unidade ?? '',
            })),
            saveUrl,
            salvando: false,
            selUnidadeId: '',
            formNome: '',
            formCargo: '',

            aoSelecionarUnidade() {
                if (!this.selUnidadeId) return;
                const u = this.unidades.find(x => x.id == this.selUnidadeId);
                if (u) {
                    this.formNome = u.servidor_responsavel || this.formNome;
                }
            },

            adicionar() {
                if (!this.formNome.trim()) return;
                const u = this.unidades.find(x => x.id == this.selUnidadeId);
                this.assinantes.push({
                    nome: this.formNome.trim(),
                    cargo: this.formCargo.trim(),
                    unidade: u ? u.nome : '',
                });
                this.formNome = '';
                this.formCargo = '';
                this.selUnidadeId = '';
            },

            remover(idx) {
                this.assinantes.splice(idx, 1);
            },
        };
    }
</script>
@endonce
@endsection