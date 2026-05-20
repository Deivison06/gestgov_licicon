{{-- ============================================================
  IA Helper — JS único reaproveitado por todos os <x-form-field :ia="true">
  Pode ser incluído várias vezes na mesma página (auto-guarda).
============================================================ --}}
@once
<script>
(function () {
    if (window.__iaHelperLoaded) return;
    window.__iaHelperLoaded = true;

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        const input = document.querySelector('input[name="_token"]');
        return (meta && meta.getAttribute('content')) || (input && input.value) || '';
    }

    /**
     * Injeta `conteudo` (HTML formatado vindo da IA) no campo `nomeCampo`.
     * Detecta automaticamente se há TinyMCE inicializado e usa a API correta.
     *
     * @param {string} nomeCampo  id do <textarea> / <input>
     * @param {string} conteudo   HTML retornado pela IA (já sanitizado no backend)
     * @param {'append'|'replace'} modo
     */
    window.injetarConteudoNoCampo = function (nomeCampo, conteudo, modo = 'append') {
        const el = document.getElementById(nomeCampo);
        if (!el) {
            console.warn('IA: campo não encontrado:', nomeCampo);
            return;
        }

        // Detecta TinyMCE 6 ativo no campo
        const editor = (window.tinymce && tinymce.get) ? tinymce.get(nomeCampo) : null;

        if (editor) {
            if (modo === 'replace') {
                editor.setContent(conteudo);
            } else {
                // Append: insere o HTML cru no final do conteúdo atual
                editor.insertContent(conteudo);
            }
            // Sincroniza o <textarea> escondido + dispara input pra Alpine x-model
            el.value = editor.getContent();
            el.dispatchEvent(new Event('input', { bubbles: true }));
            return;
        }

        // Sem TinyMCE: textarea/input padrão — converte HTML para texto legível
        const textoPuro = htmlParaTextoLegivel(conteudo);
        if (modo === 'replace') {
            el.value = textoPuro;
        } else {
            const atual = el.value || '';
            const separador = atual.length > 0 && !atual.endsWith('\n') ? '\n\n' : '';
            el.value = atual + separador + textoPuro;
        }
        el.dispatchEvent(new Event('input', { bubbles: true }));
    };

    /**
     * Converte o HTML retornado pela IA em texto plain preservando quebras
     * de parágrafo e marcadores de lista. Usado só quando o campo NÃO está
     * sob TinyMCE.
     */
    function htmlParaTextoLegivel(html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html;
        // Marca itens de lista com "• " antes de extrair o texto
        tmp.querySelectorAll('li').forEach(li => {
            li.insertBefore(document.createTextNode('• '), li.firstChild);
        });
        // Quebra de linha entre blocos
        tmp.querySelectorAll('p, li, br').forEach(el => {
            el.appendChild(document.createTextNode('\n'));
        });
        // textContent ignora tags
        return (tmp.textContent || tmp.innerText || '')
            .replace(/\n{3,}/g, '\n\n')
            .trim();
    }

    /**
     * Dispara a chamada à API de IA.
     *
     * @returns {Promise<{success: boolean, conteudo?: string, message?: string, code?: string}>}
     */
    window.gerarConteudoIa = async function ({ campo, instrucao, processoId = null }) {
        const url = "{{ route('admin.ia.gerar') }}";
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({
                    campo: campo,
                    instrucao: instrucao,
                    processo_id: processoId,
                }),
            });

            // Sessão expirou (CSRF) — recarrega a página
            if (response.status === 419) {
                alert('Sua sessão expirou. A página será recarregada.');
                setTimeout(() => window.location.reload(), 800);
                return { success: false, code: 'CSRF', message: 'Sessão expirada.' };
            }

            const data = await response.json().catch(() => ({}));

            if (response.ok && data.success) {
                return data;
            }

            // 422 com erros de validação
            if (response.status === 422 && data.errors) {
                const primeiraMsg = Object.values(data.errors)[0]?.[0] || 'Dados inválidos.';
                return { success: false, code: 'VALIDATION', message: primeiraMsg };
            }

            return {
                success: false,
                code: data.code || 'ERROR',
                message: data.message || 'Erro ao gerar conteúdo. Tente novamente.',
            };
        } catch (err) {
            return { success: false, code: 'NETWORK', message: 'Falha de rede. Verifique sua conexão.' };
        }
    };

    /**
     * Mostra um toast discreto (top-right). Auto-some em 5s (erro) / 3s (sucesso).
     */
    window.toastIa = function (mensagem, tipo = 'success') {
        const cores = {
            success: 'bg-emerald-600',
            error:   'bg-rose-600',
            info:    'bg-slate-700',
        };
        const cor = cores[tipo] || cores.info;
        const toast = document.createElement('div');
        toast.className = `fixed top-6 right-6 z-[9999] px-4 py-3 rounded-lg shadow-lg ${cor} text-white text-sm max-w-sm transition-opacity duration-300`;
        toast.textContent = mensagem;
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '0'; }, tipo === 'error' ? 4500 : 2500);
        setTimeout(() => toast.remove(), tipo === 'error' ? 5000 : 3000);
    };
})();
</script>
@endonce
