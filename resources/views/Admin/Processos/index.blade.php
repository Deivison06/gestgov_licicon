@extends('layouts.app')
@section('page-title', 'Gestão de Processos')
@section('page-subtitle', 'Gerencie todos os processos licitatórios de forma centralizada')

@section('content')
<div class="py-8">
    <div class="px-4 mx-auto sm:px-6 lg:px-8">

        <!-- Botão Novo Processo -->
        <div class="flex justify-end mb-8">
            <a href="{{ route('admin.processos.create') }}"
                class="inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-[#052323] to-[#052323] rounded-xl hover:shadow-lg hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg> Novo Processo
            </a>
        </div>

        <!-- Mensagem de Sucesso -->
        @if (session('success'))
        <div class="p-4 mb-8 border border-green-200 shadow-sm rounded-2xl bg-gradient-to-r from-green-50 to-emerald-50">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 13l4 4L19 7"></path>
                </svg>
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        <!-- Botão Voltar -->
        @if(request('prefeitura_id'))
        <div class="mb-4">
            <a href="{{ route('admin.processos.index') }}"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all duration-200">
                ← Voltar para todas as prefeituras
            </a>
        </div>
        @endif

        <!-- Prefeituras Cards -->
        @if(!request('prefeitura_id'))
        <div class="mb-8">
            <h2 class="mb-4 text-xl font-semibold text-gray-800">Selecione uma Prefeitura</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($prefeituras as $prefeitura)
                <a href="{{ route('admin.processos.index', ['prefeitura_id' => $prefeitura->id]) }}"
                   class="prefeitura-card group relative p-6 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg hover:border-[#009496] transition-all duration-300 transform hover:-translate-y-1 cursor-pointer block">
                    <div class="absolute transition-opacity duration-300 opacity-0 top-4 right-4 group-hover:opacity-100">
                        <svg class="w-5 h-5 text-[#009496]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                    <div class="flex items-center mb-3">
                        <div class="p-2 rounded-lg bg-[#009496]/10">
                            <svg class="w-6 h-6 text-[#009496]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-6 0H5m2 0h4M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-base font-semibold text-gray-800 group-hover:text-[#009496] transition-colors duration-300">
                        {{ $prefeitura->nome }}
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">{{ $prefeitura->email }}</p>
                    <div class="pt-3 mt-3 border-t border-gray-100">
                        <span class="text-xs font-medium text-[#009496] bg-[#009496]/10 px-2 py-1 rounded-full">
                            {{ $prefeitura->processos_count }} processos
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    @endif

        <!-- Tabela de Processos (mostrada apenas quando há filtro de prefeitura) -->
        @if(request('prefeitura_id'))
        <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-100 shadow-sm rounded-2xl">
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                <div class="flex flex-col items-start justify-between lg:flex-row lg:items-center">
                    <h3 class="text-xl font-semibold text-gray-800">
                        Processos da Prefeitura: {{ $prefeituras->find(request('prefeitura_id'))->nome ?? 'Selecionada' }}
                    </h3>
                    <span class="mt-2 text-sm text-gray-500 lg:mt-0">
                        Total: {{ $processos->total() }} processos
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full overflow-hidden divide-y divide-gray-200 rounded-lg shadow-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Nº Processo</th>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Nº Procedimento</th>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Tipo Contratação</th>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Tipo Procedimento</th>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Modalidade</th>
                            <th class="px-4 py-3 text-xs font-semibold tracking-wider text-center text-gray-600 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($processos as $processo)
                        <tr class="transition-colors duration-200 hover:bg-gray-50/80">
                            <td class="px-4 py-3 font-mono text-sm text-gray-900 whitespace-nowrap">{{ $processo->numero_processo }}</td>
                            <td class="px-4 py-3 font-mono text-sm text-gray-900 whitespace-nowrap">{{ $processo->numero_procedimento }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $processo->tipo_contratacao_nome }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $processo->tipo_procedimento_nome }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full
                                    @if ($processo->modalidade->value === 'dispensa') bg-purple-100 text-purple-800 border border-purple-200
                                    @elseif($processo->modalidade->value === 'inexigibilidade') bg-pink-100 text-pink-800 border border-pink-200
                                    @elseif($processo->modalidade->value === 'pregão') bg-blue-100 text-blue-800 border border-blue-200
                                    @elseif($processo->modalidade->value === 'concorrência') bg-green-100 text-green-800 border border-green-200
                                    @else bg-gray-100 text-gray-800 border border-gray-200 @endif">
                                    {{ $processo->modalidade->getDisplayName() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center space-x-1.5">
                                    <a href="{{ route('admin.processos.iniciar', $processo->id) }}"
                                        class="px-3 py-1.5 text-xs font-medium text-white transition-colors duration-200 bg-[#062F43] rounded-md hover:bg-[#065f8b] focus:outline-none focus:ring-2 focus:ring-[#062F43] focus:ring-offset-1"
                                        title="Iniciar processo">
                                        Iniciar
                                    </a>

                                    <a href="{{ route('admin.processos.finalizacao.finalizar', $processo->id) }}"
                                        class="px-3 py-1.5 text-xs font-medium text-white transition-colors duration-200 bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-1"
                                        title="Finalizar processo">
                                        Finalizar
                                    </a>
                                    @if ($processo->detalhe->tipo_srp === 'nao')
                                        <a href="{{ route('admin.processos.contrato.index', $processo->id) }}"
                                        class="px-3 py-1.5 text-xs font-medium text-white transition-colors duration-200 bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-1"
                                        title="Emitir Contrato">
                                        Contrato
                                    </a>
                                    @endif
                                   

                                    <a href="{{ route('admin.processos.edit', $processo->id) }}"
                                        class="p-1.5 text-gray-600 transition-colors duration-200 rounded-md hover:bg-gray-100 hover:text-[#009496] focus:outline-none focus:ring-2 focus:ring-[#009496] focus:ring-offset-1"
                                        title="Editar processo">✏️</a>

                                    <form action="{{ route('admin.processos.destroy', $processo->id) }}" method="POST"
                                        class="inline" id="delete-form-{{ $processo->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="button"
                                            onclick="confirmDelete({{ $processo->id }}, '{{ $processo->numero_processo }}')"
                                            class="p-1.5 text-gray-600 transition-colors duration-200 rounded-md hover:bg-red-100 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-1"
                                            title="Excluir processo">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr class="bg-gray-50/50">
                            <td colspan="6" class="px-4 py-3 text-sm text-gray-700">
                                <div class="space-y-1">
                                    <div><strong class="text-gray-900">Objeto:</strong> {!! strip_tags($processo->objeto) !!}</div>
                                    <div class="text-xs text-gray-500">Criado Por: {{ $processo->user->name ?? 'N/A' }}</div>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-sm font-medium">Nenhum processo encontrado para esta prefeitura</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($processos->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $processos->withQueryString()->links() }}
            </div>
            @endif
        </div>
        @endif

    </div>
</div>

<script>
function confirmDelete(processoId, numeroProcesso) {
    Swal.fire({
        title: 'Tem certeza?',
        html: `Você está prestes a excluir o processo <strong>${numeroProcesso}</strong>. <br>Esta ação não pode ser desfeita!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        customClass: {
            confirmButton: 'px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500',
            cancelButton: 'px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 mr-3'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Envia o formulário de exclusão
            document.getElementById(`delete-form-${processoId}`).submit();
        }
    });
}
</script>

<!-- Incluir SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.swal2-popup {
    border-radius: 16px !important;
}
</style>
@endsection
