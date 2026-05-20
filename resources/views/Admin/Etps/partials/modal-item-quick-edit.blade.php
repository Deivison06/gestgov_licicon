<!-- Modal de Edição Rápida do Item -->
<div id="modal-item-quick-edit" class="fixed inset-0 z-[70] flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col mx-4">
        <div class="p-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center rounded-t-2xl">
            <h3 class="text-xl font-bold text-gray-800">Detalhes do Item</h3>
            <button type="button" onclick="closeModalItemQuickEdit()" class="text-gray-400 hover:text-red-500 focus:outline-none transition-colors duration-200">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <div class="p-6">
            <input type="hidden" id="quick_edit_item_id" value="">
            <div class="mb-2">
                <label class="block text-sm font-semibold leading-6 text-gray-900 mb-1">Descrição</label>
                <textarea id="quick_edit_descricao" rows="6" class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#009496] sm:text-sm sm:leading-6"></textarea>
            </div>
            <p id="quick_edit_alert" class="hidden text-sm text-red-600 font-medium mt-2"></p>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3 items-center">
            <div id="quick_edit_loading" class="hidden text-sm text-gray-500 flex items-center gap-2">
                <div class="w-4 h-4 border-2 border-[#009496]/20 border-t-[#009496] rounded-full animate-spin"></div>
                Salvando...
            </div>
            <button type="button" onclick="closeModalItemQuickEdit()" class="text-sm text-gray-600 hover:text-gray-900 font-medium px-4 py-2 hover:bg-gray-200 rounded-md transition-colors duration-200">
                Cancelar
            </button>
            <button type="button" onclick="saveItemQuickEdit()" id="btn_save_quick_edit" class="rounded-md bg-[#009496] px-5 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#007a7a] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#009496] transition-colors duration-200">
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
