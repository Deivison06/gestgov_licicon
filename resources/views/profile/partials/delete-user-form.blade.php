<h4 class="mb-4 font-medium text-gray-700 text-md">Excluir Conta</h4>

<p class="mb-6 text-sm text-gray-600">
    Uma vez que sua conta for excluída, todos os seus recursos e dados serão permanentemente apagados.
    Antes de excluir sua conta, faça o download de quaisquer dados ou informações que deseja manter.
</p>

<!-- Modal Trigger -->
<button x-data="" x-on:click="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors shadow-sm">
    <i class="fas fa-trash"></i>
    Excluir Conta
</button>

<!-- Modal -->
<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
        @csrf
        @method('delete')

        <h2 class="text-lg font-medium text-gray-900">
            Tem certeza que deseja excluir sua conta?
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Esta ação não pode ser desfeita. Todos os seus dados serão permanentemente excluídos.
        </p>

        <div class="mt-6">
            <label for="password" class="block mb-2 text-sm font-medium text-gray-700">Digite sua senha para confirmar</label>
            <input type="password" name="password" id="password"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors"
                   placeholder="Sua senha">
            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <x-secondary-button x-on:click="$dispatch('close')">
                Cancelar
            </x-secondary-button>

            <x-danger-button>
                Excluir Conta
            </x-danger-button>
        </div>
    </form>
</x-modal>
