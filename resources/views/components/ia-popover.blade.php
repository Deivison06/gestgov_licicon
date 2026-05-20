{{-- ============================================================
  Popover de IA para um campo específico.
  Props:
    - name        (string)  id do campo alvo (ex: "justificativa")
    - processoId  (?int)    id do processo atual (opcional, melhora contexto)
============================================================ --}}
@props([
    'name',
    'processoId' => null,
])

<div x-data="iaPopoverField({
        campo: '{{ $name }}',
        processoId: {{ $processoId ? (int) $processoId : 'null' }},
     })"
     class="relative inline-block">

    {{-- Botão minimalista (faísca) --}}
    <button type="button"
            @click="abrir = !abrir"
            :aria-expanded="abrir"
            aria-label="Gerar conteúdo com IA"
            title="Gerar com IA"
            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-[#009496] hover:text-white hover:bg-[#009496] border border-[#009496] rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-[#009496]/30">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 2l1.8 5.2L19 9l-5.2 1.8L12 16l-1.8-5.2L5 9l5.2-1.8L12 2zm7 11l.9 2.6L22.5 16.5l-2.6.9L19 20l-.9-2.6L15.5 16.5l2.6-.9L19 13zM5 15l.7 2L7.5 17.5l-1.8.7L5 20l-.7-1.8L2.5 17.5l1.8-.7L5 15z"/>
        </svg>
        <span>IA</span>
    </button>

    {{-- Popover --}}
    <div x-show="abrir"
         x-cloak
         x-transition.opacity.duration.150ms
         @click.outside="abrir = false"
         @keydown.escape.window="abrir = false"
         role="dialog"
         aria-modal="true"
         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white border border-gray-200 rounded-xl shadow-xl z-50 p-4">
        <div class="flex items-start justify-between mb-3">
            <h4 class="text-sm font-semibold text-gray-900">Gerar com IA</h4>
            <button type="button" @click="abrir = false" class="text-gray-400 hover:text-gray-600" aria-label="Fechar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <label class="block mb-1 text-xs font-medium text-gray-600">
            Descreva o que você precisa
        </label>
        <textarea x-model="instrucao"
                  rows="3"
                  maxlength="500"
                  placeholder="Ex: Compra de 200 cadeiras para 3 escolas municipais. Justifique a necessidade."
                  @keydown.enter.prevent.stop="gerar()"
                  :disabled="loading"
                  class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#009496] focus:border-[#009496] disabled:bg-gray-50"></textarea>

        <div class="mt-1 flex items-center justify-between text-[10px] text-gray-400">
            <span x-text="instrucao.length + ' / 500'"></span>
            <span class="hidden sm:inline">Pressione Enter para gerar</span>
        </div>

        <label class="mt-3 flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
            <input type="checkbox" x-model="substituir" :disabled="loading"
                   class="rounded border-gray-300 text-[#009496] focus:ring-[#009496]">
            Substituir conteúdo atual do campo
        </label>

        <div class="mt-4 flex items-center justify-end gap-2">
            <button type="button"
                    @click="abrir = false"
                    :disabled="loading"
                    class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50">
                Cancelar
            </button>
            <button type="button"
                    @click="gerar()"
                    :disabled="loading || instrucao.trim().length < 3"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-[#009496] rounded-md hover:bg-[#007779] disabled:opacity-50 disabled:cursor-not-allowed">
                <template x-if="!loading">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l1.8 5.2L19 9l-5.2 1.8L12 16l-1.8-5.2L5 9l5.2-1.8L12 2z"/></svg>
                </template>
                <template x-if="loading">
                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 11-8 8h4z"></path>
                    </svg>
                </template>
                <span x-text="loading ? 'Gerando…' : 'Gerar'"></span>
            </button>
        </div>
    </div>

    {{-- Overlay de loading sobre o campo alvo (renderizado fora do popover) --}}
    <template x-teleport="body">
        <div x-show="loading"
             x-cloak
             class="pointer-events-none">
            {{-- O overlay real é criado dinamicamente em JS para posicionar
                 sobre o textarea / TinyMCE correto. --}}
        </div>
    </template>
</div>

@once
<script>
function iaPopoverField(config) {
    return {
        abrir: false,
        loading: false,
        instrucao: '',
        substituir: false,
        campo: config.campo,
        processoId: config.processoId,

        async gerar() {
            const texto = this.instrucao.trim();
            if (texto.length < 3) {
                window.toastIa?.('Descreva melhor o que você precisa (mínimo 3 caracteres).', 'error');
                return;
            }
            if (this.loading) return;

            this.loading = true;
            this.mostrarOverlayNoCampo(true);

            const resultado = await window.gerarConteudoIa({
                campo: this.campo,
                instrucao: texto,
                processoId: this.processoId,
            });

            this.loading = false;
            this.mostrarOverlayNoCampo(false);

            if (!resultado.success) {
                window.toastIa?.(resultado.message || 'Erro ao gerar conteúdo.', 'error');
                return;
            }

            window.injetarConteudoNoCampo(
                this.campo,
                resultado.conteudo,
                this.substituir ? 'replace' : 'append'
            );
            window.toastIa?.('Conteúdo gerado com sucesso!', 'success');
            this.abrir = false;
            this.instrucao = '';
        },

        /**
         * Cria um overlay semi-transparente sobre o textarea OU sobre o
         * iframe do TinyMCE (que substitui o textarea), com um spinner.
         */
        mostrarOverlayNoCampo(mostrar) {
            const overlayId = 'ia-overlay-' + this.campo;
            const existente = document.getElementById(overlayId);
            if (existente) existente.remove();
            if (!mostrar) return;

            // Tenta achar o container do TinyMCE primeiro; fallback no textarea
            const tinyContainer = document.querySelector(`.tox-tinymce[id$="${this.campo}_ifr"], #${this.campo}_ifr`)
                ?.closest('.tox-tinymce')
                ?? document.querySelector(`[data-tinymce-target="${this.campo}"]`);
            const alvo = tinyContainer
                ?? document.getElementById(this.campo)?.closest('.tox-tinymce')
                ?? document.getElementById(this.campo);

            if (!alvo) return;

            const rect = alvo.getBoundingClientRect();
            const overlay = document.createElement('div');
            overlay.id = overlayId;
            overlay.style.cssText = `
                position: fixed;
                top: ${rect.top}px;
                left: ${rect.left}px;
                width: ${rect.width}px;
                height: ${rect.height}px;
                background: rgba(255,255,255,0.75);
                display: flex; align-items: center; justify-content: center;
                z-index: 9998; border-radius: 8px;
                pointer-events: all;
            `;
            overlay.innerHTML = `
                <div style="display:flex;flex-direction:column;align-items:center;gap:8px;color:#009496;font-size:13px;font-weight:600;">
                    <svg style="width:32px;height:32px;animation:spin 1s linear infinite" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 11-8 8h4z"></path>
                    </svg>
                    <span>Gerando texto com IA…</span>
                </div>
                <style>@keyframes spin {to{transform:rotate(360deg)}}</style>
            `;
            document.body.appendChild(overlay);
        },
    };
}
</script>
@endonce
