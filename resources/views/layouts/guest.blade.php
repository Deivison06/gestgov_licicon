<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>GestGov - Login</title>

    <!-- Meta tags para SEO e acessibilidade -->
    <meta name="description" content="Sistema de gestão GestGov - Acesse sua conta">
    <meta name="theme-color" content="#093f5a">

    <!-- Preload de recursos críticos -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary-dark: #062F43;
            --primary: #093f5a;
            --accent: #128b8d;
            --accent-light: #1D9698;
            --white: #ffffff;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .floating {
            animation: float 6s ease-in-out infinite;
        }

        .card-glow {
            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.1),
                0 0 40px rgba(18, 139, 141, 0.1);
        }

        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(18, 139, 141, 0.2);
            border-color: var(--accent);
        }

        .btn-hover {
            transition: all 0.3s ease;
        }

        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        /* Melhorias de acessibilidade */
        @media (prefers-reduced-motion: reduce) {
            .floating, .btn-hover, .input-focus {
                animation: none;
                transition: none;
            }
        }

        /* Melhorias para modo escuro/claro do sistema */
        @media (prefers-color-scheme: dark) {
            .bg-pattern {
                opacity: 0.2;
            }
        }

        /* Melhorias de foco para acessibilidade */
        .focus-visible:focus {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gradient-to-br from-[#093f5a] to-[#062F43] min-h-screen flex items-center justify-center p-4 md:p-5 relative overflow-x-hidden">
    <!-- Background pattern com SVG otimizado -->
    <div class="absolute inset-0 opacity-30 bg-pattern"
        aria-hidden="true"
        style="background-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\'%3E%3Cpath fill=\'%23fff\' fill-opacity=\'.05\' d=\'M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z\'/%3E%3C/svg%3E');">
    </div>

    <!-- Floating elements para interesse visual -->
    <div class="absolute w-20 h-20 bg-white rounded-full top-10 left-10 opacity-5 floating" aria-hidden="true"></div>
    <div class="absolute w-16 h-16 bg-white rounded-full bottom-20 right-10 opacity-5 floating" style="animation-delay: 2s;" aria-hidden="true"></div>
    <div class="absolute w-12 h-12 bg-white rounded-full top-1/3 right-1/4 opacity-5 floating" style="animation-delay: 4s;" aria-hidden="true"></div>

    <div class="relative z-10 w-full max-w-md fade-in ">
        <div
            class="bg-gradient-to-br from-[#128b8d] to-[#1D9698] rounded-2xl shadow-2xl overflow-hidden transition-all duration-300 relative card-glow p-10">
            <!-- Efeito de brilho no card -->
            <div
                class="absolute -inset-48 bg-radial-circle bg-[radial-gradient(circle,_rgba(255,255,255,0.15)_0%,_transparent_60%)] rotate-30 -z-0"
                aria-hidden="true">
            </div>

            <!-- Logo container -->
            <div class="relative z-10 flex justify-center bg-gradient-to-b from-white/10 to-transparent rounded-t-2xl">
                <a href="/" class="transition-all duration-300 rounded-lg hover:scale-105 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-50">
                    <img src="{{ url('logo/logo_gestgov.png') }}" alt="GestGov - Sistema de Gestão" class="w-56 h-auto md:w-64 drop-shadow-md" width="256" height="80">
                </a>
            </div>

            <!-- Form container -->
            <div class="relative z-10 p-6 md:p-8">
                <h1 class="mb-6 text-xl font-bold text-center text-white md:mb-8 md:text-2xl drop-shadow-md">Acesse sua conta</h1>

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="p-4 mb-6 text-sm text-white border bg-red-500/90 backdrop-blur-sm rounded-xl border-red-300/30" role="alert">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="flex items-center">
                                    <svg class="flex-shrink-0 w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}

                <div class="relative my-6 md:my-8" aria-hidden="true">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-white/20"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-transparent text-white/70">GestGov</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer note -->
        <div class="mt-6 text-center">
            <p class="text-sm text-white/60">© {{ date('Y') }} GestGov. Todos os direitos reservados.</p>
        </div>
    </div>
</body>

</html>
