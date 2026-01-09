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

        <!-- INPUT SENHA COM OLHINHO -->
        <div class="relative">
            <input type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="SENHA"
                    class="w-full px-4 py-3.5 pr-12 rounded-lg bg-white/95 text-sm outline-none border-2 border-transparent focus:border-[#05322A]/30 focus:bg-white shadow-md transition-all duration-300">
            
            <!-- Botão para mostrar/ocultar senha -->
            <button type="button"
                    id="togglePassword"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-[#05322A] transition-colors focus:outline-none">
                <!-- Ícone de olho fechado (padrão) -->
                <svg id="eyeClosed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <!-- Ícone de olho aberto (inicialmente oculto) -->
                <svg id="eyeOpen" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
            </button>
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

    <!-- Script para alternar visibilidade da senha -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeClosed = document.getElementById('eyeClosed');
            const eyeOpen = document.getElementById('eyeOpen');

            togglePassword.addEventListener('click', function() {
                // Alterna o tipo do input
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Alterna a visibilidade dos ícones
                eyeClosed.classList.toggle('hidden');
                eyeOpen.classList.toggle('hidden');
            });

            // Opcional: Adiciona tecla Enter para alternar
            togglePassword.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    togglePassword.click();
                }
            });
        });
    </script>
</x-guest-layout>