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
            z-index: 2000;
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

        /* ===== ESTILOS DO SUBMENU ===== */
        .nav-item.has-submenu {
            flex-direction: column;
            align-items: flex-start;
            padding: 0;
            background: transparent;
            cursor: pointer;
            height: auto;
            overflow: visible;
        }

        .nav-link.submenu-toggle {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0.85rem 1rem;
            color: rgba(255, 255, 255, 0.85);
            transition: var(--transition);
            position: relative;
            text-decoration: none;
            cursor: pointer;
        }

        .nav-link.submenu-toggle:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(3px);
            color: white;
        }

        .nav-item.has-submenu.open .submenu-toggle {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .submenu-arrow {
            margin-left: auto;
            font-size: 0.8rem;
            transition: transform 0.3s ease;
        }

        .nav-item.has-submenu.open .submenu-arrow {
            transform: rotate(180deg);
        }

        .submenu {
            width: 100%;
            padding-left: 2.5rem;
            display: none;
            animation: slideDown 0.3s ease;
        }

        .nav-item.has-submenu.open .submenu {
            display: block !important;
        }

        .nav-subitem {
            display: flex;
            align-items: center;
            padding: 0.65rem 1rem;
            margin: 0.25rem 0;
            border-radius: var(--border-radius);
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition);
            white-space: nowrap;
        }

        .nav-subitem i {
            width: 1.35rem;
            margin-right: 0.85rem;
            font-size: 0.85rem;
            text-align: center;
        }

        .nav-subitem:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(3px);
        }

        .nav-subitem.active {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            font-weight: 500;
        }

        /* Animações */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

            .submenu {
                padding-left: 2rem;
            }

            .nav-subitem {
                padding: 0.6rem 0.8rem;
                font-size: 0.85rem;
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

    /* Top Header */
    .top-header {
        height: 70px;
        background: white;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 0 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        position: sticky;
        top: 0;
        z-index: 1000;
        width: 100%;
        margin-left: 0;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .notif-button {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        position: relative;
        transition: var(--transition);
    }

    .notif-button:hover {
        background: #f1f5f9;
        color: var(--primary);
    }

    .notif-button.active {
        color: #ef4444;
    }

    .btn-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ef4444;
        color: white;
        font-size: 0.65rem;
        font-weight: 700;
        min-width: 18px;
        height: 18px;
        padding: 0 4px;
        border-radius: 99px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid white;
        animation: pulse-red 2s infinite;
    }

    .notif-dropdown {
        position: absolute;
        top: 60px;
        right: 0;
        width: 320px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        border: 1px solid #e2e8f0;
        display: none;
        z-index: 9999;
        overflow: hidden;
        animation: slideInTop 0.3s ease;
    }

    .notif-dropdown.show {
        display: block !important;
    }

    .notif-header {
        padding: 15px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notif-header h4 {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .notif-body {
        max-height: 350px;
        overflow-y: auto;
    }

    .notif-card {
        padding: 12px 15px;
        display: flex;
        gap: 12px;
        text-decoration: none;
        color: inherit;
        border-bottom: 1px solid #f8fafc;
        transition: background 0.2s;
    }

    .notif-card:hover {
        background: #f0fdf9;
    }

    .notif-card-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ecfdf5;
        color: #10b981;
        flex-shrink: 0;
    }

    .notif-card-body h5 {
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 2px;
        color: #334155;
    }

    .notif-card-body p {
        font-size: 0.75rem;
        color: #64748b;
        line-height: 1.4;
        margin: 0;
    }

    @keyframes slideInTop {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes pulse-red {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    /* Abas internas do dropdown de notificações */
    .notif-tabs {
        display: flex;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .notif-tab-btn {
        flex: 1;
        padding: 9px 10px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #64748b;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: color 0.2s, border-color 0.2s;
    }

    .notif-tab-btn:hover {
        color: #0596a2;
    }

    .notif-tab-btn.active {
        color: #0596a2;
        border-bottom-color: #0596a2;
        background: white;
    }

    .notif-tab-badge {
        background: #ef4444;
        color: white;
        font-size: 0.6rem;
        font-weight: 700;
        min-width: 16px;
        height: 16px;
        padding: 0 4px;
        border-radius: 99px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .notif-tab-panel {
        display: none;
    }

    .notif-tab-panel.active {
        display: block;
    }

    .notif-empty {
        padding: 28px 20px;
        text-align: center;
        color: #94a3b8;
    }

    .notif-empty i {
        font-size: 1.8rem;
        margin-bottom: 8px;
        opacity: 0.25;
        display: block;
    }

    .notif-empty p {
        font-size: 0.8rem;
        margin: 0;
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
                    {{-- Dashboard visível para admin/diretor/gerente/colaborador --}}
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
                    @endif

                    @can('atas e contratacoes')
                    <a href="{{ route('admin.atas.index') }}"
                        class="nav-item {{ request()->routeIs('admin.atas.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <span>ATAS E CONTRATAÇÕES</span>
                    </a>
                    @endcan

                    {{-- ========================================= --}}
                    {{-- ETP INTELIGENTE --}}
                    {{-- ========================================= --}}

                    @if(auth()->user()->hasAnyRole([
                        'diretor_licicon',
                        'gerente_licicon',
                        'colaborador_licicon',
                        'prefeitura'
                    ]))

                    @php
                        $etpMenuActive = request()->routeIs('admin.etps.*')
                            || request()->routeIs('admin.etps_recebidos.*')
                            || request()->routeIs('admin.etp_itens.*');
                    @endphp

                    <div class="nav-item has-submenu {{ $etpMenuActive ? 'open' : '' }}" id="etpSubmenu">
                        {{-- MENU PRINCIPAL - REMOVIDO O ONCLICK --}}
                        <div class="nav-link submenu-toggle" id="etpToggle">
                            <i class="nav-icon fas fa-brain"></i>
                            <span>ETP INTELIGENTE</span>
                            <i class="fas fa-chevron-down submenu-arrow"></i>
                        </div>

                        {{-- SUBMENUS --}}
                        <div class="submenu">
                            {{-- ✅ Solicitar ETP (TODOS os roles) --}}
                            <a href="{{ route('admin.etps.index') }}"
                                class="nav-subitem {{ request()->routeIs('admin.etps.*') && !request()->routeIs('admin.etps_recebidos.*') && !request()->routeIs('admin.etp_itens.*') ? 'active' : '' }}">
                                <i class="fas fa-file-alt"></i>
                                <span>Solicitar ETP</span>
                            </a>

                            <a href="{{ route('admin.etp_itens.index') }}"
                                class="nav-subitem {{ request()->routeIs('admin.etp_itens.*') ? 'active' : '' }}">
                                <i class="fas fa-list"></i>
                                <span>Itens ETP</span>
                            </a>
                            {{-- 🔒 Apenas Diretor e Gerente --}}
                            @if(auth()->user()->hasAnyRole(['diretor_licicon', 'gerente_licicon', 'colaborador_licicon']))
                                <a href="{{ route('admin.etps_recebidos.index') }}"
                                    class="nav-subitem {{ request()->routeIs('admin.etps_recebidos.*') ? 'active' : '' }}">
                                    <i class="fas fa-inbox"></i>
                                    <span>ETPs Recebidos</span>
                                </a>

                            @endif
                        </div>

                    </div>
                     <a href="{{ route('admin.solicitacoes.index') }}"
                        class="nav-item {{ request()->routeIs('admin.solicitacoes.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-comments"></i>
                        <span>SOLICITAÇÕES</span>
                    </a>

                    <a href="{{ route('admin.pcas.index') }}"
                        class="nav-item {{ request()->routeIs('admin.pcas.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <span>PCA</span>
                    </a>

                    <a href="{{ route('admin.pesquisa_preco.index') }}"
                        class="nav-item {{ request()->routeIs('admin.pesquisa_preco.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-search-dollar"></i>
                        <span>PESQUISA DE PREÇOS</span>
                    </a>
                    @endif

                    {{-- Contratos visível para todos --}}
                    <a href="{{ route('admin.contratos.index') }}"
                        class="nav-item {{ request()->routeIs('admin.contratos.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-signature"></i>
                        <span>CONTRATOS</span>
                    </a>

                    {{-- Fiscalização de Contratos visível apenas para quem tem a permissão --}}
                    @can('fiscalizar contratos')
                        <a href="{{ route('admin.fiscalizacoes.index') }}"
                            class="nav-item {{ request()->routeIs('admin.fiscalizacoes.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-clipboard-check"></i>
                            <span>FISCALIZAÇÃO</span>
                        </a>
                    @endcan
                    {{-- Apenas admin/diretor/gerente --}}
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
            <!-- Top Header Moderno -->
            <header class="top-header">
                <div class="header-actions">
                    @auth
                        @if(auth()->user()->hasAnyRole(['diretor_licicon', 'gerente_licicon']))
                            @php
                                $notifications    = auth()->user()->unreadNotifications;
                                $notifEtp         = $notifications->filter(fn($n) => ($n->data['tipo'] ?? '') === 'etp');
                                $notifSolicitacao = $notifications->filter(fn($n) => in_array($n->data['tipo'] ?? '', ['solicitacao', 'mensagem']));
                                $notifCount       = $notifications->count();
                                $countEtp         = $notifEtp->count();
                                $countSolic       = $notifSolicitacao->count();
                            @endphp

                        <div style="position: relative;">
                            <button class="notif-button {{ $notifCount > 0 ? 'active' : '' }}" id="bellToggle">
                                <i class="fas fa-bell"></i>
                                @if($notifCount > 0)
                                    <span class="btn-badge">{{ $notifCount }}</span>
                                @endif
                            </button>

                            <div class="notif-dropdown" id="notifMenu">
                                {{-- Cabeçalho --}}
                                <div class="notif-header">
                                    <h4>Notificações</h4>
                                    @if($notifCount > 0)
                                        <span class="status-badge" style="background:#fee2e2;color:#ef4444;">{{ $notifCount }} novas</span>
                                    @endif
                                </div>

                                {{-- Abas ETP / Solicitações --}}
                                <div class="notif-tabs">
                                    <button class="notif-tab-btn active" data-tab="notif-etp">
                                        <i class="fas fa-brain"></i>
                                        ETP
                                        @if($countEtp > 0)
                                            <span class="notif-tab-badge">{{ $countEtp }}</span>
                                        @endif
                                    </button>
                                    <button class="notif-tab-btn" data-tab="notif-solic">
                                        <i class="fas fa-comments"></i>
                                        Solicitações
                                        @if($countSolic > 0)
                                            <span class="notif-tab-badge">{{ $countSolic }}</span>
                                        @endif
                                    </button>
                                </div>

                                {{-- Painel ETP --}}
                                <div class="notif-body">
                                    <div id="notif-etp" class="notif-tab-panel active">
                                        @forelse($notifEtp as $notif)
                                            <a href="{{ $notif->data['link'] }}" class="notif-card">
                                                <div class="notif-card-icon" style="background:#eff6ff;color:#3b82f6;">
                                                    <i class="fas fa-brain"></i>
                                                </div>
                                                <div class="notif-card-body">
                                                    <h5>{{ $notif->data['titulo'] }}</h5>
                                                    <p>{{ $notif->data['mensagem'] }}</p>
                                                </div>
                                            </a>
                                        @empty
                                            <div class="notif-empty">
                                                <i class="fas fa-brain"></i>
                                                <p>Nenhuma notificação de ETP.</p>
                                            </div>
                                        @endforelse
                                    </div>

                                    {{-- Painel Solicitações --}}
                                    <div id="notif-solic" class="notif-tab-panel">
                                        @forelse($notifSolicitacao as $notif)
                                            <a href="{{ $notif->data['link'] }}" class="notif-card">
                                                <div class="notif-card-icon" style="background:#f0fdf4;color:#10b981;">
                                                    <i class="fas fa-comments"></i>
                                                </div>
                                                <div class="notif-card-body">
                                                    <h5>{{ $notif->data['titulo'] }}</h5>
                                                    <p>{{ $notif->data['mensagem'] }}</p>
                                                </div>
                                            </a>
                                        @empty
                                            <div class="notif-empty">
                                                <i class="fas fa-comments"></i>
                                                <p>Nenhuma notificação de Solicitações.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        @endif

                        <div style="display: flex; align-items: center; gap: 10px; padding-left: 10px; border-left: 1px solid #e2e8f0;">
                            <div style="text-align: right;">
                                <p style="font-size: 0.85rem; font-weight: 700; color: #1e293b; line-height: 1; margin-bottom: 4px;">{{ auth()->user()->name }}</p>
                                <p style="font-size: 0.7rem; color: #64748b; margin: 0;">{{ ucfirst(str_replace('_', ' ', auth()->user()->getRoleNames()->first() ?? 'Usuário')) }}</p>
                            </div>
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=115e59&color=fff" style="width: 38px; height: 38px; border-radius: 10px; border: 2px solid #f1f5f9;">
                        </div>
                    @endauth
                </div>
            </header>

            <div class="page-content fade-in">
                <!-- Banner de boas-vindas personalizado por função -->
                @if(auth()->user()->hasRole('prefeitura'))
                <div class="welcome-banner slide-in">
                    <button id="mobileMenuBtn" class="mobile-menu-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="welcome-text">
                        <h2>Área da Prefeitura</h2>
                        <p>Bem-vindo, {{ auth()->user()->name }}! Aqui você pode gerenciar os contratos da sua prefeitura.</p>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Elementos do menu mobile
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            // Toggle sidebar no mobile
            if (mobileMenuBtn && sidebar) {
                mobileMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('open');
                    overlay.classList.toggle('active');
                    document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
                });
            }

            // Fechar sidebar ao clicar no overlay
            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }

            // Fechar sidebar ao redimensionar para desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 1024) {
                    if (sidebar) sidebar.classList.remove('open');
                    if (overlay) overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });

            // Fechar sidebar ao pressionar ESC
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    if (sidebar) sidebar.classList.remove('open');
                    if (overlay) overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });

            // LÓGICA GENÉRICA PARA SUBMENUS
            const submenuToggles = document.querySelectorAll('.submenu-toggle');
            submenuToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const parentItem = this.parentElement; // div.has-submenu
                    if (parentItem && parentItem.classList.contains('has-submenu')) {
                        const isOpen = parentItem.classList.contains('open');

                        // Fecha outros submenus
                        document.querySelectorAll('.has-submenu.open').forEach(menu => {
                            if (menu !== parentItem) {
                                menu.classList.remove('open');
                            }
                        });

                        // Alterna o atual
                        parentItem.classList.toggle('open');
                    }
                });
            });

            // Previne que o clique no submenu feche o menu
            const submenuItems = document.querySelectorAll('.submenu');
            submenuItems.forEach(submenu => {
                submenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });

            // Abre automaticamente o submenu se uma rota filha estiver ativa
            const activeSubitem = document.querySelector('.nav-subitem.active');
            if (activeSubitem) {
                const parentSubmenu = activeSubitem.closest('.has-submenu');
                if (parentSubmenu) {
                    parentSubmenu.classList.add('open');
                }
            }

            // LÓGICA DE CLIQUES (SINO E SIDEBAR)
            document.addEventListener('click', function(e) {
                // 1. Notificações: toggle dropdown
                const bell = e.target.closest('#bellToggle');
                const menu = document.getElementById('notifMenu');
                if (bell) {
                    e.preventDefault();
                    if (menu) menu.classList.toggle('show');
                    return;
                }
                if (menu && menu.classList.contains('show') && !menu.contains(e.target)) {
                    menu.classList.remove('show');
                }

                // 2. Abas internas das notificações
                const tabBtn = e.target.closest('.notif-tab-btn');
                if (tabBtn) {
                    const targetId = tabBtn.dataset.tab;
                    // Botões
                    document.querySelectorAll('.notif-tab-btn').forEach(b => b.classList.remove('active'));
                    tabBtn.classList.add('active');
                    // Painéis
                    document.querySelectorAll('.notif-tab-panel').forEach(p => p.classList.remove('active'));
                    const panel = document.getElementById(targetId);
                    if (panel) panel.classList.add('active');
                    return;
                }

                // 3. Submenus Sidebar
                const toggle = e.target.closest('.submenu-toggle');
                if (toggle) {
                    e.preventDefault();
                    const parent = toggle.closest('.has-submenu');
                    if (parent) {
                        const isOpen = parent.classList.contains('open');
                        document.querySelectorAll('.has-submenu.open').forEach(m => m.classList.remove('open'));
                        if (!isOpen) parent.classList.add('open');
                    }
                }
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
