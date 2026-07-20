@props([
    'processoId',
    'tipo',
    'homologacaoId' => null,
    'vencedorId'    => null,
    'compacto'      => false,
])

@php
    $compId  = 'btn-solic-' . str_replace([':', '.', ' '], '_', $tipo)
               . ($homologacaoId ? '-h' . $homologacaoId : '')
               . ($vencedorId ? '-v' . $vencedorId : '');
    $statusUrl    = route('admin.processos.assinatura.selecao.status', $processoId);
    $salvarUrl    = route('admin.processos.assinatura.selecao.salvar', $processoId);
    $solicitarUrl = route('admin.processos.assinatura.solicitar', $processoId);
    $cancelarUrl  = route('admin.processos.assinatura.cancelar', $processoId);
@endphp

<div id="{{ $compId }}"
     class="inline-flex items-center gap-2"
     data-assinatura-widget
     data-tipo="{{ $tipo }}"
     data-homologacao-id="{{ $homologacaoId ?? '' }}"
     data-vencedor-id="{{ $vencedorId ?? '' }}"
     data-status-url="{{ $statusUrl }}"
     data-salvar-url="{{ $salvarUrl }}"
     data-solicitar-url="{{ $solicitarUrl }}"
     data-cancelar-url="{{ $cancelarUrl }}">

    {{-- Badge de status (atualizado pelo JS no init e após ações) --}}
    <span data-status-badge
          class="hidden items-center gap-1 px-2 py-0.5 text-[10px] font-semibold rounded uppercase tracking-wide">
        <span data-status-icon></span>
        <span data-status-label></span>
    </span>

    {{-- Botão Solicitar (renderiza só quando estado permitir) --}}
    <button type="button"
            data-btn-solicitar
            class="hidden items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-[#009496] rounded-md shadow-sm hover:bg-[#007779] focus:outline-none focus:ring-2 focus:ring-[#009496] focus:ring-offset-1 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 12l2 2 4-4"></path>
            <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span data-btn-label>Solicitar Assinatura</span>
    </button>

    {{-- Botão Cancelar rodada (renderiza quando há rodada ativa) --}}
    <button type="button"
            data-btn-cancelar
            class="hidden items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-red-600 rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6L6 18M6 6l12 12"></path>
        </svg>
        <span>Cancelar rodada</span>
    </button>
</div>

@once
<script>
(() => {
    // ==========================================================
    // Persistência da seleção + status + disparo da rodada
    // ==========================================================
    window.AssinaturaPersistencia = window.AssinaturaPersistencia || {
        _csrf: document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',

        async _fetch(url, opts = {}) {
            const res = await fetch(url, {
                ...opts,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this._csrf,
                    ...(opts.body ? { 'Content-Type': 'application/json' } : {}),
                    ...(opts.headers || {}),
                },
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                const err = new Error(data.message || `HTTP ${res.status}`);
                err.payload = data;
                throw err;
            }
            return data;
        },

        /** Hidrata window.assinaturaConfig[tipo] a partir do backend. */
        async hidratar(processoId, tipo, homologacaoId, vencedorId) {
            const url = '{{ route('admin.processos.assinatura.selecao.obter', ':pid') }}'.replace(':pid', processoId)
                + `?tipo_documento=${encodeURIComponent(tipo)}`
                + (homologacaoId ? `&homologacao_id=${homologacaoId}` : '')
                + (vencedorId    ? `&vencedor_id=${vencedorId}`    : '');
            const r = await this._fetch(url);
            if (r.success && r.data) {
                window.assinaturaConfig = window.assinaturaConfig || {};
                window.assinaturaConfig[tipo] = {
                    modo:       r.data.modo,
                    prazoDias:  r.data.prazo_dias,
                    assinantes: r.data.assinantes,
                };
                this._reHidratarPainelAlpine(tipo);
                window.dispatchEvent(new CustomEvent('assinatura:hidratada', {
                    detail: { tipoDoc: tipo, payload: window.assinaturaConfig[tipo] }
                }));
            }
            return r.data;
        },

        /**
         * Força o painel customizado `inlineAssinantes_${tipo}` a re-ler
         * `window.assinaturaConfig[tipo]` chamando seu `hidratarDoPai()`.
         * Necessário porque Alpine init roda ANTES do fetch terminar.
         */
        _reHidratarPainelAlpine(tipo) {
            if (!window.Alpine) return;
            const marker = `inlineAssinantes_${tipo}(`;
            document.querySelectorAll('[x-data]').forEach(el => {
                if (!(el.getAttribute('x-data') || '').includes(marker)) return;
                try {
                    const data = window.Alpine.$data(el);
                    if (data && typeof data.hidratarDoPai === 'function') {
                        data.hidratarDoPai();
                    }
                } catch (e) { /* swallow */ }
            });
        },

        /** Salva a seleção atual (window.assinaturaConfig[tipo]). */
        async salvar(salvarUrl, tipo, homologacaoId, vencedorId, opts = {}) {
            const conf = (window.assinaturaConfig && window.assinaturaConfig[tipo]) || {};
            const body = {
                tipo_documento: tipo,
                homologacao_id: homologacaoId || null,
                vencedor_id:    vencedorId    || null,
                modo:           conf.modo || 'paralelo',
                prazo_dias:     conf.prazoDias || 7,
                assinantes:     conf.assinantes || opts.assinantes || [],
            };
            if (!body.assinantes.length && !opts.permitirVazio) {
                return { success: false, message: 'Nenhum assinante selecionado.' };
            }
            return this._fetch(salvarUrl, { method: 'POST', body: JSON.stringify(body) });
        },

        /** Dispara a rodada (após salvar). */
        async solicitar(salvarUrl, solicitarUrl, tipo, homologacaoId, vencedorId) {
            const salvou = await this.salvar(salvarUrl, tipo, homologacaoId, vencedorId);
            if (!salvou.success) return salvou;
            return this._fetch(solicitarUrl, {
                method: 'POST',
                body: JSON.stringify({
                    tipo_documento: tipo,
                    homologacao_id: homologacaoId || null,
                    vencedor_id:    vencedorId    || null,
                }),
            });
        },

        /** Cancela a rodada ativa do documento. */
        async cancelar(cancelarUrl, tipo, homologacaoId, vencedorId) {
            return this._fetch(cancelarUrl, {
                method: 'POST',
                body: JSON.stringify({
                    tipo_documento: tipo,
                    homologacao_id: homologacaoId || null,
                    vencedor_id:    vencedorId    || null,
                }),
            });
        },

        async status(statusUrl, tipo, homologacaoId, vencedorId) {
            const url = statusUrl
                + `?tipo_documento=${encodeURIComponent(tipo)}`
                + (homologacaoId ? `&homologacao_id=${homologacaoId}` : '')
                + (vencedorId    ? `&vencedor_id=${vencedorId}`    : '');
            const r = await this._fetch(url);
            return r.data;
        },

        /**
         * Salva a seleção DIRETO no banco (chamada antes de "Gerar PDF").
         * Garante que "Solicitar Assinatura" depois encontre os dados.
         * Aceita assinantes/modo/prazo explícitos OU lê de window.assinaturaConfig[tipo].
         */
        async salvarSelecaoAntesDeGerar(processoId, tipo, homologacaoId, vencedorId, opts = {}) {
            const conf = (window.assinaturaConfig && window.assinaturaConfig[tipo]) || {};
            const assinantes = opts.assinantes || conf.assinantes || [];
            if (!assinantes.length) return null;

            const url = '{{ route('admin.processos.assinatura.selecao.salvar', ':pid') }}'.replace(':pid', processoId);
            const body = {
                tipo_documento: tipo,
                homologacao_id: homologacaoId || null,
                vencedor_id:    vencedorId    || null,
                modo:           opts.modo || conf.modo || 'paralelo',
                prazo_dias:     opts.prazoDias || conf.prazoDias || 7,
                assinantes:     assinantes,
            };
            try {
                return await this._fetch(url, { method: 'POST', body: JSON.stringify(body) });
            } catch (e) {
                console.warn('[assinatura] falha ao persistir seleção', tipo, e);
                return null;
            }
        },
    };

    // Expose conveniência global (callable de qualquer gerarPdf legado)
    window.salvarSelecaoAntesDeGerar = (...args) =>
        window.AssinaturaPersistencia.salvarSelecaoAntesDeGerar(...args);

    // Render do widget (badge + botão) ---------------------------
    const ESTADOS = {
        pronto_para_solicitar: { badge: null,                                            botao: 'Solicitar Assinatura' },
        aguardando:            { badge: ['bg-amber-100 text-amber-800', 'Aguardando'],   botao: null },
        parcialmente_assinado: { badge: ['bg-blue-100 text-blue-800', 'Em andamento'],   botao: null },
        assinado:              { badge: ['bg-emerald-100 text-emerald-800', 'Assinado'], botao: null },
    };

    function renderWidget(root, status) {
        const cfg = ESTADOS[status.estado] || ESTADOS.pronto_para_solicitar;
        const badge = root.querySelector('[data-status-badge]');
        const btn   = root.querySelector('[data-btn-solicitar]');

        if (cfg.badge) {
            badge.classList.remove('hidden');
            badge.classList.add('inline-flex');
            badge.className = badge.className.replace(/bg-\S+|text-\S+/g, '').trim()
                + ' inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold rounded uppercase tracking-wide '
                + cfg.badge[0];
            badge.querySelector('[data-status-label]').textContent = cfg.badge[1];
            if (status.pendentes > 0) {
                badge.querySelector('[data-status-label]').textContent += ` (${status.assinadas}/${status.total})`;
            }
        } else {
            badge.classList.add('hidden');
        }

        if (cfg.botao) {
            btn.classList.remove('hidden');
            btn.classList.add('inline-flex');
            btn.querySelector('[data-btn-label]').textContent = cfg.botao;
            btn.disabled = !status.pode_solicitar;
            btn.title = status.mensagem || '';
        } else {
            btn.classList.add('hidden');
            btn.classList.remove('inline-flex');
        }

        // Botão de cancelar: visível só quando há rodada ativa.
        const btnCancelar = root.querySelector('[data-btn-cancelar]');
        if (btnCancelar) {
            const ativa = status.estado === 'aguardando' || status.estado === 'parcialmente_assinado';
            btnCancelar.classList.toggle('hidden', !ativa);
            btnCancelar.classList.toggle('inline-flex', ativa);
        }
    }

    async function atualizarWidget(root) {
        const tipo = root.dataset.tipo;
        const h    = root.dataset.homologacaoId || null;
        const v    = root.dataset.vencedorId || null;
        try {
            const status = await window.AssinaturaPersistencia.status(root.dataset.statusUrl, tipo, h, v);
            renderWidget(root, status);
        } catch (e) {
            console.warn('[assinatura] falha ao buscar status', tipo, e);
        }
    }

    async function aoClicarSolicitar(root) {
        const tipo = root.dataset.tipo;
        const h    = root.dataset.homologacaoId || null;
        const v    = root.dataset.vencedorId || null;
        const btn  = root.querySelector('[data-btn-solicitar]');
        const orig = btn.querySelector('[data-btn-label]').textContent;

        btn.disabled = true;
        btn.querySelector('[data-btn-label]').textContent = 'Enviando...';
        try {
            const r = await window.AssinaturaPersistencia.solicitar(
                root.dataset.salvarUrl, root.dataset.solicitarUrl, tipo, h, v
            );
            if (r.success) {
                showMsg(r.message || 'Solicitação enviada.', 'success');
                await atualizarWidget(root);
            } else {
                showMsg(r.message || 'Falha ao solicitar.', 'error');
            }
        } catch (e) {
            showMsg(e.message || 'Erro inesperado.', 'error');
        } finally {
            btn.disabled = false;
            btn.querySelector('[data-btn-label]').textContent = orig;
        }
    }

    async function aoClicarCancelar(root) {
        const tipo = root.dataset.tipo;
        const h    = root.dataset.homologacaoId || null;
        const v    = root.dataset.vencedorId || null;
        const btn  = root.querySelector('[data-btn-cancelar]');

        if (!confirm('Cancelar a rodada de assinatura em andamento? As solicitações pendentes serão canceladas.')) {
            return;
        }

        btn.disabled = true;
        try {
            const r = await window.AssinaturaPersistencia.cancelar(root.dataset.cancelarUrl, tipo, h, v);
            if (r.success) {
                showMsg(r.message || 'Rodada cancelada.', 'success');
                await atualizarWidget(root);
            } else {
                showMsg(r.message || 'Falha ao cancelar.', 'error');
            }
        } catch (e) {
            showMsg(e.message || 'Erro inesperado ao cancelar.', 'error');
        } finally {
            btn.disabled = false;
        }
    }

    // Mensageria leve (não conflita com showMessage da página, se houver)
    function showMsg(msg, type) {
        if (typeof window.showMessage === 'function') { window.showMessage(msg, type); return; }
        const cont = document.getElementById('message-container');
        if (cont) {
            cont.innerHTML = `<div class="p-3 mb-3 border-l-4 rounded ${type === 'success' ? 'bg-green-100 border-green-400 text-green-800' : 'bg-red-100 border-red-400 text-red-800'}">${msg}</div>`;
            setTimeout(() => cont.innerHTML = '', 6000);
        } else {
            alert(msg);
        }
    }

    // Boot --------------------------------------------------------
    function inicializarWidgets() {
        document.querySelectorAll('[data-assinatura-widget]').forEach(root => {
            if (root.dataset.bootDone) return;
            root.dataset.bootDone = '1';
            const btn = root.querySelector('[data-btn-solicitar]');
            btn.addEventListener('click', () => aoClicarSolicitar(root));
            const btnCancelar = root.querySelector('[data-btn-cancelar]');
            if (btnCancelar) btnCancelar.addEventListener('click', () => aoClicarCancelar(root));
            atualizarWidget(root);

            // Hidrata seleção salva no window.assinaturaConfig (best-effort)
            const tipo = root.dataset.tipo;
            const h    = root.dataset.homologacaoId || null;
            const v    = root.dataset.vencedorId || null;
            const procId = root.dataset.solicitarUrl.match(/processos\/(\d+)\//)?.[1];
            if (procId) window.AssinaturaPersistencia.hidratar(procId, tipo, h, v).catch(() => {});
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializarWidgets);
    } else {
        inicializarWidgets();
    }

    // Reage a re-render dinâmico (Alpine/Livewire)
    document.addEventListener('assinatura:reinicializar', inicializarWidgets);
})();
</script>
@endonce
