@extends('layouts.app')
@section('page-title', 'Gestão de Prefeituras')
@section('page-subtitle', 'Gerencie as prefeituras cadastradas')

@section('content')
    <!-- Botão Novo Processo -->
    <div class="flex justify-end mb-8">
        <a href="{{ route('admin.prefeituras.create') }}"
            class="inline-flex items-center gap-3 px-6 py-3 text-sm font-bold text-[var(--on-mint)] transition-all duration-200 bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] hover:shadow-lg hover:scale-105">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
            </svg> Nova Prefeitura </a>
    </div>

    @if (session('success'))
        <div class="p-4 mb-6 rounded-lg bg-[var(--card-3)] border border-[var(--line-3)] text-[var(--mint)]">
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <div class="overflow-hidden bg-[var(--card)] border border-[var(--line)] shadow-sm rounded-xl">
        <div class="px-6 py-4 border-b border-[var(--line)] bg-[var(--card-2)]">
            <h3 class="text-lg font-semibold text-[var(--ink)]">Prefeituras Cadastradas</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[var(--line)]">
                <thead class="bg-[var(--bg)]">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-left text-[var(--ink-4)] uppercase">Nome</th>
                        <th class="px-6 py-3 text-xs font-semibold text-left text-[var(--ink-4)] uppercase">CNPJ</th>
                        <th class="px-6 py-3 text-xs font-semibold text-left text-[var(--ink-4)] uppercase">Email</th>
                        <th class="px-6 py-3 text-xs font-semibold text-left text-[var(--ink-4)] uppercase">Telefone</th>
                        <th class="px-6 py-3 text-xs font-semibold text-center text-[var(--ink-4)] uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-[var(--card)] divide-y divide-[var(--card-3)]">
                    @foreach ($prefeituras as $prefeitura)
                        <tr class="hover:bg-[var(--hover-bg)] transition-colors">
                            <td class="px-6 py-4 text-[var(--ink)] font-medium">{{ $prefeitura->nome }}</td>
                            <td class="px-6 py-4 text-[var(--ink-2)]" data-type="cnpj">{{ $prefeitura->cnpj }}</td>
                            <td class="px-6 py-4 text-[var(--ink-2)]">{{ $prefeitura->email }}</td>
                            <td class="px-6 py-4 text-[var(--ink-2)]" data-type="telefone">{{ $prefeitura->telefone }}</td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.prefeituras.edit', $prefeitura->id) }}"
                                    class="text-amber-400 hover:text-amber-300 hover:underline mr-3">Editar</a>
                                <form action="{{ route('admin.prefeituras.destroy', $prefeitura->id) }}" method="POST"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Excluir prefeitura?')"
                                        class="text-red-400 hover:text-red-300 hover:underline">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Função para aplicar máscara CNPJ
                function formatCNPJ(value) {
                    return value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
                }

                // Função para aplicar máscara Telefone
                function formatTelefone(value) {
                    return value.replace(/^(\d{2})(\d{4,5})(\d{4})$/, "($1) $2-$3");
                }

                // CNPJs
                document.querySelectorAll("td[data-type='cnpj']").forEach(function(el) {
                    let onlyNumbers = el.innerText.replace(/\D/g, '');
                    if (onlyNumbers.length === 14) {
                        el.innerText = formatCNPJ(onlyNumbers);
                    }
                });

                // Telefones
                document.querySelectorAll("td[data-type='telefone']").forEach(function(el) {
                    let onlyNumbers = el.innerText.replace(/\D/g, '');
                    if (onlyNumbers.length >= 10) {
                        el.innerText = formatTelefone(onlyNumbers);
                    }
                });
            });
        </script>
    @endpush
@endsection
