<form method="post" action="{{ route('profile.update') }}" class="space-y-6">
    @csrf
    @method('patch')

    <div>
        <h4 class="mb-4 font-medium text-gray-700 text-md">Informações Pessoais</h4>

        <div class="grid grid-cols-1 gap-4 mb-4">
            <div>
                <label for="name" class="block mb-2 text-sm font-medium text-gray-700">Nome</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors"
                       required autofocus autocomplete="name">
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <label for="email" class="block mb-2 text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors"
                       required autocomplete="username">
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>
            
            <div>
                <label for="cpf" class="block mb-2 text-sm font-medium text-gray-700">CPF</label>
                <input type="cpf" name="cpf" id="cpf" value="{{ old('cpf', $user->cpf) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors"
                       required autocomplete="username">
                <x-input-error class="mt-2" :messages="$errors->get('cpf')" />
            </div>
        </div>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div>
                <p class="mt-2 text-sm text-gray-800">
                    {{ __('Your email address is unverified.') }}

                    <button form="send-verification" class="text-sm text-gray-600 underline rounded-md hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm font-medium text-green-600">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            </div>
        @endif
    </div>

    <div class="flex items-center gap-4">
        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-[var(--mint)] rounded-lg hover:bg-[var(--info-bg)] transition-colors shadow-sm">
            <i class="fas fa-save"></i>
            Salvar
        </button>

        @if (session('status') === 'profile-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
               class="text-sm text-green-600">
                Salvo com sucesso.
            </p>
        @endif
    </div>
</form>
