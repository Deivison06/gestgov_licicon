@extends('layouts.app')
@section('page-title', 'Gestão de Prefeituras')
@section('page-subtitle', 'Gerencie as prefeituras cadastradas')

@section('content')
    <!-- Botão Novo Processo -->
    <div class="flex justify-end mb-8">
        <a href="{{ route('admin.prefeituras.create') }}"
            class="inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-[#052323] to-[#052323] rounded-xl hover:shadow-lg hover:scale-105">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
            </svg> Nova Prefeitura </a>
    </div>

    @if (session('success'))
        <div class="p-4 mb-6 rounded-lg bg-green-50">
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <div class="overflow-hidden bg-white shadow-sm rounded-xl">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-700">Prefeituras Cadastradas</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Nome</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">CNPJ</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Telefone</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($prefeituras as $prefeitura)
                        <tr>
                            <td class="px-6 py-4">{{ $prefeitura->nome }}</td>
                            <td class="px-6 py-4" data-type="cnpj">{{ $prefeitura->cnpj }}</td>
                            <td class="px-6 py-4">{{ $prefeitura->email }}</td>
                            <td class="px-6 py-4" data-type="telefone">{{ $prefeitura->telefone }}</td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.prefeituras.edit', $prefeitura->id) }}"
                                    class="text-yellow-600 hover:underline">Editar</a>
                                <form action="{{ route('admin.prefeituras.destroy', $prefeitura->id) }}" method="POST"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Excluir prefeitura?')"
                                        class="text-red-600 hover:underline">Excluir</button>
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
