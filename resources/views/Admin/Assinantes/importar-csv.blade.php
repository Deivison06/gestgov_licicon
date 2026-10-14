@extends('layouts.app')

@section('page-title', 'Importar Assinantes via CSV')
@section('page-subtitle', 'Cadastro em lote a partir de planilha')

@section('content')
    <div class="max-w-3xl mx-auto py-6">

        @if ($errors->any())
            <div class="p-4 mb-6 rounded-xl bg-red-950/60 border border-red-500/30">
                <p class="font-medium text-red-300 mb-2">Corrija os erros abaixo:</p>
                <ul class="text-sm text-red-400 list-disc list-inside">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 mb-6 rounded-xl bg-red-950/60 border border-red-500/30">
                <p class="text-sm font-medium text-red-300">{{ session('error') }}</p>
            </div>
        @endif

        <div class="card !rounded-2xl overflow-hidden p-8 mb-6">
            <h3 class="text-lg font-bold text-[#e6f0ee] mb-3 flex items-center gap-2">
                <i class="fas fa-file-csv text-[#2DC197]"></i> Formato esperado do CSV
            </h3>

            <p class="text-sm text-[#a2b9b5] mb-3">
                Separador: vírgula (<code class="px-1.5 py-0.5 bg-[#0a1514] border border-[#24403c] text-[#2DC197] rounded font-mono">,</code>).
                Encoding: UTF-8. Primeira linha = cabeçalho.
            </p>

            <p class="text-sm text-[#a2b9b5] mb-2">
                <strong class="text-[#e6f0ee]">Cabeçalhos obrigatórios:</strong>
                <code class="px-1.5 py-0.5 bg-[#0a1514] border border-[#24403c] text-[#2DC197] rounded font-mono">nome</code>,
                <code class="px-1.5 py-0.5 bg-[#0a1514] border border-[#24403c] text-[#2DC197] rounded font-mono">email</code>
            </p>
            <p class="text-sm text-[#a2b9b5] mb-4">
                <strong class="text-[#e6f0ee]">Opcionais:</strong>
                <code class="px-1.5 py-0.5 bg-[#0a1514] border border-[#24403c] text-[#2DC197] rounded font-mono">numero_portaria</code>,
                <code class="px-1.5 py-0.5 bg-[#0a1514] border border-[#24403c] text-[#2DC197] rounded font-mono">data_portaria</code>
                (aceita <code class="px-1.5 py-0.5 bg-[#0a1514] border border-[#24403c] text-[#2DC197] rounded font-mono">dd/mm/yyyy</code> ou <code class="px-1.5 py-0.5 bg-[#0a1514] border border-[#24403c] text-[#2DC197] rounded font-mono">yyyy-mm-dd</code>)
            </p>

            <div class="p-4 text-xs font-mono text-[#e6f0ee] bg-[#0a1514] border border-[#24403c] rounded-xl overflow-x-auto">
nome,email,numero_portaria,data_portaria<br>
João Silva,joao.silva@prefeitura.gov.br,002/2025,02/01/2025<br>
Maria Souza,maria.souza@prefeitura.gov.br,003/2025,15/01/2025
            </div>

            <ul class="mt-4 text-xs text-[#a2b9b5] list-disc list-inside space-y-1">
                <li>Se o e-mail já existir, o usuário é <strong class="text-[#e6f0ee]">atualizado</strong> (não duplica).</li>
                <li>Linhas com nome ou e-mail vazios são <strong class="text-[#e6f0ee]">ignoradas</strong> e listadas no relatório de erros.</li>
                <li>A senha de novos usuários é gerada automaticamente — você verá o relatório após importar.</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('admin.assinantes.processar-csv') }}"
              enctype="multipart/form-data"
              class="card !rounded-2xl overflow-hidden p-8 space-y-6">
            @csrf

            <div>
                <label class="block mb-1.5 text-sm font-semibold text-[#a2b9b5]">
                    Prefeitura <span class="text-red-400">*</span>
                </label>
                <select name="prefeitura_id" required
                        class="block w-full px-4 py-2.5 text-sm bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] transition-colors">
                    <option value="">Selecione...</option>
                    @foreach ($prefeituras as $p)
                        <option value="{{ $p->id }}">{{ $p->nome ?? $p->cidade }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-[#a2b9b5]">
                    Todos os assinantes do arquivo serão vinculados a esta prefeitura.
                </p>
            </div>

            <div>
                <label class="block mb-1.5 text-sm font-semibold text-[#a2b9b5]">
                    Arquivo CSV <span class="text-red-400">*</span>
                </label>
                <input type="file" name="arquivo" required accept=".csv,text/csv"
                       class="block w-full text-sm text-[#a2b9b5]
                              file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0
                              file:text-sm file:font-bold
                              file:bg-[#2DC197] file:text-[#041d1a] hover:file:bg-[#26aa84]
                              file:cursor-pointer transition-colors">
                <p class="mt-1 text-xs text-[#a2b9b5]">Máx 2 MB.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-[#24403c]">
                <a href="{{ route('admin.assinantes.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-[#e6f0ee] bg-[#10201e] border border-[#24403c] rounded-xl hover:bg-[#142825] hover:text-[#2DC197] transition-colors shadow-sm">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-[#041d1a] bg-[#2DC197] rounded-xl hover:bg-[#26aa84] transition-all shadow-sm hover:scale-105">
                    <i class="fas fa-file-import"></i>
                    Importar
                </button>
            </div>
        </form>
    </div>
@endsection
