{{-- ============================================================
    Helper JS para integrar o modal de assinatura com vanilla JS.

    Funções globais expostas:
      - window.injetarRodadaAssinantesNaUrl(url): string
      - window.injetarRodadaAssinantesNoFormData(formData): FormData
      - window.temRodadaAssinantesConfigurada(): bool

    Lê de `window.assinaturaConfig` populado pelo modal-selecionar-assinantes.
============================================================ --}}
@once
<script>
(function () {
    if (window.__assinaturaHelperLoaded) return;
    window.__assinaturaHelperLoaded = true;

    window.temRodadaAssinantesConfigurada = function () {
        const cfg = window.assinaturaConfig;
        return cfg && Array.isArray(cfg.assinantes) && cfg.assinantes.length > 0;
    };

    /**
     * Adiciona `rodada_assinantes[i][id]`, `rodada_assinantes[i][ordem]`, `modo`, `prazo_dias`
     * a uma URL com querystring. Não modifica nada se a config não estiver setada.
     */
    window.injetarRodadaAssinantesNaUrl = function (url) {
        if (!window.temRodadaAssinantesConfigurada()) return url;
        const cfg = window.assinaturaConfig;

        // Garante separador correto
        const sep = url.includes('?') ? '&' : '?';
        const parts = [];

        cfg.assinantes.forEach((a, idx) => {
            parts.push(`rodada_assinantes[${idx}][id]=${encodeURIComponent(a.id)}`);
            parts.push(`rodada_assinantes[${idx}][ordem]=${encodeURIComponent(a.ordem ?? 0)}`);
        });
        parts.push(`modo=${encodeURIComponent(cfg.modo || 'paralelo')}`);
        parts.push(`prazo_dias=${encodeURIComponent(cfg.prazoDias || 7)}`);

        return url + sep + parts.join('&');
    };

    /**
     * Adiciona os mesmos campos a um FormData. Para uso em POSTs.
     */
    window.injetarRodadaAssinantesNoFormData = function (formData) {
        if (!window.temRodadaAssinantesConfigurada()) return formData;
        const cfg = window.assinaturaConfig;

        cfg.assinantes.forEach((a, idx) => {
            formData.append(`rodada_assinantes[${idx}][id]`, a.id);
            formData.append(`rodada_assinantes[${idx}][ordem]`, a.ordem ?? 0);
        });
        formData.append('modo', cfg.modo || 'paralelo');
        formData.append('prazo_dias', cfg.prazoDias || 7);

        return formData;
    };

    /**
     * Adiciona aos campos JSON do body de um fetch. Para uso em POSTs JSON.
     */
    window.injetarRodadaAssinantesNoBody = function (body) {
        if (!window.temRodadaAssinantesConfigurada()) return body;
        const cfg = window.assinaturaConfig;

        body.rodada_assinantes = cfg.assinantes.map(a => ({
            id:    a.id,
            ordem: a.ordem ?? 0,
        }));
        body.modo = cfg.modo || 'paralelo';
        body.prazo_dias = cfg.prazoDias || 7;

        return body;
    };

    /**
     * Mostra um toast amigável depois da geração indicando que a rodada foi iniciada.
     */
    window.notificarRodadaIniciada = function (info) {
        if (!info) return;
        const total = info.total_solicitacoes ?? 0;
        const modo  = info.modo === 'sequencial' ? 'sequencial' : 'paralelo';

        const msg = `✅ Rodada de assinatura iniciada (${modo}) com ${total} solicitação(ões).`;

        // Tenta usar a função global existente nas views, senão alert
        if (typeof window.showMessage === 'function') {
            window.showMessage(msg, 'success');
        } else if (typeof window.mostrarMensagem === 'function') {
            window.mostrarMensagem(msg, 'success');
        } else {
            console.log(msg);
        }
    };
})();
</script>
@endonce
