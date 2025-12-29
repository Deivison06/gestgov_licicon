<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Licitações - GestGov</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- CKEditor 5 -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    <!-- Ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/imask"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

       <style>
        :root {
            /* Cores e Gradiente Padronizados */
            --gradient-start: #115e59;
            --gradient-end: #0f292b;

            --primary: #2DC197;
            --background: #f8fafc;
            --sidebar-width: 300px;
            --border-radius: 12px;
            --border-radius-lg: 16px;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        [x-cloak] { display: none !important; }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: var(--background);
            color: #1e2a32;
            overflow-x: hidden;
            line-height: 1.6;
        }

        .app-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(170deg, var(--gradient-start) 0%, var(--gradient-end) 90%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.8rem 1.2rem;
            position: fixed;
            height: 100vh;
            z-index: 100;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar { display: none; }
        .sidebar { -ms-overflow-style: none; scrollbar-width: none; }

        .sidebar-logo {
            padding: 0.5rem;
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: center;
        }

        .sidebar-logo img {
            max-width: 170px;
            height: auto;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        /* Nav Items */
        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.9rem 1.2rem;
            border-radius: var(--border-radius);
            margin-bottom: 0.35rem;
            transition: var(--transition);
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(3px);
            color: white;
        }

        .nav-item.active {
            background: white !important;
            color: #0d3532 !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            font-weight: 700;
        }

        .nav-icon {
            width: 1.35rem;
            height: 1.35rem;
            margin-right: 0.85rem;
            text-align: center;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,0.1));
        }

        .nav-item.active .nav-icon {
            color: var(--gradient-start);
        }

        .nav-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 0 1.2rem;
            margin-top: 2rem;
            margin-bottom: 0.75rem;
            color: rgba(255, 255, 255, 0.5);
        }

        /* Sidebar Footer */
        .sidebar-footer { padding-top: 2rem; }

        .sidebar-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.75rem;
            border-radius: var(--border-radius);
            margin-bottom: 0.75rem;
            font-weight: 600;
            transition: var(--transition);
            cursor: pointer;
            gap: 0.5rem;
        }

        .btn-logout {
            background: rgba(255,255,255,0.95);
            color: #0f292b;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-logout:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .btn-profile {
            background: transparent;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .btn-profile:hover { background: rgba(255, 255, 255, 0.1); }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .page-content {
            padding: 2.5rem;
            flex: 1;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
        }

        /* Banner Style */
        .welcome-banner {
            background: linear-gradient(120deg, var(--gradient-start) 0%, var(--primary) 100%);
            color: white;
            border-radius: var(--border-radius-lg);
            padding: 2.5rem;
            margin-bottom: 2.5rem;
            box-shadow: var(--shadow-lg);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .welcome-text h2 { font-size: 1.8rem; margin-bottom: 0.5rem; font-weight: 700; }
        .welcome-text p { opacity: 0.95; max-width: 600px; color: #f0fdf4; }
        .welcome-icon { font-size: 3.5rem; opacity: 0.8; }

        .mobile-menu-btn {
            display: none;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            width: 40px; height: 40px;
            border-radius: 50%;
            align-items: center; justify-content: center;
            transition: var(--transition);
            margin-right: 15px;
        }
        .mobile-menu-btn:hover { background: rgba(255,255,255,0.4); }

        /* Mapa e Cards (Mantidos do original, mas com ajustes de cor) */
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.05);
        }
        .card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-3px);
        }
        #map {
            height: 400px;
            width: 100%;
            border-radius: var(--border-radius);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .mobile-menu-btn { display: flex; position: absolute; top: 1.5rem; left: 1.5rem; }
            .welcome-banner { padding-top: 5rem; text-align: center; flex-direction: column; align-items: center; }
            .welcome-icon { display: none; }
        }

        .fade-in { animation: fadeIn 0.5s ease-in-out; }
        .slide-in { animation: slideIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { transform: translateX(-20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
</head>

<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div>
                <!-- Logo -->
                <div class="sidebar-logo">
                    <a href="{{ route("admin.dashboard") }}">
                        <img src="{{ url('logo/logo_gestgov.png') }}" alt="LOGO GESTGOV">
                    </a>
                </div>

                <!-- Navegação -->
                <nav>
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>

                    <div class="nav-section-title">Conteúdo do Site</div>

                    <a href="{{ route('admin.processos.index') }}"
                        class="nav-item {{ request()->routeIs('admin.processos.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <span>PROCESSOS</span>
                    </a>

                    <a href="{{ route('admin.atas.index') }}"
                        class="nav-item {{ request()->routeIs('admin.atas.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <span>ATAS E CONTRATAÇÕES</span>
                    </a>

                    <a href="{{ route('admin.contratos.index') }}"
                        class="nav-item {{ request()->routeIs('admin.contratos.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-signature"></i>
                        <span>Contratos</span>
                    </a>

                    <a href="{{ route('admin.prefeituras.index') }}"
                        class="nav-item {{ request()->routeIs('admin.prefeituras.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-building"></i>
                        <span>PREFEITURAS</span>
                    </a>

                    <a href="{{ route('admin.usuarios.index') }}"
                        class="nav-item {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <span>USUÁRIOS</span>
                    </a>
                </nav>
            </div>

            <!-- Rodapé -->
            <div class="sidebar-footer">
                <a href="{{ route('profile.edit') }}" class="sidebar-btn btn-profile">
                    <i class="fas fa-user-circle"></i>
                    <span>Meu Perfil</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Sair</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Conteúdo Principal -->
        <div class="main-content">

            <div class="page-content fade-in">
                <!-- Banner de boas-vindas -->
                <div class="welcome-banner slide-in">
                    <div class="welcome-text">
                        <h2>@yield('page-title', 'Olá, ' . (auth()->user()->name ?? 'Administrador') . '!')</h2>
                        <p>@yield('page-subtitle', 'Bem-vindo à plataforma de administração da GestGov Consultoria e Assessoria Administrativa. Aqui você pode gerenciar todos os aspectos do sistema.')</p>
                    </div>
                    <div class="welcome-icon">
                        <i class="fas fa-building-circle-check"></i>
                    </div>
                </div>

                @yield('content')
            </div>
        </div>
    </div>

    <script>

        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('open');
        });

        // Fechar o menu ao clicar fora dele em dispositivos móveis
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');

            if (window.innerWidth <= 1024 &&
                sidebar.classList.contains('open') &&
                !sidebar.contains(event.target) &&
                !mobileMenuBtn.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
