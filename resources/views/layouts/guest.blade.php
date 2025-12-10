<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GestGov - Login</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            background: 
                /* Gradiente suave base */
                linear-gradient(135deg, #f8fafc 0%, #f1f5f9 25%, #e2e8f0 100%),
                /* Padrão geométrico sutil */
                repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(5, 35, 35, 0.03) 10px, rgba(5, 35, 35, 0.03) 20px),
                repeating-linear-gradient(-45deg, transparent, transparent 10px, rgba(45, 193, 151, 0.03) 10px, rgba(45, 193, 151, 0.03) 20px);
            
            /* Efeito de partículas/bolhas no fundo */
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(45, 193, 151, 0.08) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(5, 35, 35, 0.08) 0%, transparent 20%),
                radial-gradient(circle at 50% 50%, rgba(5, 50, 42, 0.05) 0%, transparent 30%);
            
            background-attachment: fixed;
            background-size: cover;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Adicionando elementos decorativos no fundo */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                /* Linhas diagonais sutis */
                linear-gradient(45deg, transparent 49.5%, rgba(5, 35, 35, 0.02) 49.5%, rgba(5, 35, 35, 0.02) 50.5%, transparent 50.5%),
                linear-gradient(-45deg, transparent 49.5%, rgba(45, 193, 151, 0.02) 49.5%, rgba(45, 193, 151, 0.02) 50.5%, transparent 50.5%);
            background-size: 60px 60px;
            pointer-events: none;
            z-index: -1;
        }
        
        body::after {
            content: '';
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80vw;
            height: 80vh;
            background: radial-gradient(circle, rgba(45, 193, 151, 0.04) 0%, transparent 70%);
            filter: blur(40px);
            pointer-events: none;
            z-index: -1;
        }
        
        /* Animações sutis para o fundo */
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(10px, -10px) rotate(1deg); }
            66% { transform: translate(-5px, 5px) rotate(-1deg); }
        }
        
        .bg-float-element {
            position: fixed;
            border: 1px solid rgba(5, 35, 35, 0.1);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(5px);
            animation: float 20s ease-in-out infinite;
            z-index: -1;
        }
        
        /* Sombra mais destacada no container */
        .shadow-xl {
            box-shadow: 
                0 20px 60px rgba(5, 35, 35, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.9);
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center">

    <!-- Elementos decorativos flutuantes no fundo -->
    <div class="bg-float-element w-40 h-40 top-1/4 left-10 opacity-20" style="animation-delay: -5s;"></div>
    <div class="bg-float-element w-64 h-64 bottom-1/4 right-10 opacity-10" style="animation-delay: -10s;"></div>
    <div class="bg-float-element w-32 h-32 top-10 right-1/4 opacity-15" style="animation-delay: -15s;"></div>
    <div class="bg-float-element w-48 h-48 bottom-10 left-1/4 opacity-10" style="animation-delay: -7s;"></div>

    <!-- Container principal com duas colunas (MANTIDO IGUAL) -->
    <div class="w-full max-w-5xl h-[550px] bg-white shadow-xl grid grid-cols-2 rounded-lg overflow-hidden">

        <!-- ============================
             COLUNA ESQUERDA (fundo escuro) - MANTIDO IGUAL
        ============================= -->
        <div class="flex flex-col items-center justify-center relative px-10 bg-[#052323]"
            style="background-image: url('{{ asset('Pattern-Login-GestGov.png') }}');
                    background-size: cover;
                    background-position: center;
                    background-repeat: no-repeat;">
            
            <!-- LOGO -->
            <img src="{{ url('logo/logo_gestgov_login.png') }}"
                class="h-24 mb-8 select-none"
                alt="GestGov">

            <!-- TEXTO DESCRITIVO -->
            <div class="bg-[#003333] text-white text-[10px] px-4 py-2 rounded-md text-center w-70">
                DESENVOLVEMOS SISTEMAS QUE ORGANIZAM,<br>
                MODERNIZAM E TORNAM A GESTÃO PÚBLICA <br>
                MAIS EFICIENTE.
            </div>
        </div>

        <!-- ============================
             COLUNA DIREITA (fundo verde) - MANTIDO IGUAL
        ============================= -->
        <div class="bg-[#2DC197] flex flex-col items-center justify-center px-14 relative">

            <!-- TÍTULO ENTRAR -->
            <h2 class="text-3xl text-[#05322A] mb-6 tracking-wide" style="font-weight: 900">
                ENTRAR
            </h2>

            <!-- FORMULÁRIO -->
            {{ $slot }}

            <!-- RODAPÉ -->
            <div class="absolute bottom-6 text-center text-[10px] text-[#05322A]">
                PRECISA DE AJUDA?<br>
                <a href="#" class="font-bold hover:underline">FALE COM A GENTE</a>
            </div>
        </div>
    </div>

</body>
</html>