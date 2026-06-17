@once
{{-- ============================================================
     Ponte: conecta o bloco LEGADO de "Seleção de Assinantes" à
     persistência (DocumentoSelecaoAssinantes). Ao selecionar:
       - salva no banco (debounced)
       - mantém window.assinaturaConfig[tipo] em sincronia, para que o
         botão "Solicitar Assinatura" encontre a seleção sem reselecionar.
     Ao carregar a página, re-hidrata o bloco a partir do que está salvo.

     Funciona em qualquer tela que renderize um container
     `#assinantes-container-*` com os data-attributes:
       data-assinante-bridge data-tipo data-homologacao-id data-vencedor-id
       data-obter-url data-salvar-url
============================================================ --}}
<script>
(() => {
    if (window.__assinanteBridgeLoaded) return;
    window.__assinanteBridgeLoaded = true;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const debounce = (fn, ms) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };

    const itens = (c) => Array.from(c.querySelectorAll('.assinante-item'));

    function coletar(container) {
        const out = [];
        itens(container).forEach(item => {
            const sel   = item.querySelector('.unidade-select');
            if (!sel || !sel.value) return; // só itens com unidade escolhida
            out.push({
                unidade_id:      sel.value,
                unidade_nome:    sel.options[sel.selectedIndex]?.text || '',
                responsavel:     item.querySelector('.responsavel-input')?.value || '',
                numero_portaria: item.querySelector('.portaria-input')?.value || '',
                data_portaria:   item.querySelector('.data-portaria-input')?.value || '',
            });
        });
        return out;
    }

    // Mantém window.assinaturaConfig[tipoBase] em sincronia (usado pelo botão Solicitar).
    function sincronizarConfig(container, assinantes) {
        const base = container.dataset.tipo;
        window.assinaturaConfig = window.assinaturaConfig || {};
        window.assinaturaConfig[base] = {
            modo:       (window.assinaturaConfig[base]?.modo) || 'paralelo',
            prazoDias:  (window.assinaturaConfig[base]?.prazoDias) || 7,
            assinantes: assinantes,
        };
    }

    async function salvar(container) {
        if (container.dataset.hidratando === '1') return; // evita salvar durante a hidratação
        const assinantes = coletar(container);
        sincronizarConfig(container, assinantes);
        if (!assinantes.length) return; // não persiste seleção vazia
        try {
            await fetch(container.dataset.salvarUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    tipo_documento: container.dataset.tipo,
                    homologacao_id: container.dataset.homologacaoId || null,
                    vencedor_id:    container.dataset.vencedorId || null,
                    modo:           window.assinaturaConfig[container.dataset.tipo]?.modo || 'paralelo',
                    prazo_dias:     window.assinaturaConfig[container.dataset.tipo]?.prazoDias || 7,
                    assinantes,
                }),
            });
        } catch (e) {
            console.warn('[assinante-bridge] falha ao salvar seleção', e);
        }
    }

    function aplicar(container, assinantes) {
        if (!Array.isArray(assinantes) || !assinantes.length) return;
        const chave = container.id.replace('assinantes-container-', ''); // tipo + idSuffix (chave do adicionarAssinante)

        // Garante linhas suficientes reutilizando a função da página.
        let linhas = itens(container);
        while (linhas.length < assinantes.length && typeof window.adicionarAssinante === 'function') {
            window.adicionarAssinante(chave);
            linhas = itens(container);
        }

        assinantes.forEach((a, i) => {
            const item = linhas[i];
            if (!item) return;
            const sel = item.querySelector('.unidade-select');
            if (sel && a.unidade_id != null && a.unidade_id !== '') {
                sel.value = String(a.unidade_id);
                // dispara updateResponsavel (preenche responsável/portaria pela lógica da página)
                sel.dispatchEvent(new Event('change', { bubbles: true }));
            }
            // fallback: preenche direto caso a página não tenha preenchido
            const resp  = item.querySelector('.responsavel-input');
            const port  = item.querySelector('.portaria-input');
            const dataP = item.querySelector('.data-portaria-input');
            if (resp  && !resp.value  && a.responsavel)     resp.value  = a.responsavel;
            if (port  && !port.value  && a.numero_portaria) port.value  = a.numero_portaria;
            if (dataP && !dataP.value && a.data_portaria)   dataP.value = a.data_portaria;
        });

        sincronizarConfig(container, coletar(container));
    }

    async function hidratar(container) {
        const url = container.dataset.obterUrl
            + '?tipo_documento=' + encodeURIComponent(container.dataset.tipo)
            + (container.dataset.homologacaoId ? '&homologacao_id=' + container.dataset.homologacaoId : '')
            + (container.dataset.vencedorId    ? '&vencedor_id='    + container.dataset.vencedorId    : '');
        try {
            const res  = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf } });
            const data = await res.json();
            if (data?.success && data.data?.assinantes?.length) {
                container.dataset.hidratando = '1';
                aplicar(container, data.data.assinantes);
                container.dataset.hidratando = '0';
            }
        } catch (e) {
            console.warn('[assinante-bridge] falha ao hidratar seleção', e);
        }
    }

    function init() {
        document.querySelectorAll('[data-assinante-bridge]').forEach(container => {
            if (container.dataset.bridgeBooted) return;
            container.dataset.bridgeBooted = '1';
            const salvarDebounced = debounce(() => salvar(container), 600);
            container.addEventListener('change', salvarDebounced);
            container.addEventListener('input', salvarDebounced);
            hidratar(container);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    // Reage a re-render dinâmico (mesma convenção do widget de assinatura)
    document.addEventListener('assinatura:reinicializar', init);
})();
</script>
@endonce
