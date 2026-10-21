<form method="post" action="{{ route('password.update') }}" class="space-y-6">
    @csrf
    @method('put')

    <h4 class="mb-4 font-medium text-gray-700 text-md">Alteração de Senha</h4>

    <div class="space-y-4">
        <div>
            <label for="current_password" class="block mb-2 text-sm font-medium text-gray-700">Senha Atual</label>
            <input type="password" name="current_password" id="current_password"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors"
                   autocomplete="current-password">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="block mb-2 text-sm font-medium text-gray-700">Nova Senha</label>
            <input type="password" name="password" id="password"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors"
                   autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-700">Confirmar Nova Senha</label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors"
                   autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>
    </div>

    <div class="flex items-center gap-4">
        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-[var(--mint)] rounded-lg hover:bg-[var(--info-solid)] transition-colors shadow-sm">
            <i class="fas fa-key"></i>
            Alterar Senha
        </button>

        @if (session('status') === 'password-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
               class="text-sm text-green-600">
                Senha alterada com sucesso.
            </p>
        @endif
    </div>
</form>
