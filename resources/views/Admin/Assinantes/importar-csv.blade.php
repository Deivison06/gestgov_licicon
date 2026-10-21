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
            <h3 class="text-lg font-bold text-[var(--ink)] mb-3 flex items-center gap-2">
                <i class="fas fa-file-csv text-[var(--mint)]"></i> Formato esperado do CSV
            </h3>

            <p class="text-sm text-[var(--ink-2)] mb-3">
                Separador: vírgula (<code class="px-1.5 py-0.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--mint)] rounded font-mono">,</code>).
                Encoding: UTF-8. Primeira linha = cabeçalho.
            </p>

            <p class="text-sm text-[var(--ink-2)] mb-2">
                <strong class="text-[var(--ink)]">Cabeçalhos obrigatórios:</strong>
                <code class="px-1.5 py-0.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--mint)] rounded font-mono">nome</code>,
                <code class="px-1.5 py-0.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--mint)] rounded font-mono">email</code>
            </p>
            <p class="text-sm text-[var(--ink-2)] mb-4">
                <strong class="text-[var(--ink)]">Opcionais:</strong>
                <code class="px-1.5 py-0.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--mint)] rounded font-mono">numero_portaria</code>,
                <code class="px-1.5 py-0.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--mint)] rounded font-mono">data_portaria</code>
                (aceita <code class="px-1.5 py-0.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--mint)] rounded font-mono">dd/mm/yyyy</code> ou <code class="px-1.5 py-0.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--mint)] rounded font-mono">yyyy-mm-dd</code>)
            </p>

            <div class="p-4 text-xs font-mono text-[var(--ink)] bg-[var(--bg)] border border-[var(--line)] rounded-xl overflow-x-auto">
nome,email,numero_portaria,data_portaria<br>
João Silva,joao.silva@prefeitura.gov.br,002/2025,02/01/2025<br>
Maria Souza,maria.souza@prefeitura.gov.br,003/2025,15/01/2025
            </div>

            <ul class="mt-4 text-xs text-[var(--ink-2)] list-disc list-inside space-y-1">
                <li>Se o e-mail já existir, o usuário é <strong class="text-[var(--ink)]">atualizado</strong> (não duplica).</li>
                <li>Linhas com nome ou e-mail vazios são <strong class="text-[var(--ink)]">ignoradas</strong> e listadas no relatório de erros.</li>
                <li>A senha de novos usuários é gerada automaticamente — você verá o relatório após importar.</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('admin.assinantes.processar-csv') }}"
              enctype="multipart/form-data"
              class="card !rounded-2xl overflow-hidden p-8 space-y-6">
            @csrf

            <div>
                <label class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">
                    Prefeitura <span class="text-red-400">*</span>
                </label>
                <select name="prefeitura_id" required
                        class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                    <option value="">Selecione...</option>
                    @foreach ($prefeituras as $p)
                        <option value="{{ $p->id }}">{{ $p->nome ?? $p->cidade }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-[var(--ink-2)]">
                    Todos os assinantes do arquivo serão vinculados a esta prefeitura.
                </p>
            </div>

            <div>
                <label class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">
                    Arquivo CSV <span class="text-red-400">*</span>
                </label>
                <input type="file" name="arquivo" required accept=".csv,text/csv"
                       class="block w-full text-sm text-[var(--ink-2)]
                              file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0
                              file:text-sm file:font-bold
                              file:bg-[var(--mint)] file:text-[var(--on-mint)] hover:file:bg-[var(--mint-h)]
                              file:cursor-pointer transition-colors">
                <p class="mt-1 text-xs text-[var(--ink-2)]">Máx 2 MB.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-[var(--line)]">
                <a href="{{ route('admin.assinantes.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-[var(--ink)] bg-[var(--card)] border border-[var(--line)] rounded-xl hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] transition-colors shadow-sm">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-sm hover:scale-105">
                    <i class="fas fa-file-import"></i>
                    Importar
                </button>
            </div>
        </form>
    </div>
@endsection
