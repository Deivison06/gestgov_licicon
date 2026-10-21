<!-- Modal de Edição Rápida do Item -->
<div id="modal-item-quick-edit" class="fixed inset-0 z-[70] flex items-center justify-center hidden bg-black/70 backdrop-blur-sm">
    <div class="bg-[var(--card)] border border-[var(--line)] rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col mx-4">
        <div class="p-6 border-b border-[var(--line)] bg-[var(--card-2)] flex justify-between items-center rounded-t-2xl">
            <h3 class="text-xl font-bold text-[var(--ink)]">Detalhes do Item</h3>
            <button type="button" onclick="closeModalItemQuickEdit()" class="text-[var(--ink-2)] hover:text-red-400 focus:outline-none transition-colors duration-200">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <div class="p-6 bg-[var(--card)]">
            <input type="hidden" id="quick_edit_item_id" value="">
            <div class="mb-2">
                <label class="block text-sm font-semibold leading-6 text-[var(--ink-2)] mb-1">Descrição</label>
                <textarea id="quick_edit_descricao" rows="6" class="block w-full rounded-xl bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] py-2.5 px-3 shadow-sm placeholder-[var(--ink-2)]/50 focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] sm:text-sm sm:leading-6"></textarea>
            </div>
            <p id="quick_edit_alert" class="hidden text-sm text-red-400 font-medium mt-2"></p>
        </div>

        <div class="px-6 py-4 bg-[var(--card-2)] border-t border-[var(--line)] flex justify-end gap-3 items-center">
            <div id="quick_edit_loading" class="hidden text-sm text-[var(--ink-2)] flex items-center gap-2">
                <div class="w-4 h-4 border-2 border-[var(--mint)]/20 border-t-[var(--mint)] rounded-full animate-spin"></div>
                Salvando...
            </div>
            <button type="button" onclick="closeModalItemQuickEdit()" class="text-sm text-[var(--ink)] bg-[var(--card)] border border-[var(--line)] font-semibold px-4 py-2 hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] rounded-xl transition-colors duration-200">
                Cancelar
            </button>
            <button type="button" onclick="saveItemQuickEdit()" id="btn_save_quick_edit" class="rounded-xl bg-[var(--mint)] px-5 py-2 text-sm font-bold text-[var(--on-mint)] shadow-sm hover:bg-[var(--mint-h)] hover:scale-105 transition-all duration-200">
                Salvar Alterações
            </button>
        </div>
    </div>
</div>

<script>
    function openModalItemQuickEdit(itemId, currentDescription) {
        document.getElementById('quick_edit_item_id').value = itemId;
        document.getElementById('quick_edit_descricao').value = currentDescription;
        document.getElementById('quick_edit_alert').classList.add('hidden');
        document.getElementById('quick_edit_loading').classList.add('hidden');
        document.getElementById('btn_save_quick_edit').disabled = false;
        document.getElementById('modal-item-quick-edit').classList.remove('hidden');
    }

    function closeModalItemQuickEdit() {
        document.getElementById('modal-item-quick-edit').classList.add('hidden');
        document.getElementById('quick_edit_item_id').value = '';
        document.getElementById('quick_edit_descricao').value = '';
    }

    async function saveItemQuickEdit() {
        const itemId = document.getElementById('quick_edit_item_id').value;
        const novaDescricao = document.getElementById('quick_edit_descricao').value.trim();
        const alertBox = document.getElementById('quick_edit_alert');
        const loading = document.getElementById('quick_edit_loading');
        const btnSave = document.getElementById('btn_save_quick_edit');

        if (!novaDescricao) {
            alertBox.textContent = 'A descrição não pode ficar vazia.';
            alertBox.classList.remove('hidden');
            return;
        }

        alertBox.classList.add('hidden');
        loading.classList.remove('hidden');
        btnSave.disabled = true;

        try {
            const response = await fetch(`/admin/etp-itens/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    descricao_item: novaDescricao
                })
            });

            const json = await response.json();

            if (json.success) {
                // Atualiza o texto na tela em todos os locais onde o item aparece
                document.querySelectorAll(`.desc-item-${itemId}`).forEach(el => {
                    el.textContent = novaDescricao;
                    el.title = novaDescricao;
                });
                
                // Atualiza o botão que abre o modal para que da próxima vez abra com o texto novo
                document.querySelectorAll(`.btn-edit-item-${itemId}`).forEach(btn => {
                    // Nós vamos armazenar o texto em data-descricao no botão ou simplesmente usar o innerText do parágrafo.
                    // Para garantir, vamos só fechar e a próxima vez puxamos do elemento texto.
                });

                closeModalItemQuickEdit();
            } else {
                alertBox.textContent = json.message || 'Erro ao salvar.';
                alertBox.classList.remove('hidden');
            }
        } catch (e) {
            alertBox.textContent = 'Falha de comunicação com o servidor.';
            alertBox.classList.remove('hidden');
        } finally {
            loading.classList.add('hidden');
            btnSave.disabled = false;
        }
    }
</script>
