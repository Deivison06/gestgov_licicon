<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block mb-2 text-sm font-medium text-white">E-mail</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                placeholder="seu@email.com"
                class="w-full px-4 py-3.5 rounded-lg bg-white bg-opacity-95 focus:outline-none focus:ring-4 focus:ring-white focus:ring-opacity-30 shadow-sm transition-all duration-300 focus:-translate-y-0.5 focus-visible"
                aria-describedby="email-error">
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block mb-2 text-sm font-medium text-white">Senha</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Sua senha"
                class="w-full px-4 py-3.5 rounded-lg bg-white bg-opacity-95 focus:outline-none focus:ring-4 focus:ring-white focus:ring-opacity-30 shadow-sm transition-all duration-300 focus:-translate-y-0.5 focus-visible"
                aria-describedby="password-error">
        </div>

        <!-- Remember Me -->
        <div class="flex flex-wrap items-center justify-between gap-2 mt-6">
            <div class="flex items-center">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="w-4 h-4 mr-2 text-indigo-600 border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus-visible"
                    name="remember">
                <label for="remember_me" class="text-sm text-white cursor-pointer">Lembrar-me</label>
            </div>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                    class="text-sm text-white hover:text-[#e6f7ff] hover:underline transition-colors duration-300 focus:outline-none focus-visible:underline">
                    Esqueci minha senha
                </a>
            @endif
        </div>

        <button type="submit"
            class="w-full bg-[#062F43] hover:bg-[#083f5a] text-white font-semibold py-3.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus-visible btn-hover">
            Entrar
        </button>
    </form>
</x-guest-layout>
