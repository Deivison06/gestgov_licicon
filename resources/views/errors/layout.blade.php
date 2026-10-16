<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Erro') — Licicon</title>
    <link rel="icon" href="{{ asset('logo/minilogo-g-app-escuro-1024.png') }}">

    <!-- Tipografia Institucional: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Estilos de Resiliência (Garante renderização perfeita mesmo se o Vite estiver indisponível) -->
    <style>
        :root {
            --font: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --side-1: #0b3b37;
            --side-2: #072926;
            --side-3: #051e1c;
            --mint: #2DC197;
            --mint-h: #4bd2aa;
            --on-mint: #06302d;
            --bg: #0a1514;
            --card: #10201e;
            --card-2: #0d1b19;
            --line: #24403c;
            --line-2: #1a302d;
            --ink: #e6f0ee;
            --ink-2: #a8bdb9;
            --ink-3: #8aa29d;
            --teal-text: #5eead4;
        }

        body {
            font-family: var(--font);
            background-color: var(--side-3);
            color: var(--ink);
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        .ambient-glow {
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(720px 520px at 50% 45%, rgba(45, 193, 151, 0.16), rgba(45, 193, 151, 0) 70%);
        }

        .grid-pattern {
            position: absolute;
            inset-x: 0;
            top: 0;
            height: 45%;
            pointer-events: none;
            background-image: radial-gradient(rgba(45, 193, 151, 0.35) 1.2px, transparent 1.6px);
            background-size: 24px 24px;
            -webkit-mask-image: linear-gradient(to bottom, #000 0%, transparent 100%);
            mask-image: linear-gradient(to bottom, #000 0%, transparent 100%);
        }

        .error-card {
            width: 100%;
            max-width: 580px !important;
            margin-left: auto;
            margin-right: auto;
            background: rgba(16, 32, 30, 0.95);
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow: 0 30px 90px rgba(0, 0, 0, 0.75), 0 0 0 1px rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .btn-licicon-primary {
            background-color: var(--mint) !important;
            color: var(--on-mint) !important;
            font-weight: 700;
            border-radius: 12px;
            padding: 11px 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 16px rgba(45, 193, 151, 0.3);
            font-size: 13.5px;
            cursor: pointer;
            border: none;
        }
        .btn-licicon-primary:hover {
            background-color: var(--mint-h) !important;
            color: var(--on-mint) !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 22px rgba(45, 193, 151, 0.45);
        }

        .btn-licicon-secondary {
            background: var(--card-2);
            border: 1px solid var(--line);
            color: var(--ink);
            font-weight: 600;
            border-radius: 12px;
            padding: 11px 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 13.5px;
            cursor: pointer;
        }
        .btn-licicon-secondary:hover {
            border-color: var(--teal-text);
            color: #ffffff;
            background: #142825;
            transform: translateY(-1px);
        }

        /* Garante que o texto do botão do WhatsApp fique sempre legível com fundo branco */
        .btn-whatsapp-text {
            color: #0f172a !important;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-[#051e1c] font-['Plus_Jakarta_Sans',sans-serif] text-[#e6f0ee] antialiased min-h-screen flex flex-col justify-between selection:bg-[#2DC197] selection:text-[#06302d]">

    <!-- Fundo Ambiental com Gradiente e Malha Oficial do Licicon -->
    <div class="fixed inset-0 pointer-events-none bg-[linear-gradient(160deg,#0b3b37_0%,#072926_55%,#051e1c_100%)] z-0"></div>
    <div class="ambient-glow z-0"></div>
    <div class="grid-pattern z-0"></div>

    <!-- Conteúdo Principal -->
    <div class="relative z-10 flex min-h-screen flex-col items-center justify-between px-4 py-6 sm:px-6 sm:py-8">

        <!-- Topo: Marca e Sistema -->
        <header class="flex w-full max-w-[1100px] items-center justify-between pb-4">
            <a href="{{ auth()->check() ? route('admin.dashboard') : url('/') }}" class="inline-flex items-center gap-3 transition hover:opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#5eead4] rounded-lg">
                <img src="{{ asset('logo/licicon-horizontal-colorida-para-fundo-escuro.png') }}"
                     alt="Licicon — GestGov"
                     class="h-8 sm:h-9 w-auto">
            </a>

            @if(auth()->check())
                <div class="flex items-center gap-2.5 rounded-full border border-[#24403c] bg-[#10201e]/80 px-3.5 py-1.5 text-xs text-[#a8bdb9]">
                    <span class="h-2 w-2 rounded-full bg-[#2DC197] animate-pulse"></span>
                    <span class="font-medium text-[#e6f0ee] truncate max-w-[140px] sm:max-w-[200px]">{{ auth()->user()->name }}</span>
                </div>
            @else
                <a href="{{ route('login') }}" class="text-xs font-semibold text-[#5eead4] hover:text-[#86efac] transition flex items-center gap-1.5">
                    <span>Acessar o sistema</span>
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            @endif
        </header>

        <!-- Cartão Central de Erro -->
        <main class="my-auto flex w-full flex-col items-center justify-center py-6 sm:py-10">
            <div class="error-card p-6 sm:p-10 text-center relative overflow-hidden">

                <!-- Brilho Interno Sutil Superior -->
                <div class="pointer-events-none absolute -top-24 left-1/2 -translate-x-1/2 h-48 w-72 rounded-full bg-[radial-gradient(ellipse_at_center,rgba(45,193,151,0.22),transparent_70%)]"></div>

                <!-- Ilustração Vetorial e Ícone de Status -->
                <div class="relative mx-auto mb-6 flex items-center justify-center">
                    @yield('illustration')
                </div>

                <!-- Badge de Código de Erro -->
                <div class="mb-3.5 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold uppercase tracking-wider @yield('badge_classes', 'border-[#24403c] bg-[#0d1b19] text-[#5eead4]')">
                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                    <span>@yield('badge_text', 'Erro ' . ($exception?->getStatusCode() ?? 'HTTP'))</span>
                </div>

                <!-- Título do Erro -->
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#e6f0ee] mb-3">
                    @yield('title')
                </h1>

                <!-- Descrição Amigável -->
                <p class="text-sm sm:text-base text-[#a8bdb9] leading-relaxed max-w-[460px] mx-auto mb-6">
                    @yield('message')
                </p>

                <!-- Detalhes Específicos da Exceção (quando fornecidos pelo abort() e relevantes) -->
                @php
                    $customMsg = null;
                    if (isset($exception) && $exception instanceof \Throwable) {
                        $rawMsg = trim($exception->getMessage());
                        // Filtra mensagens padrão e genéricas do framework
                        $isGeneric = empty($rawMsg) ||
                            in_array(strtolower($rawMsg), ['not found', 'forbidden', 'unauthorized', 'too many requests', 'internal server error', 'server error', 'page expired', 'service unavailable', 'this action is unauthorized.']) ||
                            str_starts_with(strtolower($rawMsg), 'the route') ||
                            str_starts_with(strtolower($rawMsg), 'unauthenticated');

                        if (!$isGeneric) {
                            $customMsg = $rawMsg;
                        }
                    }
                @endphp

                @if(!empty($customMsg))
                    <div class="mb-6 mx-auto max-w-[460px] rounded-xl border border-[#24403c] bg-[#0d1b19]/90 px-4 py-3 text-left flex items-start gap-3">
                        <div class="mt-0.5 shrink-0 text-[#fcd34d]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="block text-[11px] font-bold uppercase tracking-wider text-[#8aa29d]">Informação Adicional</span>
                            <p class="text-xs sm:text-sm font-medium text-[#e6f0ee] break-words mt-0.5">{{ $customMsg }}</p>
                        </div>
                    </div>
                @endif

                <!-- Bloco de Dica / Ação Recomendada -->
                @hasSection('hint')
                    <div class="mb-7 mx-auto max-w-[460px] rounded-xl border border-white/5 bg-white/[0.03] px-4 py-2.5 text-xs text-[#8aa29d] flex items-center justify-center gap-2">
                        <svg class="h-4 w-4 shrink-0 text-[#2DC197]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="M2 12h4"/><path d="m4.93 19.07 2.83-2.83"/><path d="M12 22v-4"/><path d="m19.07 19.07-2.83-2.83"/><path d="M22 12h-4"/><path d="m19.07 4.93-2.83 2.83"/></svg>
                        <span>@yield('hint')</span>
                    </div>
                @endif

                <!-- Ações de Recuperação -->
                <div class="flex flex-wrap items-center justify-center gap-3 pt-1">
                    @hasSection('actions')
                        @yield('actions')
                    @else
                        @if(auth()->check())
                            <a href="{{ route('admin.dashboard') }}" class="btn-licicon-primary">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                                <span>Ir para o Dashboard</span>
                            </a>
                        @else
                            <a href="{{ url('/') }}" class="btn-licicon-primary">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                <span>Página Inicial</span>
                            </a>
                        @endif

                        <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ auth()->check() ? route('admin.dashboard') : url('/') }}'" class="btn-licicon-secondary">
                            <svg class="h-4 w-4 text-[#8aa29d]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                            <span>Voltar à Página Anterior</span>
                        </button>
                    @endif
                </div>

            </div>
        </main>

        <!-- Rodapé Institucional: GestCloud & Suporte -->
        <footer class="relative flex w-full max-w-[1100px] flex-wrap items-center justify-between gap-x-6 gap-y-4 border-t border-white/10 pt-5 z-10">
            <div class="flex max-w-[420px] flex-col gap-2">
                <div class="flex items-center gap-3">
                    <span class="whitespace-nowrap text-[11px] font-bold uppercase tracking-[.1em] text-white/50">Desenvolvido por</span>
                    <span class="h-4 w-px shrink-0 bg-white/20"></span>
                    <img src="{{ asset('logo/logo_gestcloud.png') }}"
                         alt="GestCloud"
                         class="block h-5 w-auto">
                </div>
                <p class="text-xs leading-relaxed text-white/60">
                    Sistemas inteligentes de gestão pública municipal, contratações e controle processual.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="https://wa.me/558988060983" target="_blank" rel="noopener noreferrer"
                   style="background-color: #ffffff !important; color: #0f172a !important;"
                   class="inline-flex items-center gap-2.5 rounded-full py-1.5 pl-4 pr-1.5 text-xs font-semibold shadow-[0_4px_14px_rgba(0,0,0,.3)] transition hover:opacity-90 hover:shadow-[0_6px_20px_rgba(0,0,0,.4)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#5eead4]">
                    <span style="color: #0f172a !important; font-weight: 600;">Dúvidas ou suporte? Fale conosco</span>
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#2DC197] text-[#06302d]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
                    </span>
                </a>
            </div>
        </footer>

    </div>

</body>
</html>
