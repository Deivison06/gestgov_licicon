@props([
    'unidades'  => [],
    'alpineRef' => 'assinaturaConfig',
    'tipo'      => 'global'
])

<div x-data="inlineAssinantes_{{ $tipo }}({
        unidades: {{ json_encode($unidades) }},
        configPai: '{{ $alpineRef }}',
        tipoDoc: '{{ $tipo }}'
     })"
     x-init="hidratarDoPai()"
     class="w-full bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">

    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
        <h4 class="text-sm font-semibold text-gray-800">Seleção de Assinantes</h4>
        <span class="text-xs font-medium text-gray-500" x-show="assinantes.length > 0">
            <span x-text="assinantes.length"></span> selecionado(s)
        </span>
    </div>

    <div class="p-6 space-y-6">
        {{-- Modo + prazo --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Modo da rodada</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button"
                            @click="modo = 'paralelo'; syncPai()"
                            :class="modo === 'paralelo'
                                ? 'bg-[#009496] text-white border-[#009496] shadow-sm'
                                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                            class="px-3 py-2.5 text-sm font-medium border rounded-md transition-all text-left">
                        <div class="font-semibold">Paralelo</div>
                        <div class="text-[10px] opacity-80 mt-0.5">Assinam em qualquer ordem</div>
                    </button>
                    <button type="button"
                            @click="modo = 'sequencial'; syncPai()"
                            :class="modo === 'sequencial'
                                ? 'bg-[#009496] text-white border-[#009496] shadow-sm'
                                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                            class="px-3 py-2.5 text-sm font-medium border rounded-md transition-all text-left">
                        <div class="font-semibold">Sequencial</div>
                        <div class="text-[10px] opacity-80 mt-0.5">Próximo só após o anterior</div>
                    </button>
                </div>
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Prazo (dias)</label>
                <input type="number" x-model.number="prazoDias" @change="syncPai()" min="1" max="60"
                       class="block w-full px-3 py-2.5 text-sm bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#009496] focus:border-[#009496]">
                <p class="mt-1.5 text-xs text-gray-500">Expira após este período.</p>
            </div>
        </div>

        <div class="border-t border-gray-200"></div>

        {{-- Seleção e Edição --}}
        <div class="space-y-4">
            <h5 class="text-sm font-semibold text-gray-700">Adicionar Assinante</h5>
            
            <div class="flex flex-col gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg sm:flex-row sm:items-end">
                <div class="flex-1 min-w-[180px]">
                    <label class="block mb-1 text-xs font-medium text-gray-600">Unidade</label>
                    <select x-model="selecionadaId" @change="aoSelecionarUnidade()"
                            class="block w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#009496] focus:border-[#009496]">
                        <option value="">Selecione a Unidade</option>
                        <template x-for="unidade in unidades" :key="unidade.id">
                            <option :value="unidade.id" x-text="unidade.nome"></option>
                        </template>
                    </select>
                </div>
                <div class="flex-1 min-w-[160px]">
                    <label class="block mb-1 text-xs font-medium text-gray-600">Responsável</label>
                    <input type="text" x-model="formResp" placeholder="Nome do Responsável" 
                           class="block w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:ring-[#009496]">
                </div>
                <div class="flex-1 min-w-[120px]">
                    <label class="block mb-1 text-xs font-medium text-gray-600">Nº Portaria</label>
                    <input type="text" x-model="formPortaria" placeholder="Opcional" 
                           class="block w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:ring-[#009496]">
                </div>
                <div class="flex-1 min-w-[120px]">
                    <label class="block mb-1 text-xs font-medium text-gray-600">Data Portaria</label>
                    <input type="text" x-model="formDataPortaria" placeholder="Opcional" 
                           class="block w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:ring-[#009496]">
                </div>
                <div>
                    <button type="button" @click="adicionarSelecionada()" :disabled="!selecionadaId || !formResp"
                            class="flex items-center justify-center w-full px-4 py-2 text-sm font-semibold text-white transition-colors bg-[#009496] rounded-md shadow disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[#007779]">
                        + Adicionar
                    </button>
                </div>
            </div>
        </div>

        {{-- Selecionados --}}
        <div class="border border-emerald-200 rounded-lg overflow-hidden bg-white">
            <div class="px-4 py-2.5 bg-emerald-50 border-b border-emerald-200 flex items-center justify-between">
                <span class="text-xs font-semibold text-emerald-800 uppercase tracking-wide">
                    Assinantes Selecionados
                </span>
                <span x-show="modo === 'sequencial' && assinantes.length > 1"
                      class="text-xs font-medium text-emerald-700">
                    Use ↑ ↓ para definir a ordem
                </span>
            </div>

            <template x-if="assinantes.length === 0">
                <div class="p-6 text-sm text-center text-gray-500">
                    Nenhum assinante adicionado. Selecione uma unidade acima.
                </div>
            </template>

            <ul class="divide-y divide-emerald-100">
                <template x-for="(a, idx) in assinantes" :key="idx">
                    <li class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <span x-show="modo === 'sequencial'"
                                  class="inline-flex items-center justify-center w-8 h-8 text-sm font-bold text-emerald-800 bg-emerald-100 rounded-full shadow-sm"
                                  x-text="idx + 1"></span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate" x-text="a.responsavel"></p>
                                <p class="text-xs text-gray-500 truncate" x-text="a.unidade_nome + (a.numero_portaria ? ' - Portaria: ' + a.numero_portaria : '')"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 ml-4">
                            <button type="button"
                                    x-show="modo === 'sequencial' && idx > 0"
                                    @click="moverPraCima(idx)"
                                    class="p-1.5 text-gray-400 hover:text-gray-900 hover:bg-gray-100 rounded-md transition-colors"
                                    title="Mover para cima">
                                ↑
                            </button>
                            <button type="button"
                                    x-show="modo === 'sequencial' && idx < assinantes.length - 1"
                                    @click="moverPraBaixo(idx)"
                                    class="p-1.5 text-gray-400 hover:text-gray-900 hover:bg-gray-100 rounded-md transition-colors"
                                    title="Mover para baixo">
                                ↓
                            </button>
                            <button type="button"
                                    @click="remover(idx)"
                                    class="px-2.5 py-1.5 text-xs font-medium text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md transition-colors">
                                Remover
                            </button>
                        </div>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</div>

@once
<script>
    function inlineAssinantes_{{ $tipo }}({ unidades, configPai, tipoDoc }) {
        return {
            selecionadaId: '',
            formResp: '',
            formPortaria: '',
            formDataPortaria: '',
            unidades: unidades || [],
            modo: 'paralelo',
            prazoDias: 7,
            assinantes: [],
            configPai,
            tipoDoc,

            hidratarDoPai() {
                // Initialize state and expose to window
                window.assinaturaConfig = window.assinaturaConfig || {};
                const configExistente = window.assinaturaConfig[this.tipoDoc];
                
                if (configExistente) {
                    this.modo = configExistente.modo ?? 'paralelo';
                    this.prazoDias = configExistente.prazoDias ?? 7;
                    this.assinantes = JSON.parse(JSON.stringify(configExistente.assinantes ?? []));
                } else {
                    this.syncPai();
                }
            },

            aoSelecionarUnidade() {
                if (!this.selecionadaId) {
                    this.limparForm(true);
                    return;
                }
                const uni = this.unidades.find(u => u.id == this.selecionadaId);
                if (uni) {
                    this.formResp = uni.servidor_responsavel || '';
                    this.formPortaria = uni.numero_portaria || '';
                    this.formDataPortaria = uni.data_portaria || '';
                }
                
                // ADD AUTOMATICALLY AS REQUESTED
                this.adicionarSelecionada();
            },

            adicionarSelecionada() {
                if (!this.selecionadaId || !this.formResp) return;
                
                const uni = this.unidades.find(u => u.id == this.selecionadaId);
                this.assinantes.push({
                    unidade_id: uni.id,
                    unidade_nome: uni.nome,
                    responsavel: this.formResp,
                    numero_portaria: this.formPortaria,
                    data_portaria: this.formDataPortaria
                });
                this.limparForm();
                this.syncPai();
            },

            limparForm(manterSelect = false) {
                if (!manterSelect) this.selecionadaId = '';
                this.formResp = '';
                this.formPortaria = '';
                this.formDataPortaria = '';
            },

            remover(idx) { 
                this.assinantes.splice(idx, 1); 
                this.syncPai();
            },

            moverPraCima(idx) {
                if (idx <= 0) return;
                [this.assinantes[idx-1], this.assinantes[idx]] = [this.assinantes[idx], this.assinantes[idx-1]];
                this.syncPai();
            },
            
            moverPraBaixo(idx) {
                if (idx >= this.assinantes.length - 1) return;
                [this.assinantes[idx+1], this.assinantes[idx]] = [this.assinantes[idx], this.assinantes[idx+1]];
                this.syncPai();
            },

            syncPai() {
                const payload = {
                    modo: this.modo,
                    prazoDias: this.prazoDias,
                    assinantes: this.assinantes.map((a, idx) => ({
                        ...a,
                        ordem: this.modo === 'sequencial' ? (idx + 1) : 0,
                    })),
                };

                window.assinaturaConfig = window.assinaturaConfig || {};
                window.assinaturaConfig[this.tipoDoc] = payload;
                
                // Expose globally for legacy scripts to access the array easily
                window.dispatchEvent(new CustomEvent('assinatura:config-atualizada', { 
                    detail: { tipoDoc: this.tipoDoc, payload } 
                }));
            }
        };
    }
</script>
@endonce
