<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4 px-8 pb-8 pt-7">
        @csrf

        <div>
          <h1 class="mb-1 text-center text-2xl font-extrabold tracking-tight">Entrar</h1>
          <p class="text-center text-[13px] text-slate-600">Acesse sua conta para continuar.</p>
        </div>

        <div>
          <label for="email" class="mb-1.5 block text-[12.5px] font-semibold text-slate-700">E-mail</label>
          <div class="flex h-[46px] items-center gap-2.5 rounded-xl border border-[#d5e0de] bg-[#f8fafa] pl-3.5 pr-2 text-slate-500 transition focus-within:border-[#2DC197] focus-within:bg-white focus-within:shadow-[0_0_0_3px_rgba(45,193,151,.28)]">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            <input id="email" name="email" type="email" autocomplete="username" required placeholder="voce@exemplo.com.br" value="{{ old('email') }}"
                   class="h-full min-w-0 flex-1 border-0 bg-transparent text-sm text-slate-900 outline-none placeholder:text-slate-500 focus:ring-0 [&:-webkit-autofill]:shadow-[inset_0_0_0px_1000px_#f8fafa] focus:[&:-webkit-autofill]:shadow-[inset_0_0_0px_1000px_#ffffff]">
          </div>
          <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
          <label for="senha" class="mb-1.5 block text-[12.5px] font-semibold text-slate-700">Senha</label>
          <div class="flex h-[46px] items-center gap-2.5 rounded-xl border border-[#d5e0de] bg-[#f8fafa] pl-3.5 pr-2 text-slate-500 transition focus-within:border-[#2DC197] focus-within:bg-white focus-within:shadow-[0_0_0_3px_rgba(45,193,151,.28)]">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input id="senha" name="password" type="password" autocomplete="current-password" required placeholder="••••••••"
                   class="h-full min-w-0 flex-1 border-0 bg-transparent text-sm text-slate-900 outline-none placeholder:text-slate-500 focus:ring-0 [&:-webkit-autofill]:shadow-[inset_0_0_0px_1000px_#f8fafa] focus:[&:-webkit-autofill]:shadow-[inset_0_0_0px_1000px_#ffffff]">
            <button id="toggle-senha" type="button" aria-label="Mostrar senha" title="Mostrar senha"
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[10px] text-slate-500 transition hover:bg-[#e6efed] hover:text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700">
              <svg data-eye-on class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.06 12.35a1 1 0 0 1 0-.7 10.75 10.75 0 0 1 19.88 0 1 1 0 0 1 0 .7 10.75 10.75 0 0 1-19.88 0"/><circle cx="12" cy="12" r="3"/></svg>
              <svg data-eye-off class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
            </button>
          </div>
          <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label class="flex min-h-11 cursor-pointer items-center gap-2.5 text-[13.5px] font-medium text-slate-700 w-fit">
          <input type="checkbox" name="remember" class="m-0 h-[18px] w-[18px] accent-teal-700 focus:ring-0">
          Lembrar-me
        </label>

        <button type="submit"
                class="flex h-[46px] w-full items-center justify-center gap-2.5 rounded-xl bg-[#2DC197] text-sm font-bold tracking-[.02em] text-[#06302d] shadow-[0_10px_22px_rgba(45,193,151,.35)] transition hover:bg-[#4bd2aa] active:translate-y-px focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700 mt-2">
          Entrar
          <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </button>
    </form>

    <script>
    (function () {
      var input = document.getElementById('senha');
      var btn = document.getElementById('toggle-senha');
      var eyeOn = btn.querySelector('[data-eye-on]');
      var eyeOff = btn.querySelector('[data-eye-off]');

      btn.addEventListener('click', function () {
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        var label = show ? 'Ocultar senha' : 'Mostrar senha';
        btn.setAttribute('aria-label', label);
        btn.setAttribute('title', label);
        eyeOn.classList.toggle('hidden', show);
        eyeOff.classList.toggle('hidden', !show);
      });
    })();
    </script>
</x-guest-layout>
