@extends('layouts.app')
@section('page-title', 'Fiscalização de Contratos')
@section('page-subtitle', 'Gerencie as fiscalizações realizadas nos contratos')

@section('content')

<div class="overflow-hidden bg-[var(--card)] border border-[var(--line)] shadow-sm rounded-xl">

    {{-- Header com botão de ação --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-[var(--line)] bg-[var(--card-2)]">
        <div>
            <h3 class="text-lg font-semibold text-[var(--ink)]">
                <i class="fas fa-clipboard-check text-[var(--mint)] mr-2"></i>Fiscalização & Ocorrências
            </h3>
        </div>
        @can('fiscalizar contratos')
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.ocorrencias.selecionar-contrato') }}"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-[var(--mint)] border border-[var(--line)] bg-[var(--hover-bg)] transition-colors rounded-lg hover:bg-[var(--card-3)] shadow-sm">
                    <i class="fas fa-plus"></i> Nova Ocorrência
                </a>
                <a href="{{ route('admin.fiscalizacoes.selecionar-contrato') }}"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-[var(--on-mint)] transition-colors bg-[var(--mint)] rounded-lg hover:bg-[var(--mint-h)] shadow-sm">
                    <i class="fas fa-plus"></i> Nova Fiscalização
                </a>
            </div>
        @endcan
    </div>

    {{-- Filtros --}}
    <div class="px-6 py-4 bg-[var(--card-2)] border-b border-[var(--line)]">
        <form method="GET" action="{{ route('admin.fiscalizacoes.index') }}" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            @if($activeTab === 'fiscalizacoes')
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-[var(--ink-2)] mb-1">Pesquisar</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full px-3 py-2 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-lg focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)]"
                           placeholder="Nº fiscalização, objeto...">
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-xs font-medium text-[var(--ink-2)] mb-1">Tipo</label>
                    <select name="tipo_contrato"
                            class="w-full px-3 py-2 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-lg focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)]">
                        <option value="">Todos</option>
                        @foreach($tiposFiscalizacao as $tipo)
                            <option value="{{ $tipo->value }}" {{ request('tipo_contrato') == $tipo->value ? 'selected' : '' }}>
                                {{ $tipo->getDisplayName() }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-[var(--ink-2)] mb-1">Pesquisar</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full px-3 py-2 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-lg focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)]"
                           placeholder="Nº ocorrência, descrição do fato...">
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-xs font-medium text-[var(--ink-2)] mb-1">Status</label>
                    <select name="status"
                            class="w-full px-3 py-2 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-lg focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)]">
                        <option value="">Todos</option>
                        @foreach(\App\Enums\StatusOcorrenciaEnum::cases() as $status)
                            <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                {{ $status->getDisplayName() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[180px]">
                    <label class="block text-xs font-medium text-[var(--ink-2)] mb-1">Situação</label>
                    <select name="situacao"
                            class="w-full px-3 py-2 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-lg focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)]">
                        <option value="">Todas</option>
                        @foreach(\App\Enums\SituacaoOcorrenciaEnum::cases() as $situacao)
                            <option value="{{ $situacao->value }}" {{ request('situacao') == $situacao->value ? 'selected' : '' }}>
                                {{ $situacao->getDisplayName() }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if(!$isPrefeituraUser)
                <div class="min-w-[200px]">
                    <label class="block text-xs font-medium text-[var(--ink-2)] mb-1">Prefeitura</label>
                    <select name="prefeitura_id"
                            class="w-full px-3 py-2 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-lg focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)]">
                        <option value="">Todas</option>
                        @foreach($prefeituras as $pref)
                            <option value="{{ $pref->id }}" {{ request('prefeitura_id') == $pref->id ? 'selected' : '' }}>
                                {{ $pref->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            {{-- Filtro de Unidade --}}
            <div class="min-w-[200px]" id="div_unidade_filtro">
                <label class="block text-xs font-medium text-[var(--ink-2)] mb-1">Secretaria/Unidade</label>
                <select name="unidade_id" id="unidade_id_filtro"
                        class="w-full px-3 py-2 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-lg focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)]">
                    <option value="">Todas</option>
                    @foreach($unidades as $un)
                        <option value="{{ $un->id }}" data-prefeitura="{{ $un->prefeitura_id }}"
                            {{ request('unidade_id') == $un->id ? 'selected' : '' }}>
                            {{ $un->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2 text-sm font-semibold text-[var(--on-mint)] bg-[var(--mint)] rounded-lg hover:bg-[var(--mint-h)] transition-colors">
                    <i class="fas fa-search mr-1"></i> Filtrar
                </button>
                <a href="{{ route('admin.fiscalizacoes.index') }}"
                   class="px-4 py-2 text-sm font-medium text-[var(--ink-2)] bg-[var(--hover-bg)] border border-[var(--line)] rounded-lg hover:bg-[var(--card-3)] hover:text-[var(--ink)] transition-colors">
                    <i class="fas fa-eraser mr-1"></i> Limpar
                </a>
            </div>
        </form>
    </div>

    {{-- Abas do Painel --}}
    <div class="flex border-b border-[var(--line)] bg-[var(--bg)] px-6">
        <a href="{{ route('admin.fiscalizacoes.index', ['tab' => 'fiscalizacoes'] + request()->except(['tab', 'page_fisc', 'page_ocor'])) }}"
           class="py-3 px-4 text-sm font-medium border-b-2 transition-colors flex items-center {{ $activeTab === 'fiscalizacoes' ? 'border-[var(--mint)] text-[var(--mint)] font-semibold' : 'border-transparent text-[var(--ink-4)] hover:text-[var(--ink)]' }}">
            <i class="fas fa-clipboard-check mr-2"></i>Fiscalizações
        </a>
        <a href="{{ route('admin.fiscalizacoes.index', ['tab' => 'ocorrencias'] + request()->except(['tab', 'page_fisc', 'page_ocor'])) }}"
           class="py-3 px-4 text-sm font-medium border-b-2 transition-colors flex items-center {{ $activeTab === 'ocorrencias' ? 'border-[var(--mint)] text-[var(--mint)] font-semibold' : 'border-transparent text-[var(--ink-4)] hover:text-[var(--ink)]' }}">
            <i class="fas fa-triangle-exclamation mr-2"></i>Ocorrências
        </a>
    </div>

    @if($activeTab === 'fiscalizacoes')
        {{-- Tabela de Fiscalizações --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-[var(--bg)] text-[var(--ink-4)] uppercase text-xs border-b border-[var(--line)]">
                    <tr>
                        <th class="px-6 py-3">Nº Fiscalização</th>
                        <th class="px-6 py-3">Data</th>
                        <th class="px-6 py-3">Contrato</th>
                        <th class="px-6 py-3">Tipo</th>
                        <th class="px-6 py-3">Empresa</th>
                        <th class="px-6 py-3">Conclusão</th>
                        <th class="px-6 py-3 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--card-3)] bg-[var(--card)]">
                    @forelse($fiscalizacoes as $fisc)
                        <tr class="hover:bg-[var(--hover-bg)] transition-colors">
                            <td class="px-6 py-4 font-medium text-[var(--ink)]">
                                {{ $fisc->numero_fiscalizacao }}
                            </td>
                            <td class="px-6 py-4 text-[var(--ink-2)]">
                                {{ $fisc->data_fiscalizacao?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-[var(--ink-2)]">
                                {{ $fisc->contrato_info['numero_contrato'] ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                    {{ $fisc->tipo_contrato?->getDisplayName() ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ \Str::limit($fisc->contrato_info['razao_social'] ?? '—', 30) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $fisc->conclusao_badge_class }}">
                                    {{ $fisc->conclusao_fiscal?->getDisplayName() ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.fiscalizacoes.show', $fisc->id) }}"
                                       class="p-2 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.fiscalizacoes.edit', $fisc->id) }}"
                                       class="p-2 text-yellow-600 rounded-lg hover:bg-yellow-50 transition-colors" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.fiscalizacoes.destroy', $fisc->id) }}" method="POST"
                                          class="inline" onsubmit="return confirmarExclusao(event)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2 text-red-600 rounded-lg hover:bg-red-50 transition-colors" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <i class="fas fa-clipboard-check text-4xl mb-3 block"></i>
                                <p class="text-lg font-medium">Nenhuma fiscalização encontrada</p>
                                <p class="mt-1 text-sm">Clique em "Nova Fiscalização" para começar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        @if($fiscalizacoes->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $fiscalizacoes->withQueryString()->links() }}
            </div>
        @endif
    @else
        {{-- Tabela de Ocorrências --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-[var(--bg)] text-[var(--ink-4)] uppercase text-xs border-b border-[var(--line)]">
                    <tr>
                        <th class="px-6 py-3">Nº Ocorrência</th>
                        <th class="px-6 py-3">Data</th>
                        <th class="px-6 py-3">Contrato</th>
                        <th class="px-6 py-3">Empresa</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Situação</th>
                        <th class="px-6 py-3 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--card-3)] bg-[var(--card)]">
                    @forelse($ocorrencias as $ocorrencia)
                        <tr class="hover:bg-[var(--hover-bg)] transition-colors">
                            <td class="px-6 py-4 font-medium text-[var(--ink)]">
                                {{ $ocorrencia->numero_ocorrencia }}
                            </td>
                            <td class="px-6 py-4 text-[var(--ink-2)]">
                                {{ $ocorrencia->data_ocorrencia?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-[var(--ink-2)]">
                                {{ $ocorrencia->contrato_info['numero_contrato'] ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-[var(--ink-2)]">
                                {{ \Str::limit($ocorrencia->contrato_info['razao_social'] ?? '—', 30) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $ocorrencia->status_badge_class }}">
                                    {{ $ocorrencia->status_texto }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($ocorrencia->situacao)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $ocorrencia->situacao_badge_class }}">
                                        {{ $ocorrencia->situacao_texto }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.ocorrencias.show', $ocorrencia->id) }}"
                                       class="p-2 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($ocorrencia->status->value !== 'concluida')
                                        <a href="{{ route('admin.ocorrencias.edit', $ocorrencia->id) }}"
                                           class="p-2 text-yellow-600 rounded-lg hover:bg-yellow-50 transition-colors" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    @if($ocorrencia->status->value === 'rascunho' || auth()->user()->hasAnyRole(['diretor_licicon', 'gerente_licicon']))
                                        <form action="{{ route('admin.ocorrencias.destroy', $ocorrencia->id) }}" method="POST"
                                              class="inline" onsubmit="return confirmarExclusao(event)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 text-red-600 rounded-lg hover:bg-red-50 transition-colors" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <i class="fas fa-triangle-exclamation text-4xl mb-3 block"></i>
                                <p class="text-lg font-medium">Nenhuma ocorrência encontrada</p>
                                <p class="mt-1 text-sm">Clique em "Nova Ocorrência" para começar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        @if($ocorrencias->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $ocorrencias->withQueryString()->links() }}
            </div>
        @endif
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const prefeituraSelect = document.querySelector('select[name="prefeitura_id"]');
    const unidadeSelect = document.getElementById('unidade_id_filtro');

    if (prefeituraSelect && unidadeSelect) {
        const unidadeOptions = Array.from(unidadeSelect.options);

        function filterUnidades() {
            const prefeituraId = prefeituraSelect.value;
            const currentVal = unidadeSelect.value;

            unidadeSelect.innerHTML = '<option value="">Todas</option>';
            unidadeOptions.forEach(opt => {
                if (opt.value === "" || opt.dataset.prefeitura === prefeituraId) {
                    unidadeSelect.appendChild(opt);
                }
            });
            unidadeSelect.value = currentVal;
        }

        prefeituraSelect.addEventListener('change', filterUnidades);
        if (prefeituraSelect.value) filterUnidades();
    }
});

function confirmarExclusao(event) {
    event.preventDefault();
    Swal.fire({
        title: 'Tem certeza?',
        text: 'Esta ação não poderá ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            event.target.closest('form').submit();
        }
    });
    return false;
}
</script>

@endsection
