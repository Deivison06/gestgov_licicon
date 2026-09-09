@extends('layouts.app')
@section('page-title', 'Cota Reservada ME/EPP')
@section('page-subtitle', 'Divisão de lotes em Ampla Concorrência / Cota Reservada para ME e EPP')

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
            <span class="text-sm font-semibold text-gray-700">Cota Reservada ME/EPP</span>
        </div>
    </div>

    {{-- CARD DO PROCESSO --}}
    <div class="mb-6 p-4 bg-white border border-gray-200 rounded-xl shadow-sm flex items-center gap-5">
        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-[#009496]/10 text-[#009496] flex items-center justify-center">
            <i class="fas fa-balance-scale-right text-lg"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Processo</p>
            <p class="text-sm font-bold text-gray-900 truncate">{{ $processo->numero_processo }}</p>
            <p class="text-xs text-gray-500 truncate">{!! strip_tags($processo->objeto) !!}</p>
        </div>
        <div class="text-right text-xs text-gray-500">
            <span class="font-bold">{{ $lotes->count() }}</span> {{ Str::plural('lote', $lotes->count()) }} no ETP
        </div>
    </div>

    {{-- INSTRUÇÃO --}}
    <div class="mb-4 p-3 bg-[#009496]/5 border border-[#009496]/20 rounded-lg text-xs text-[#007a7a]">
        <i class="fas fa-info-circle mr-1"></i>
        Ao ativar um lote, os itens dele são divididos automaticamente (75% Ampla Concorrência / 25% Cota Reservada,
        arredondado para cima) apenas para fins de geração de documentos do processo (Termo de Referência/Edital) e
        exportação BNC — <strong>o ETP Inteligente original não é alterado</strong>. Desmarcar reverte a divisão
        imediatamente na próxima geração de documento/planilha. As colunas "Ampla Concorrência" e "Cota Reservada"
        abaixo já mostram uma prévia de como cada item ficaria dividido.
    </div>

    @if($lotes->isEmpty())
        <div class="py-16 flex flex-col items-center justify-center text-gray-400 bg-white rounded-xl border border-gray-200">
            <i class="fas fa-box-open text-4xl mb-3 opacity-30"></i>
            <p class="text-sm font-medium">Nenhum lote encontrado para este processo.</p>
            <p class="text-xs mt-1">Vincule um ETP Inteligente organizado por lotes para habilitar esta funcionalidade.</p>
        </div>
    @else

    <div x-data="cotaReservadaApp()">
        <form @submit.prevent="salvar" x-ref="formCota" class="space-y-4">
            @foreach($lotes as $lote)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ abertoItens: false }">
                <div class="flex items-start gap-3 p-4 hover:bg-gray-50 transition-colors">
                    <label class="flex items-start gap-3 flex-1 cursor-pointer">
                        <input type="checkbox" name="lote_ids[]" value="{{ $lote['etp_lote_id'] }}"
                               {{ in_array($lote['etp_lote_id'], $loteIdsAtivos) ? 'checked' : '' }}
                               class="mt-1 w-4 h-4 text-[#009496] border-gray-300 rounded focus:ring-[#009496]">
                        <div class="flex-1">
                            <span class="text-sm font-semibold text-gray-800">{{ $lote['nome'] }}</span>
                            <span class="text-xs text-gray-500 ml-1">({{ $lote['itens']->count() }} {{ Str::plural('item', $lote['itens']->count()) }})</span>
                            @if($lote['itens_abaixo_do_minimo'] > 0)
                            <p class="text-xs text-amber-600 mt-1">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                {{ $lote['itens_abaixo_do_minimo'] }} {{ Str::plural('item', $lote['itens_abaixo_do_minimo']) }}
                                com quantidade menor que {{ \App\Services\CotaReservadaService::QUANTIDADE_MINIMA_PARA_DIVIDIR }}
                                — não {{ $lote['itens_abaixo_do_minimo'] > 1 ? 'serão divididos' : 'será dividido' }}, fica(m) 100% em Ampla Concorrência.
                            </p>
                            @endif
                        </div>
                    </label>

                    <button type="button" @click="abertoItens = !abertoItens"
                            class="flex-shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-[#007a7a] bg-[#009496]/10 rounded-md hover:bg-[#009496]/20 transition-colors">
                        <span x-text="abertoItens ? 'Ocultar itens' : 'Ver itens'"></span>
                        <svg :class="abertoItens ? 'rotate-180' : ''" class="w-3.5 h-3.5 transition-transform duration-200"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>

                <div x-show="abertoItens" x-cloak x-collapse class="overflow-x-auto border-t border-gray-100">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-[10px] font-bold">
                            <tr>
                                <th class="px-3 py-2 w-8 text-center">#</th>
                                <th class="px-3 py-2">Descrição</th>
                                <th class="px-3 py-2 w-16 text-center">Und.</th>
                                <th class="px-3 py-2 w-20 text-right">Qtd. Total</th>
                                <th class="px-3 py-2 w-32 text-right">Ampla Concorrência</th>
                                <th class="px-3 py-2 w-32 text-right">Cota Reservada</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($lote['itens'] as $idx => $item)
                            <tr class="bg-white hover:bg-gray-50">
                                <td class="px-3 py-2 text-center text-gray-400">{{ $idx + 1 }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $item['descricao'] }}</td>
                                <td class="px-3 py-2 text-center text-gray-500">{{ $item['unidade'] }}</td>
                                <td class="px-3 py-2 text-right font-medium text-gray-700">{{ $item['quantidade'] }}</td>
                                @if($item['divide'])
                                <td class="px-3 py-2 text-right text-gray-700">{{ $item['quantidade_ampla'] }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-[#007a7a]">{{ $item['quantidade_reservada'] }}</td>
                                @else
                                <td class="px-3 py-2 text-right text-gray-400" colspan="2">
                                    <i class="fas fa-minus-circle mr-1"></i> não divide (qtd. abaixo do mínimo)
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" :disabled="salvando"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] disabled:opacity-60 transition-all shadow-sm">
                    <i class="fas fa-save"></i>
                    <span x-text="salvando ? 'Salvando...' : 'Salvar'"></span>
                </button>
                <span x-show="mensagem" :class="sucesso ? 'text-green-700' : 'text-red-700'" class="text-xs font-semibold" x-text="mensagem"></span>
            </div>
        </form>
    </div>
    @endif
</div>

<script>
function cotaReservadaApp() {
    return {
        salvando: false,
        mensagem: '',
        sucesso: true,
        async salvar() {
            this.salvando = true;
            this.mensagem = '';

            const formData = new FormData(this.$refs.formCota);
            const loteIds = formData.getAll('lote_ids[]');

            try {
                const res = await fetch('{{ route('admin.processos.cota_reservada.store', $processo->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ lote_ids: loteIds }),
                });
                const json = await res.json();
                this.sucesso = res.ok && json.success;
                this.mensagem = this.sucesso ? 'Configuração salva com sucesso!' : ('Erro: ' + (json.message ?? 'Tente novamente.'));
            } catch (e) {
                this.sucesso = false;
                this.mensagem = 'Erro de rede ao salvar. Tente novamente.';
            } finally {
                this.salvando = false;
                setTimeout(() => { this.mensagem = ''; }, 5000);
            }
        },
    };
}
</script>
@endsection
