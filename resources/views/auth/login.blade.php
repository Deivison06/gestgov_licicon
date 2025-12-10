<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="w-full space-y-5">
        @csrf

        <!-- INPUT EMAIL -->
        <div class="relative">
            <input type="email"
                    name="email"
                    required
                    placeholder="EMAIL"
                    class="w-full px-4 py-3.5 rounded-lg bg-white/95 text-sm outline-none border-2 border-transparent focus:border-[#05322A]/30 focus:bg-white shadow-md transition-all duration-300">
        </div>

        <!-- INPUT SENHA -->
        <div class="relative">
            <input type="password"
                    name="password"
                    required
                    placeholder="SENHA"
                    class="w-full px-4 py-3.5 rounded-lg bg-white/95 text-sm outline-none border-2 border-transparent focus:border-[#05322A]/30 focus:bg-white shadow-md transition-all duration-300">
        </div>

        <!-- LEMBRAR-ME -->
        <label class="flex items-center text-xs text-[#05322A] mt-2 select-none cursor-pointer group">
            <div class="relative mr-2">
                <input type="checkbox"
                    name="remember"
                    class="appearance-none w-4 h-4 rounded-full border-2 border-[#05322A] checked:bg-[#05322A] checked:border-[#05322A] transition-all cursor-pointer group-hover:border-[#052323]">
                <svg class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-2 h-2 text-white opacity-0 checked:opacity-100 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span class="font-medium group-hover:text-[#052323] transition-colors">LEMBRAR-ME</span>
        </label>

        <!-- BOTÃO ENTRAR -->
        <button type="submit"
            class="w-full bg-[#052323] hover:bg-[#03201E] text-white py-3.5 rounded-lg text-sm font-bold tracking-wide transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 shadow-lg hover:shadow-xl mt-6">
            ENTRAR
        </button>

        <!-- ESQUECI MINHA SENHA -->
        @if (Route::has('password.request'))
        <div class="text-center mt-4">
            <a href="{{ route('password.request') }}"
                class="text-xs text-[#05322A] hover:text-[#052323] font-medium hover:underline transition-colors">
                ESQUECI MINHA SENHA
            </a>
        </div>
        @endif
    </form>
</x-guest-layout>
