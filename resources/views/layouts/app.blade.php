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
            --sidebar-width: 280px;
            --sidebar-width-collapsed: 80px;
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

        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: var(--background);
            color: #1e2a32;
            overflow-x: hidden;
            line-height: 1.6;
            min-height: 100vh;
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
            padding: 1.5rem 1rem;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
            overflow-y: auto;
            left: 0;
            top: 0;
        }

        .sidebar::-webkit-scrollbar {
            display: none;
        }

        .sidebar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .sidebar-logo {
            padding: 0.5rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: center;
            transition: var(--transition);
        }

        .sidebar-logo img {
            max-width: 160px;
            height: auto;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            transition: var(--transition);
        }

        /* Nav Items */
        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.85rem 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 0.35rem;
            transition: var(--transition);
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-weight: 500;
            position: relative;
            overflow: hidden;
            white-space: nowrap;
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
            font-weight: 600;
        }

        .nav-icon {
            width: 1.35rem;
            height: 1.35rem;
            margin-right: 0.85rem;
            text-align: center;
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
            flex-shrink: 0;
        }

        .nav-item.active .nav-icon {
            color: var(--gradient-start);
        }

        .nav-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 0 1rem;
            margin-top: 2rem;
            margin-bottom: 0.75rem;
            color: rgba(255, 255, 255, 0.5);
            white-space: nowrap;
            transition: var(--transition);
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding-top: 2rem;
        }

        .sidebar-btn {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            width: 100%;
            padding: 0.75rem;
            border-radius: var(--border-radius);
            margin-bottom: 0.75rem;
            font-weight: 600;
            transition: var(--transition);
            cursor: pointer;
            gap: 0.5rem;
            white-space: nowrap;
            text-decoration: none;
            border: none;
            background: transparent;
            color: inherit;
            font-family: inherit;
            font-size: inherit;
        }

        .btn-logout {
            background: rgba(255, 255, 255, 0.95);
            color: #0f292b;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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

        .btn-profile:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
        }

        .page-content {
            padding: 2rem 0;
            flex: 1;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Banner Style */
        .welcome-banner {
            background: linear-gradient(120deg, var(--gradient-start) 0%, var(--primary) 100%);
            color: white;
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            width: 100%;
        }

        .welcome-text h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .welcome-text p {
            opacity: 0.95;
            max-width: 600px;
            color: #f0fdf4;
            font-size: 1rem;
        }

        .welcome-icon {
            font-size: 3rem;
            opacity: 0.8;
            flex-shrink: 0;
        }

        .mobile-menu-btn {
            display: none;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            position: absolute;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
        }

        .mobile-menu-btn:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .overlay.active {
            display: block;
        }

        /* Content Styling */
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
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

        /* Responsividade */
        @media (max-width: 1200px) {
            .sidebar {
                width: 250px;
            }

            .main-content {
                margin-left: 250px;
            }

            .welcome-text h2 {
                font-size: 1.6rem;
            }

            .welcome-icon {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
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

            .welcome-banner {
                padding: 1.5rem;
                padding-left: 4rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .welcome-text {
                width: 100%;
            }

            .welcome-icon {
                display: none;
            }

            .page-content {
                padding: 1.5rem 0;
            }
        }

        @media (max-width: 768px) {
            .page-content {
                padding: 1rem 0;
            }

            .welcome-banner {
                padding: 1.25rem;
                padding-left: 3.5rem;
                margin-bottom: 1.5rem;
            }

            .welcome-text h2 {
                font-size: 1.4rem;
            }

            .welcome-text p {
                font-size: 0.9rem;
            }

            .card {
                padding: 1.25rem;
                margin-bottom: 1.25rem;
            }
        }

        @media (max-width: 480px) {
            .page-content {
                padding: 0.75rem 0;
            }

            .welcome-banner {
                padding: 1rem;
                padding-left: 3rem;
                border-radius: var(--border-radius);
            }

            .welcome-text h2 {
                font-size: 1.2rem;
            }

            .welcome-text p {
                font-size: 0.85rem;
            }

            .mobile-menu-btn {
                width: 35px;
                height: 35px;
                font-size: 1.25rem;
            }

            .card {
                padding: 1rem;
                border-radius: 10px;
            }
        }

        /* Animações */
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

        /* Dashboard Styles */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #0596A2;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #64748b;
        }

        .process-list-item {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            transition: var(--transition);
        }

        .process-list-item:hover {
            background: #f8fafc;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .stat-card {
                padding: 1.25rem;
            }

            .stat-value {
                font-size: 1.75rem;
            }
        }

        @media (max-width: 480px) {
            .dashboard-grid {
                gap: 0.75rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="app-container">
        <!-- Overlay para mobile -->
        <div class="overlay" id="overlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div>
                <!-- Logo -->
                <div class="sidebar-logo">
                    <a href="{{ route('admin.dashboard') }}">
                        <img src="{{ url('logo/logo_gestgov.png') }}" alt="LOGO GESTGOV">
                    </a>
                </div>

                <!-- Navegação -->
                <nav>
                    {{-- Dashboard visível apenas para admin/diretor --}}
                    @if(auth()->user()->hasAnyRole(['diretor_licicon', 'gerente_licicon', 'colaborador_licicon']))
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    @endif

                    @if(auth()->user()->hasAnyRole(['diretor_licicon', 'gerente_licicon', 'colaborador_licicon']))
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
                    @endif

                    {{-- Contratos visível para todos --}}
                    <a href="{{ route('admin.contratos.index') }}"
                        class="nav-item {{ request()->routeIs('admin.contratos.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-signature"></i>
                        <span>Contratos</span>
                    </a>

                    {{-- Apenas admin/diretor --}}
                    @if(auth()->user()->hasAnyRole(['diretor_licicon', 'gerente_licicon']))
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
                    @endif
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
                <!-- Banner de boas-vindas personalizado por função -->
                @if(auth()->user()->hasRole('prefeitura'))
                <div class="welcome-banner slide-in">
                    <button id="mobileMenuBtn" class="mobile-menu-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="welcome-text">
                        <h2>Área da Prefeitura</h2>
                        <p>Bem-vindo, {{ auth()->user()->name }}! Aqui você pode gerenciar os contratos da sua prefeitura.
                        </p>
                    </div>
                    <div class="welcome-icon">
                        <i class="fas fa-building-shield"></i>
                    </div>
                </div>
                @else
                <div class="welcome-banner slide-in">
                    <button id="mobileMenuBtn" class="mobile-menu-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="welcome-text">
                        <h2>@yield('page-title', 'Olá, ' . (auth()->user()->name ?? 'Administrador') . '!')</h2>
                        <p>@yield('page-subtitle', 'Bem-vindo à plataforma de administração da GestGov Consultoria e Assessoria Administrativa. Aqui você pode gerenciar todos os aspectos do sistema.')</p>
                    </div>
                    <div class="welcome-icon">
                        <i class="fas fa-building-circle-check"></i>
                    </div>
                </div>
                @endif

                <!-- Conteúdo da Página -->
                @yield('content')
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            // Toggle sidebar no mobile
            if (mobileMenuBtn && sidebar) {
                mobileMenuBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('open');
                    overlay.classList.toggle('active');
                    document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
                });
            }

            // Fechar sidebar ao clicar no overlay
            if (overlay) {
                overlay.addEventListener('click', () => {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }

            // Fechar sidebar ao redimensionar para desktop
            window.addEventListener('resize', () => {
                if (window.innerWidth > 1024) {
                    if (sidebar) sidebar.classList.remove('open');
                    if (overlay) overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });

            // Fechar sidebar ao pressionar ESC
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    if (sidebar) sidebar.classList.remove('open');
                    if (overlay) overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
