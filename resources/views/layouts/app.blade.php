<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>GestGov</title>

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

    <style>
        :root {
            --primary-dark: #1a3c46;
            --primary: #1D9698;
            --primary-light: #4fb6b8;
            --primary-extra-light: #e6f4f4;
            --accent: #ff6b35;
            --accent-light: #ffe0d4;
            --text-dark: #1e2a32;
            --text-light: #64748b;
            --background: #f8fafc;
            --card-bg: #ffffff;
            --sidebar-width: 280px;
            --header-height: 80px;
            --border-radius: 12px;
            --border-radius-lg: 16px;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: var(--background);
            color: var(--text-dark);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Layout principal */
        .app-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar melhorada */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.5rem 1rem;
            position: fixed;
            height: 100vh;
            z-index: 100;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
            overflow-y: auto;
        }

        .sidebar-logo {
            padding: 1.2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            display: flex;
            justify-content: center;
            transition: var(--transition);
        }

        .sidebar-logo:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar-logo img {
            max-width: 180px;
            height: auto;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.85rem 1.2rem;
            border-radius: var(--border-radius);
            margin-bottom: 0.5rem;
            transition: var(--transition);
            color: white;
            text-decoration: none;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--accent);
            opacity: 0;
            transition: var(--transition);
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: translateX(5px);
        }

        .nav-item:hover::before {
            opacity: 1;
        }

        .nav-item.active {
            background: white;
            color: var(--primary-dark);
            box-shadow: var(--shadow);
        }

        .nav-item.active::before {
            opacity: 1;
        }

        .nav-icon {
            width: 1.35rem;
            height: 1.35rem;
            margin-right: 0.75rem;
            opacity: 0.85;
        }

        .nav-item.active .nav-icon {
            opacity: 1;
            color: var(--primary);
        }

        .nav-section-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1.5rem 1.2rem 0.5rem;
            margin-top: 0.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            color: rgba(255, 255, 255, 0.7);
        }

        .sidebar-footer {
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
        }

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
            background: white;
            color: var(--primary-dark);
            border: none;
            box-shadow: var(--shadow);
        }

        .btn-logout:hover {
            background: #f1f1f1;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-profile {
            background: transparent;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-profile:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        /* Conteúdo principal */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        /* Header melhorado */
        .main-header {
            background: var(--card-bg);
            padding: 0 2rem;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: var(--header-height);
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            position: relative;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .notification-bell {
            position: relative;
            cursor: pointer;
            color: var(--text-light);
            transition: var(--transition);
        }

        .notification-bell:hover {
            color: var(--primary);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--accent);
            color: white;
            font-size: 0.7rem;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-menu {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 0.4rem;
            border-radius: 50%;
            background: var(--primary-extra-light);
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .user-menu:hover {
            background: var(--primary-light);
            border-color: var(--primary);
        }

        .user-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Conteúdo da página */
        .page-content {
            padding: 2rem;
            flex: 1;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
        }

        .welcome-banner {
            background: linear-gradient(120deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-text h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .welcome-text p {
            opacity: 0.9;
            max-width: 600px;
        }

        .welcome-icon {
            font-size: 3.5rem;
            opacity: 0.8;
        }

        .card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-3px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .card-actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Melhorias para o mapa */
        #map {
            height: 400px;
            width: 100%;
            border-radius: var(--border-radius);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        /* Menu mobile */
        .mobile-menu-btn {
            display: none;
            background: var(--primary-extra-light);
            border: none;
            color: var(--primary);
            font-size: 1.5rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .mobile-menu-btn:hover {
            background: var(--primary-light);
            transform: scale(1.05);
        }

        /* Botões e elementos de ação */
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 148, 150, 0.2);
        }

        .btn-accent {
            background: var(--accent);
            color: white;
        }

        .btn-accent:hover {
            background: #e55a2b;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(255, 107, 53, 0.2);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary-extra-light);
            transform: translateY(-2px);
        }

        /* Responsividade */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                width: 260px;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: flex;
            }
        }

        @media (max-width: 768px) {
            .main-header {
                padding: 0 1rem;
            }

            .page-content {
                padding: 1rem;
            }

            #map {
                height: 300px;
            }

            .header-actions {
                gap: 1rem;
            }

            .welcome-banner {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .welcome-icon {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.25rem;
            }

            .card {
                padding: 1.25rem;
            }

            .welcome-text h2 {
                font-size: 1.5rem;
            }
        }

        /* Animações suaves */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        .slide-in {
            animation: slideIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(-20px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Estado de carregamento */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: var(--border-radius);
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }
    </style>
</head>

<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div>
                <!-- Logo -->
                <div class="sidebar-logo">
                    <img src="{{ url('logo/logo_gestgov.png') }}" alt="LOGO GESTGOV">
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
