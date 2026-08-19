@extends('layouts.app')
@section('page-title', 'Ocorrências Contratuais')
@section('page-subtitle', 'Registro de ocorrências, notificações e correções')

@section('content')

<div class="overflow-hidden bg-white shadow-sm rounded-xl">

    {{-- Header com botão de ação --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <div>
            <h3 class="text-lg font-semibold text-gray-700">
                <i class="fas fa-triangle-exclamation text-[#009496] mr-2"></i>Ocorrências Registradas
            </h3>
        </div>
        @can('fiscalizar contratos')
            <a href="{{ route('admin.ocorrencias.selecionar-contrato') }}"
                class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white transition-colors bg-[#009496] rounded-lg hover:bg-[#244853] shadow-sm">
                <i class="fas fa-plus"></i> Registrar Ocorrência
            </a>
        @endcan
    </div>

    {{-- Filtros --}}
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
        <form method="GET" action="{{ route('admin.ocorrencias.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Pesquisar</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496]"
                       placeholder="Nº ocorrência, descrição do fato...">
            </div>
            <div class="min-w-[160px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496]">
                    <option value="">Todos</option>
                    @foreach(\App\Enums\StatusOcorrenciaEnum::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                            {{ $status->getDisplayName() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[180px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Situação</label>
                <select name="situacao"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496]">
                    <option value="">Todas</option>
                    @foreach(\App\Enums\SituacaoOcorrenciaEnum::cases() as $situacao)
                        <option value="{{ $situacao->value }}" {{ request('situacao') == $situacao->value ? 'selected' : '' }}>
                            {{ $situacao->getDisplayName() }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(!$isPrefeituraUser)
                <div class="min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Prefeitura</label>
                    <select name="prefeitura_id"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496]">
                        <option value="">Todas</option>
                        @foreach($prefeituras as $pref)
                            <option value="{{ $pref->id }}" {{ request('prefeitura_id') == $pref->id ? 'selected' : '' }}>
                                {{ $pref->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="min-w-[200px]" id="div_unidade_filtro">
                <label class="block text-xs font-medium text-gray-500 mb-1">Secretaria/Unidade</label>
                <select name="unidade_id" id="unidade_id_filtro"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496]">
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
                        class="px-4 py-2 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#244853] transition-colors">
                    <i class="fas fa-search mr-1"></i> Filtrar
                </button>
                <a href="{{ route('admin.ocorrencias.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-eraser mr-1"></i> Limpar
                </a>
            </div>
        </form>
    </div>

    {{-- Tabela --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
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
            <tbody class="divide-y divide-gray-100">
                @forelse($ocorrencias as $ocorrencia)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $ocorrencia->numero_ocorrencia }}
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $ocorrencia->data_ocorrencia?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $ocorrencia->contrato_info['numero_contrato'] ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-gray-600">
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
                        <td class="px-6 py-4">
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
                                @if($ocorrencia->status->value === 'rascunho')
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
                            <p class="mt-1 text-sm">Clique em "Registrar Ocorrência" para começar.</p>
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
