<!-- Modal para Republicar Edital -->
<div id="modalRepublicarEdital" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

        <div class="inline-block p-6 my-8 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900">Republicar Edital</h3>
                <div class="mt-2">
                    <form id="formRepublicarEdital">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="data_republicacao" class="block text-sm font-medium text-gray-700">
                                    Data da Republicação
                                </label>
                                <input type="date" name="data" id="data_republicacao"
                                       value="{{ now()->format('Y-m-d') }}"
                                       class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="justificativa_republicacao" class="block text-sm font-medium text-gray-700">
                                    Justificativa da Republicação
                                </label>
                                <textarea name="justificativa" id="justificativa_republicacao" rows="3"
                                          class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                                          placeholder="Informe o motivo da republicação..."></textarea>
                            </div>

                            <input type="hidden" name="processo_id" id="processo_id_republicar_edital">
                        </div>

                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit"
                                    class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-purple-600 border border-transparent rounded-md shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:col-start-2 sm:text-sm">
                                Republicar Edital
                            </button>
                            <button type="button"
                                    onclick="fecharModal('modalRepublicarEdital')"
                                    class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Função para abrir modal de republicar edital
    function abrirModalRepublicarEdital(processoId) {
        document.getElementById('processo_id_republicar_edital').value = processoId;
        document.getElementById('data_republicacao').value = new Date().toISOString().split('T')[0];
        document.getElementById('justificativa_republicacao').value = '';
        document.getElementById('modalRepublicarEdital').classList.remove('hidden');
    }

    // Event listener para o formulário de republicar edital
    document.getElementById('formRepublicarEdital').addEventListener('submit', function(e) {
        e.preventDefault();

        const processoId = document.getElementById('processo_id_republicar_edital').value;
        const formData = new FormData(this);

        Swal.fire({
            title: 'Confirmar Republicação',
            text: 'Tem certeza que deseja republicar este edital?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, republicar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/processos/${processoId}/republicar-edital`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            fecharModal('modalRepublicarEdital');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: data.message,
                                showConfirmButton: true
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao republicar edital',
                            text: error.message
                        });
                    });
            }
        });
    });

    // Atualizar botão de republicar edital na view iniciar.blade.php
    document.addEventListener('DOMContentLoaded', function() {
        // Encontrar todos os botões de republicar edital e adicionar evento
        const botoesRepublicar = document.querySelectorAll('[onclick^="republicarEdital"]');
        botoesRepublicar.forEach(botao => {
            const processoId = botao.getAttribute('onclick').match(/\d+/)[0];
            botao.setAttribute('onclick', `abrirModalRepublicarEdital(${processoId})`);
        });
    });
</script>
