<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>GestGov - Login</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .floating {
            animation: float 6s ease-in-out infinite;
        }

        .card-glow {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(255, 255, 255, 0.1);
        }

        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(18, 139, 141, 0.2);
        }

        .btn-hover {
            transition: all 0.3s ease;
        }

        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body class="font-sans antialiased bg-gradient-to-br from-[#093f5a] to-[#062F43] min-h-screen flex items-center justify-center p-5 relative overflow-x-hidden">
    <!-- Background pattern -->
    <div class="absolute inset-0 opacity-30"
        style="background-image: url('data:image/svg+xml,%3Csvg width=\'100\' height=\'100\' viewBox=\'0 0 100 100\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z\' fill=\'%23ffffff\' fill-opacity=\'0.05\' fill-rule=\'evenodd\'/%3E%3C/svg%3E');">
    </div>

    <!-- Floating elements for visual interest -->
    <div class="absolute w-20 h-20 bg-white rounded-full top-10 left-10 opacity-5 floating"></div>
    <div class="absolute w-16 h-16 bg-white rounded-full bottom-20 right-10 opacity-5 floating" style="animation-delay: 2s;"></div>
    <div class="absolute w-12 h-12 bg-white rounded-full top-1/3 right-1/4 opacity-5 floating" style="animation-delay: 4s;"></div>

    <div class="relative z-10 w-full max-w-md">
        <div
            class="bg-gradient-to-br from-[#128b8d] to-[#1D9698] rounded-2xl shadow-2xl overflow-hidden transition-all duration-300 relative card-glow">
            <!-- Efeito de brilho no card -->
            <div
                class="absolute -inset-48 bg-radial-circle bg-[radial-gradient(circle,_rgba(255,255,255,0.15)_0%,_transparent_60%)] rotate-30 -z-0">
            </div>

            <!-- Logo container -->
            <div class="relative z-10 flex justify-center p-8 bg-gradient-to-b from-white/10 to-transparent rounded-t-2xl">
                <a href="/" class="transition-all duration-300 hover:scale-105">
                    <img src="{{ url('logo/logo_gestgov.png') }}" alt="LOGO GESTGOV" class="w-64 h-auto drop-shadow-md">
                </a>
            </div>

            <!-- Form container -->
            <div class="relative z-10 p-8">
                <h1 class="mb-8 text-2xl font-bold text-center text-white drop-shadow-md">Acesse sua conta</h1>

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="p-4 mb-6 text-sm text-white border bg-red-500/80 backdrop-blur-sm rounded-xl border-red-300/30">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}

                <div class="relative my-8">
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
