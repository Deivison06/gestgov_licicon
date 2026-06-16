@extends('layouts.app')

@section('page-title', 'Importar Assinantes via CSV')
@section('page-subtitle', 'Cadastro em lote a partir de planilha')

@section('content')
    <div class="max-w-3xl mx-auto">

        @if ($errors->any())
            <div class="p-4 mb-6 rounded-lg bg-red-50 border border-red-200">
                <ul class="text-sm text-red-700 list-disc list-inside">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 mb-6 rounded-lg bg-red-50 border border-red-200">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        <div class="p-8 mb-6 bg-white rounded-xl shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Formato esperado do CSV</h3>

            <p class="text-sm text-gray-600 mb-3">
                Separador: vírgula (<code class="px-1 bg-gray-100 rounded">,</code>).
                Encoding: UTF-8. Primeira linha = cabeçalho.
            </p>

            <p class="text-sm text-gray-600 mb-2">
                <strong>Cabeçalhos obrigatórios:</strong>
                <code class="px-1 bg-gray-100 rounded">nome</code>,
                <code class="px-1 bg-gray-100 rounded">email</code>
            </p>
            <p class="text-sm text-gray-600 mb-4">
                <strong>Opcionais:</strong>
                <code class="px-1 bg-gray-100 rounded">numero_portaria</code>,
                <code class="px-1 bg-gray-100 rounded">data_portaria</code>
                (aceita <code class="px-1 bg-gray-100 rounded">dd/mm/yyyy</code> ou <code class="px-1 bg-gray-100 rounded">yyyy-mm-dd</code>)
            </p>

            <div class="p-4 text-xs font-mono text-gray-700 bg-gray-50 border border-gray-200 rounded">
nome,email,numero_portaria,data_portaria<br>
João Silva,joao.silva@prefeitura.gov.br,002/2025,02/01/2025<br>
Maria Souza,maria.souza@prefeitura.gov.br,003/2025,15/01/2025
            </div>

            <ul class="mt-4 text-xs text-gray-500 list-disc list-inside space-y-1">
                <li>Se o e-mail já existir, o usuário é <strong>atualizado</strong> (não duplica).</li>
                <li>Linhas com nome ou e-mail vazios são <strong>ignoradas</strong> e listadas no relatório de erros.</li>
                <li>A senha de novos usuários é gerada automaticamente — você verá o relatório após importar.</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('admin.assinantes.processar-csv') }}"
              enctype="multipart/form-data"
              class="p-8 bg-white rounded-xl shadow-sm space-y-5">
            @csrf

            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">
                    Prefeitura <span class="text-red-500">*</span>
                </label>
                <select name="prefeitura_id" required
                        class="block w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496]">
                    <option value="">Selecione...</option>
                    @foreach ($prefeituras as $p)
                        <option value="{{ $p->id }}">{{ $p->nome ?? $p->cidade }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    Todos os assinantes do arquivo serão vinculados a esta prefeitura.
                </p>
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">
                    Arquivo CSV <span class="text-red-500">*</span>
                </label>
                <input type="file" name="arquivo" required accept=".csv,text/csv"
                       class="block w-full text-sm text-gray-500
                              file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                              file:text-sm file:font-semibold
                              file:bg-[#009496] file:text-white hover:file:bg-[#007779]">
                <p class="mt-1 text-xs text-gray-500">Máx 2 MB.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.assinantes.index') }}"
                   class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-6 py-2 text-sm font-semibold text-white bg-[#009496] rounded-md hover:bg-[#007779]">
                    Importar
                </button>
            </div>
        </form>
    </div>
@endsection
