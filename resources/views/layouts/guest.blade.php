<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Licicon — Login') }}</title>
    <link rel="icon" href="{{ asset('logo/minilogo-g-app-escuro-1024.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        }
    </style>
</head>
<body class="bg-[#051e1c] font-sans antialiased">

<div class="relative flex min-h-screen flex-col items-center gap-6 overflow-hidden bg-[linear-gradient(160deg,#0b3b37_0%,#072926_55%,#051e1c_100%)] px-6 pb-6 pt-8 text-white">

  <!-- Decoração de fundo -->
  <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(720px_520px_at_50%_48%,rgba(45,193,151,.16),rgba(45,193,151,0)_70%)]"></div>
  <div class="pointer-events-none absolute inset-x-0 top-0 h-[42%] bg-[radial-gradient(rgba(45,193,151,.4)_1.4px,rgba(45,193,151,0)_1.8px)] bg-[length:24px_24px] [-webkit-mask-image:linear-gradient(to_bottom,#000_0%,transparent_100%)] [mask-image:linear-gradient(to_bottom,#000_0%,transparent_100%)]"></div>

  <!-- ===================== MODAL ===================== -->
  <main class="relative flex w-full flex-1 flex-col items-center justify-center">
    <div class="w-full max-w-[400px] flex flex-col rounded-[20px] text-slate-900 shadow-[0_30px_80px_rgba(0,0,0,.45)]">

      <!-- Cabeçalho com a logo do sistema -->
      <div class="flex items-center justify-center rounded-t-[20px] bg-[#0b3b37] px-8 py-1">
        <img id="logo-sig"
             src="{{ asset('logo/licicon-horizontal-colorida-para-fundo-escuro.png') }}"
             alt="Licicon"
             class="block h-auto w-full max-w-[280px]">
      </div>

      <div class="rounded-b-[20px] bg-white">
        {{ $slot }}
      </div>

    </div>
  </main>
  <!-- =================== FIM DO MODAL =================== -->

  <!-- Rodapé: empresa e ajuda -->
  <footer class="relative flex w-full max-w-[1200px] flex-wrap items-center justify-between gap-x-6 gap-y-4 border-t border-white/10 pt-5 z-10">
    <div class="flex max-w-[380px] flex-col gap-3">
      <div class="flex items-center gap-3.5">
        <span class="whitespace-nowrap text-[11px] font-bold uppercase tracking-[.1em] text-white/60">Desenvolvido por</span>
        <span class="h-5 w-px shrink-0 bg-white/20"></span>
        <img id="logo-empresa"
             src="{{ asset('logo/logo_gestcloud.png') }}"
             alt="GestCloud"
             class="block h-6 w-auto">
      </div>
      <p class="text-xs leading-relaxed text-white/70">Desenvolvemos sistemas que organizam, modernizam e tornam a gestão pública mais eficiente.</p>
    </div>

    <a href="https://wa.me/558988060983" target="_blank" rel="noopener noreferrer"
       class="inline-flex items-center gap-2.5 rounded-full bg-white py-1.5 pl-4 pr-1.5 text-[12.5px] font-medium text-slate-900 shadow-[0_4px_14px_rgba(0,0,0,.25)] transition hover:shadow-[0_6px_20px_rgba(0,0,0,.35)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#5eead4]">
      Dificuldade no acesso? Fale conosco.
      <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#2DC197] text-[#06302d]">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
      </span>
    </a>
  </footer>
</div>

</body>
</html>
