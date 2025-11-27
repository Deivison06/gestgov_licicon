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
</head>

<body class="font-sans antialiased bg-gradient-to-br from-[#093f5a] to-[#062F43] min-h-screen flex items-center justify-center p-5 relative overflow-x-hidden">
    <!-- Background pattern -->
    <div class="absolute inset-0 opacity-50"
        style="background-image: url('data:image/svg+xml,%3Csvg width=\'100\' height=\'100\' viewBox=\'0 0 100 100\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z\' fill=\'%23ffffff\' fill-opacity=\'0.03\' fill-rule=\'evenodd\'/%3E%3C/svg%3E');">
    </div>

    <div class="relative z-10 w-full max-w-md">
        <div
            class="bg-gradient-to-br from-[#128b8d] to-[#1D9698] rounded-2xl shadow-xl overflow-hidden transition-all duration-300 relative">
            <!-- Efeito de brilho no card -->
            <div
                class="absolute -inset-48 bg-radial-circle bg-[radial-gradient(circle,_rgba(255,255,255,0.1)_0%,_transparent_60%)] rotate-30 -z-0">
            </div>

            <!-- Logo container -->
            <div class="relative z-10 flex justify-center pt-6 pb-1 mt-4 bg-opacity-95 rounded-t-2xl">
                <a href="/">
                    @if (!empty($logo) && file_exists(public_path('storage/' . $logo)))
                       <img src="{{ url('logo/logo_gestgov.png') }}" alt="Logo do Sistema" class="object-contain w-auto h-20">
                    @else
                        <img src="{{ url('logo/logo_gestgov.png') }}" alt="Logo Padrão" class="object-contain w-auto h-20">
                    @endif
                </a>
            </div>


            <!-- Form container -->
            <div class="relative z-10 p-8">
                <h1 class="mb-6 text-2xl font-semibold text-center text-white drop-shadow-md">Acesse sua conta</h1>

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="p-3 mb-4 text-sm text-red-100 bg-red-500 rounded-lg">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}

                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-white border-opacity-20"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
