@extends('layouts.app')
@section('page-title', 'Preços do Painel TCE')
@section('page-subtitle', 'Informe até 3 valores TCE por item para o relatório de Análise de Mercado')

@section('content')
<div class="px-6 pb-10">

    {{-- CABEÇALHO --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.processos.iniciar', $processo->id) }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Voltar ao Processo
            </a>
            <div class="text-gray-300">›</div>
            <span class="text-sm font-semibold text-gray-700">Preços do Painel TCE</span>
        </div>
    </div>

    {{-- CARD DO PROCESSO --}}
    <div class="mb-6 p-4 bg-white border border-gray-200 rounded-xl shadow-sm flex items-center gap-5">
        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
            <i class="fas fa-balance-scale text-lg"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Processo</p>
            <p class="text-sm font-bold text-gray-900 truncate">{{ $processo->numero_processo }}</p>
            <p class="text-xs text-gray-500 truncate">{!! strip_tags($processo->objeto) !!}</p>
        </div>
        <div class="text-right text-xs text-gray-500">
            <span class="font-bold">{{ $itens->count() }}</span> {{ Str::plural('item', $itens->count()) }} no ETP
        </div>
    </div>

    {{-- INSTRUÇÃO + IMPORTAÇÃO --}}
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex-1 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800">
            <i class="fas fa-info-circle mr-1"></i>
            Informe até 3 valores por item a partir do Painel de Preços do TCE. Campos não preenchidos serão exibidos como <strong>—</strong> no relatório.
            Com pelo menos 1 valor, a média é calculada automaticamente.
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <button type="button" onclick="tceBaixarModelo()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-all">
                <i class="fas fa-file-excel"></i>
                Baixar Modelo
            </button>
            <label class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-all cursor-pointer">
                <i class="fas fa-file-upload"></i>
                Importar Planilha
                <input type="file" id="inputImportarTce" accept=".xlsx,.xls,.csv" class="hidden" onchange="tceImportar(this)">
            </label>
        </div>
    </div>

    @if($itens->isEmpty())
        <div class="py-16 flex flex-col items-center justify-center text-gray-400 bg-white rounded-xl border border-gray-200">
            <i class="fas fa-box-open text-4xl mb-3 opacity-30"></i>
            <p class="text-sm font-medium">Nenhum item encontrado para este processo.</p>
            <p class="text-xs mt-1">Vincule um ETP ao processo para habilitar esta funcionalidade.</p>
        </div>
    @else

    {{-- FORMULÁRIO --}}
    <div id="app-tce" x-data="tceApp()">
        <form @submit.prevent="salvar" x-ref="formTce" class="space-y-4">

            @foreach($itens as $idx => $item)
            @php
                $salvo = collect($precosSalvos)->first(fn($p) => ($p['item'] ?? '') === $item['descricao'])
                      ?? (isset($precosSalvos[$idx]) ? $precosSalvos[$idx] : null);
            @endphp
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

                {{-- Cabeçalho do item --}}
                <div class="px-5 py-3 bg-blue-50 border-b border-blue-100 flex items-center gap-3">
                    <span class="flex-shrink-0 inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-200 text-blue-800 text-xs font-bold">{{ $idx + 1 }}</span>
                    <div>
                        <span class="text-sm font-semibold text-gray-800">{{ $item['descricao'] }}</span>
                        @if($item['unidade'])
                            <span class="ml-2 text-xs text-gray-400">({{ $item['unidade'] }})</span>
                        @endif
                    </div>
                </div>

                {{-- Campos dos valores TCE --}}
                <div class="p-5">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        @foreach([1, 2, 3] as $n)
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-100 text-gray-600 text-[9px] font-bold mr-1">{{ $n }}</span>
                                Valor TCE {{ $n }} (R$)
                            </label>
                            <input type="number" step="0.0001" min="0"
                                   name="itens[{{ $idx }}][valor_tce_{{ $n }}]"
                                   value="{{ $salvo && isset($salvo['valor_tce_'.$n]) && $salvo['valor_tce_'.$n] !== '' ? str_replace(',', '.', str_replace('.', '', $salvo['valor_tce_'.$n])) : '' }}"
                                   placeholder="0,00"
                                   class="w-full border border-gray-200 rounded-lg text-xs px-3 py-2 focus:ring-1 focus:ring-blue-400 focus:border-blue-400">
                        </div>
                        @endforeach
                    </div>

                    <input type="hidden" name="itens[{{ $idx }}][etp_item_id]" value="{{ $item['id'] ?? '' }}">
                    <input type="hidden" name="itens[{{ $idx }}][descricao]" value="{{ $item['descricao'] }}">
                </div>
            </div>
            @endforeach

            {{-- Botão salvar --}}
            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('admin.processos.iniciar', $processo->id) }}"
                   class="px-5 py-2.5 text-sm font-semibold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                    Cancelar
                </a>
                <button type="submit"
                        :disabled="salvando"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-all shadow-sm disabled:opacity-60">
                    <svg x-show="!salvando" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg x-show="salvando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span x-text="salvando ? 'Salvando...' : 'Salvar Preços TCE'"></span>
                </button>
            </div>

        </form>

        {{-- Mensagem de feedback --}}
        <div x-show="mensagem" x-transition
             :class="sucesso ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'"
             class="fixed bottom-6 right-6 px-5 py-3 border rounded-xl shadow-lg text-sm font-semibold max-w-xs z-50" x-cloak>
            <span x-text="mensagem"></span>
        </div>
    </div>

    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script>
const _tceItens = @json($itens->values());

function tceNormalizar(str) {
    return (str ?? '').toString().toLowerCase().trim().replace(/\s+/g, ' ');
}

function tceBaixarModelo() {
    const cabecalho = ['Descrição do Item', 'Valor TCE 1 (R$)', 'Valor TCE 2 (R$)', 'Valor TCE 3 (R$)'];
    const linhas = _tceItens.map(item => [item.descricao ?? '', '', '', '']);

    const ws = XLSX.utils.aoa_to_sheet([cabecalho, ...linhas]);
    ws['!cols'] = [{ wch: 50 }, { wch: 18 }, { wch: 18 }, { wch: 18 }];

    const range = XLSX.utils.decode_range(ws['!ref']);
    for (let C = range.s.c; C <= range.e.c; C++) {
        const addr = XLSX.utils.encode_cell({ r: 0, c: C });
        if (!ws[addr]) continue;
        ws[addr].s = { font: { bold: true }, fill: { fgColor: { rgb: 'DBEAFE' } } };
    }

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Preços TCE');
    XLSX.writeFile(wb, 'modelo_precos_tce.xlsx');
}

function tceImportar(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    input.value = '';

    const reader = new FileReader();
    reader.onload = function (e) {
        try {
            const data = new Uint8Array(e.target.result);
            const wb = XLSX.read(data, { type: 'array' });
            const ws = wb.Sheets[wb.SheetNames[0]];
            const rows = XLSX.utils.sheet_to_json(ws, { header: 1, defval: '' });

            if (rows.length < 2) {
                alert('Planilha vazia ou sem dados. Verifique o arquivo.');
                return;
            }

            const dataRows = rows.slice(1).filter(r => r.some(c => c !== ''));

            const mapaDescricao = {};
            _tceItens.forEach((item, idx) => {
                mapaDescricao[tceNormalizar(item.descricao)] = idx;
            });

            let importados = 0;
            let naoEncontrados = 0;

            dataRows.forEach((row, posicao) => {
                const descricaoPlanilha = tceNormalizar(row[0]);

                let idx = mapaDescricao[descricaoPlanilha];
                if (idx === undefined && posicao < _tceItens.length) {
                    idx = posicao;
                }
                if (idx === undefined) {
                    naoEncontrados++;
                    return;
                }

                const setValor = (campo, valor) => {
                    const el = document.querySelector(`[name="itens[${idx}][${campo}]"]`);
                    if (el) el.value = valor ?? '';
                };

                setValor('valor_tce_1', row[1]);
                setValor('valor_tce_2', row[2]);
                setValor('valor_tce_3', row[3]);

                importados++;
            });

            const msg = naoEncontrados > 0
                ? `${importados} item(s) preenchido(s). ${naoEncontrados} linha(s) não encontrada(s) no processo.`
                : `${importados} item(s) preenchido(s) com sucesso!`;

            try {
                const comp = Alpine.$data(document.querySelector('#app-tce'));
                comp.sucesso  = naoEncontrados === 0;
                comp.mensagem = msg;
                setTimeout(() => comp.mensagem = '', 6000);
            } catch {
                alert(msg);
            }

        } catch (err) {
            console.error('[TCE] importar():', err);
            alert('Erro ao ler o arquivo. Certifique-se de usar o modelo gerado pelo sistema.');
        }
    };
    reader.readAsArrayBuffer(file);
}

function tceApp() {
    return {
        salvando: false,
        mensagem: '',
        sucesso: true,

        async salvar() {
            this.salvando = true;
            this.mensagem = '';

            try {
                const form = this.$refs.formTce;
                if (!form) throw new Error('Formulário não encontrado.');

                const fd = new FormData(form);
                const itensMap = {};
                for (const [key, val] of fd.entries()) {
                    const m = key.match(/^itens\[(\d+)\]\[(.+)\]$/);
                    if (!m) continue;
                    const [, idx, campo] = m;
                    if (!itensMap[idx]) itensMap[idx] = {};
                    itensMap[idx][campo] = val;
                }

                const itens = Object.values(itensMap);
                if (itens.length === 0) throw new Error('Nenhum item encontrado no formulário.');

                const res = await fetch('{{ route('admin.processos.pesquisa_preco_tce.store', $processo->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ itens })
                });

                const json = await res.json();

                if (res.ok && json.success) {
                    this.sucesso  = true;
                    this.mensagem = 'Preços TCE salvos com sucesso!';
                } else {
                    this.sucesso  = false;
                    this.mensagem = 'Erro: ' + (json.message ?? 'Tente novamente.');
                }
            } catch (e) {
                this.sucesso  = false;
                this.mensagem = e.message.includes('item') || e.message.includes('Formulário')
                    ? e.message
                    : 'Erro de conexão. Tente novamente.';
                console.error('[TCE] salvar():', e);
            } finally {
                this.salvando = false;
                setTimeout(() => this.mensagem = '', 5000);
            }
        }
    }
}
</script>
@endsection
