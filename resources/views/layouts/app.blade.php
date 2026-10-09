<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Licicon</title>
    <link rel="icon" href="{{ asset('logo/minilogo-g-app-escuro-1024.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script src="https://unpkg.com/imask"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        [x-cloak] { display: none !important; }
        .fade-in { animation: fadeIn 0.5s ease-in-out; }
        .slide-in { animation: slideIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { transform: translateX(-20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
    <script>
        // Alpine não aceita try/catch dentro de expressões inline (@click, x-init),
        // então o acesso ao localStorage fica isolado aqui.
        function sigGetSidebarCollapsed() {
            try { return localStorage.getItem('sig-sidebar-collapsed') === '1'; } catch (e) { return false; }
        }
        function sigSetSidebarCollapsed(value) {
            try { localStorage.setItem('sig-sidebar-collapsed', value ? '1' : '0'); } catch (e) {}
        }
    </script>
</head>

<body class="bg-[#f3f6f6] font-['Plus_Jakarta_Sans'] text-slate-900 antialiased"
      x-data="{ sidebarOpen: false, collapsed: false, isDesktop: window.matchMedia('(min-width: 1024px)').matches }"
      x-init="
          collapsed = sigGetSidebarCollapsed();
          window.matchMedia('(min-width: 1024px)').addEventListener('change', (e) => { isDesktop = e.matches });
      "
      @keydown.escape.window="sidebarOpen = false">

    @php
        $navItemBase = 'flex h-11 w-full items-center gap-3 rounded-xl px-3 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#5eead4] group-[.is-collapsed]/sb:justify-center group-[.is-collapsed]/sb:px-0';
        $navItemInactive = 'text-white/80 hover:bg-white/[.07] hover:text-white';
        $navItemActive = 'bg-[#2DC197] text-[#06302d] shadow-[0_6px_16px_rgba(0,0,0,.25)] hover:bg-[#4bd2aa]';

        $toggleBase = 'flex h-11 w-full items-center gap-3 rounded-xl px-3 text-left text-sm font-semibold transition hover:bg-white/[.07] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#5eead4] group-[.is-collapsed]/sb:justify-center group-[.is-collapsed]/sb:px-0';

        $subItemBase = 'group/sub flex h-10 w-full items-center gap-2.5 rounded-lg px-3 text-[13.5px] font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#5eead4]';
        $subItemDot = '<i class="h-1.5 w-1.5 shrink-0 rounded-full bg-white/30 transition group-hover/sub:bg-[#5eead4]"></i>';
        $subItemInactive = 'text-white/70 hover:bg-white/[.07] hover:text-white';
        $subItemActive = 'bg-[#2DC197] text-[#06302d] font-semibold shadow-[0_4px_12px_rgba(0,0,0,.2)]';

        $iconAtivo = 'h-5 w-5 shrink-0 text-[#06302d]';
        $iconInativo = 'h-5 w-5 shrink-0 text-[#2DC197]';
        $almoxAtivo = request()->is('almoxarifado*');

        $navItemLocked = 'flex h-11 w-full cursor-not-allowed items-center gap-3 rounded-xl px-3 text-left text-sm font-semibold text-white/40 transition hover:bg-white/[.04] hover:text-white/60 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#5eead4] group-[.is-collapsed]/sb:justify-center group-[.is-collapsed]/sb:px-0';
        $iconLocked = 'h-5 w-5 shrink-0 text-[#2DC197]/35 transition group-hover/lk:text-[#2DC197]/50';
    @endphp

    <div class="flex min-h-screen">

        {{-- Fundo escuro do menu no celular --}}
        <div x-show="sidebarOpen"
             x-cloak
             x-transition.opacity
             @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>

        {{-- ===================== MENU LATERAL ===================== --}}
        <aside id="sidebar"
               class="group/sb fixed inset-y-0 left-0 z-40 flex w-[272px] max-w-[85vw] flex-col gap-2 overflow-y-auto bg-[linear-gradient(180deg,#0b3b37_0%,#072926_55%,#051e1c_100%)] px-3.5 pb-4 pt-5 transition-[width,transform] duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 lg:[&.is-collapsed]:w-[84px]"
               :class="{ '-translate-x-full': !sidebarOpen, 'is-collapsed': collapsed && isDesktop }">

            {{-- Topo: logo + botão recolher --}}
            <div class="mb-2 flex items-center justify-between gap-3 border-b border-white/10 pb-[18px] group-[.is-collapsed]/sb:flex-col">
                <a href="{{ route('admin.dashboard') }}" class="min-w-0 group-[.is-collapsed]/sb:hidden">
                    @if (!empty($logo) && file_exists(public_path('storage/' . $logo)))
                        <img src="{{ asset('storage/' . $logo) }}" alt="Logo do Sistema" class="block h-auto w-40">
                    @else
                        <img src="{{ asset('logo/licicon-horizontal-colorida-para-fundo-escuro.png') }}"
                             onerror="this.src='https://placehold.co/180x70?text=GestGov&bg=115e59&color=ffffff'; this.onerror=null;"
                             alt="Licicon" class="block h-auto w-40">
                    @endif
                </a>
                {{-- Não há logo "ícone" configurável por prefeitura — a marca recolhida é sempre o minilogo fixo do Licicon --}}
                <a href="{{ route('admin.dashboard') }}" class="hidden group-[.is-collapsed]/sb:block">
                    <img src="{{ asset('logo/minilogo-g-colorido-para-fundo-escuro.png') }}" alt="Licicon" class="h-9 w-9 object-contain">
                </a>
                <button type="button"
                        @click="collapsed = !collapsed; sigSetSidebarCollapsed(collapsed)"
                        :aria-label="collapsed ? 'Expandir menu' : 'Recolher menu'"
                        :title="collapsed ? 'Expandir menu' : 'Recolher menu'"
                        class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/15 text-white/85 transition hover:bg-white/10 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#5eead4] lg:flex">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/></svg>
                </button>
            </div>

            <nav aria-label="Menu principal" class="flex flex-1 flex-col gap-4">

                {{-- DASHBOARD --}}
                <div class="flex flex-col gap-1">
                    @if(auth()->user()->hasAnyRole(['diretor_licicon', 'gerente_licicon', 'colaborador_licicon']))
                        <a href="{{ route('admin.dashboard') }}" title="Dashboard"
                           @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif
                           class="{{ $navItemBase }} {{ request()->routeIs('admin.dashboard') ? $navItemActive : $navItemInactive }}">
                            <svg class="{{ request()->routeIs('admin.dashboard') ? $iconAtivo : $iconInativo }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                            <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">Dashboard</span>
                        </a>
                    @endif
                    
                    {{-- Minhas Assinaturas --}}
                    @if(auth()->user()->is_assinante)
                        @php
                            $pendentesAssinatura = \App\Models\SolicitacaoAssinatura::query()
                                ->where('assinante_user_id', auth()->id())
                                ->where('status', \App\Models\SolicitacaoAssinatura::STATUS_PENDENTE)
                                ->count();
                        @endphp
                        <a href="{{ route('minhas-assinaturas.index') }}" title="Minhas Assinaturas"
                           @if(request()->routeIs('minhas-assinaturas.*')) aria-current="page" @endif
                           class="{{ $navItemBase }} {{ request()->routeIs('minhas-assinaturas.*') ? $navItemActive : $navItemInactive }}">
                            <svg class="{{ request()->routeIs('minhas-assinaturas.*') ? $iconAtivo : $iconInativo }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">Minhas Assinaturas</span>
                            @if ($pendentesAssinatura > 0)
                                <span class="ml-auto inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold text-white bg-amber-500 rounded-full group-[.is-collapsed]/sb:hidden">
                                    {{ $pendentesAssinatura > 99 ? '99+' : $pendentesAssinatura }}
                                </span>
                            @endif
                        </a>
                    @endif
                </div>

                @auth
                    <div class="flex flex-col gap-1">
                        <p class="px-3 pb-1.5 text-[11px] font-bold uppercase tracking-[.08em] text-white/55 group-[.is-collapsed]/sb:hidden">Operação</p>
                        <div class="mx-2 mb-2 hidden h-px bg-white/10 group-[.is-collapsed]/sb:block"></div>

                        @if(auth()->user()->hasAnyRole(['diretor_licicon', 'gerente_licicon', 'colaborador_licicon']))
                            <a href="{{ route('admin.processos.index') }}" title="Processos"
                               @if(request()->routeIs('admin.processos.*')) aria-current="page" @endif
                               class="{{ $navItemBase }} {{ request()->routeIs('admin.processos.*') ? $navItemActive : $navItemInactive }}">
                                <svg class="{{ request()->routeIs('admin.processos.*') ? $iconAtivo : $iconInativo }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/></svg>
                                <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">Processos</span>
                            </a>
                        @endif

                        <a href="{{ route('admin.planejamento.index') }}" title="Planejamento"
                           @if(request()->routeIs('admin.planejamento.*')) aria-current="page" @endif
                           class="{{ $navItemBase }} {{ request()->routeIs('admin.planejamento.*') ? $navItemActive : $navItemInactive }}">
                            <svg class="{{ request()->routeIs('admin.planejamento.*') ? $iconAtivo : $iconInativo }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                            <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">Planejamento</span>
                        </a>

                        @can('atas e contratacoes')
                            <a href="{{ route('admin.atas.index') }}" title="Atas e Contratações"
                               @if(request()->routeIs('admin.atas.*')) aria-current="page" @endif
                               class="{{ $navItemBase }} {{ request()->routeIs('admin.atas.*') ? $navItemActive : $navItemInactive }}">
                                <svg class="{{ request()->routeIs('admin.atas.*') ? $iconAtivo : $iconInativo }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 12h-5"/><path d="M15 8h-5"/><path d="M19 17V5a2 2 0 0 0-2-2H4"/><path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/></svg>
                                <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">Atas e Contratações</span>
                            </a>
                        @else
                            <a href="#" data-locked="Módulo de Atas e Contratações bloqueado no seu plano" data-tip="1"
                               class="{{ $navItemBase }} text-white/40 cursor-not-allowed hover:bg-transparent hover:text-white/40">
                                <svg class="h-5 w-5 shrink-0 text-white/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 12h-5"/><path d="M15 8h-5"/><path d="M19 17V5a2 2 0 0 0-2-2H4"/><path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/></svg>
                                <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">Atas e Contratações</span>
                                <div class="ml-auto inline-flex items-center justify-center group-[.is-collapsed]/sb:hidden"><i class="fas fa-lock text-xs text-white/30"></i></div>
                            </a>
                        @endcan

                        @if(auth()->user()->hasAnyRole(['diretor_licicon', 'gerente_licicon', 'colaborador_licicon', 'prefeitura']))
                            @php
                                $etpMenuActive = request()->routeIs('admin.etps.*') || request()->routeIs('admin.etps_recebidos.*') || request()->routeIs('admin.etp_itens.*');
                            @endphp
                        @can('etp inteligente')
                            <div x-data="{ open: {{ $etpMenuActive ? 'true' : 'false' }} }" class="group/lk relative flex flex-col">
                                <button type="button"
                                        @click="if (collapsed && isDesktop) { collapsed = false; sigSetSidebarCollapsed(false) }; open = !open"
                                        :aria-expanded="open"
                                        title="ETP Inteligente"
                                        class="{{ $toggleBase }} {{ $etpMenuActive ? 'text-white' : 'text-white/80' }}">
                                    <svg class="h-5 w-5 shrink-0 {{ $etpMenuActive ? 'text-[#5eead4]' : 'text-[#2DC197]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2Z"/><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2Z"/></svg>
                                    <span class="flex-1 whitespace-nowrap text-left group-[.is-collapsed]/sb:hidden">ETP Inteligente</span>
                                    <i class="fas fa-chevron-down text-xs text-white/50 transition-transform group-[.is-collapsed]/sb:hidden" :class="{ 'rotate-180': open }"></i>
                                </button>
                                <div x-show="open" x-collapse x-cloak class="mb-1.5 ml-5 mt-1 flex flex-col gap-0.5 border-l-2 border-white/15 pl-2 group-[.is-collapsed]/sb:hidden">
                                    <a href="{{ route('admin.etps.index') }}"
                                       class="{{ $subItemBase }} {{ (request()->routeIs('admin.etps.*') && !request()->routeIs('admin.etps_recebidos.*') && !request()->routeIs('admin.etp_itens.*')) ? $subItemActive : $subItemInactive }}">
                                        {!! $subItemDot !!}
                                        <span>Solicitar ETP</span>
                                    </a>
                                    <a href="{{ route('admin.etp_itens.index') }}"
                                       class="{{ $subItemBase }} {{ request()->routeIs('admin.etp_itens.*') ? $subItemActive : $subItemInactive }}">
                                        {!! $subItemDot !!}
                                        <span>Itens ETP</span>
                                    </a>
                                    @if(auth()->user()->hasAnyRole(['diretor_licicon', 'gerente_licicon', 'colaborador_licicon']))
                                        <a href="{{ route('admin.etps_recebidos.index') }}"
                                           class="{{ $subItemBase }} {{ request()->routeIs('admin.etps_recebidos.*') ? $subItemActive : $subItemInactive }}">
                                            {!! $subItemDot !!}
                                            <span>ETPs Recebidos</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @else
                            <a href="#" data-locked="Módulo de ETP Inteligente bloqueado no seu plano" data-tip="1"
                               class="{{ $navItemBase }} text-white/40 cursor-not-allowed hover:bg-transparent hover:text-white/40">
                                <svg class="h-5 w-5 shrink-0 text-white/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2Z"/><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2Z"/></svg>
                                <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">ETP Inteligente</span>
                                <div class="ml-auto inline-flex items-center justify-center group-[.is-collapsed]/sb:hidden"><i class="fas fa-lock text-xs text-white/30"></i></div>
                            </a>
                        @endcan
                        @endif

                        <a href="{{ route('admin.solicitacoes.index') }}" title="Solicitações"
                           @if(request()->routeIs('admin.solicitacoes.*')) aria-current="page" @endif
                           class="{{ $navItemBase }} {{ request()->routeIs('admin.solicitacoes.*') ? $navItemActive : $navItemInactive }}">
                            <svg class="{{ request()->routeIs('admin.solicitacoes.*') ? $iconAtivo : $iconInativo }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/></svg>
                            <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">Solicitações</span>
                        </a>

                        @can('pca')
                            <a href="{{ route('admin.pcas.index') }}" title="PCA"
                               @if(request()->routeIs('admin.pcas.*')) aria-current="page" @endif
                               class="{{ $navItemBase }} {{ request()->routeIs('admin.pcas.*') ? $navItemActive : $navItemInactive }}">
                                <svg class="{{ request()->routeIs('admin.pcas.*') ? $iconAtivo : $iconInativo }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                                <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">PCA</span>
                            </a>
                        @else
                            <a href="#" data-locked="Módulo de PCA bloqueado no seu plano" data-tip="1"
                               class="{{ $navItemBase }} text-white/40 cursor-not-allowed hover:bg-transparent hover:text-white/40">
                                <svg class="h-5 w-5 shrink-0 text-white/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                                <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">PCA</span>
                                <div class="ml-auto inline-flex items-center justify-center group-[.is-collapsed]/sb:hidden"><i class="fas fa-lock text-xs text-white/30"></i></div>
                            </a>
                        @endcan

                        @can('contratos')
                            <a href="{{ route('admin.contratos.index') }}" title="Contratos"
                               @if(request()->routeIs('admin.contratos.*')) aria-current="page" @endif
                               class="{{ $navItemBase }} {{ request()->routeIs('admin.contratos.*') ? $navItemActive : $navItemInactive }}">
                                <svg class="{{ request()->routeIs('admin.contratos.*') ? $iconAtivo : $iconInativo }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                                <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">Contratos</span>
                            </a>
                        @else
                            <a href="#" data-locked="Módulo de Contratos bloqueado no seu plano" data-tip="1"
                               class="{{ $navItemBase }} text-white/40 cursor-not-allowed hover:bg-transparent hover:text-white/40">
                                <svg class="h-5 w-5 shrink-0 text-white/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                                <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">Contratos</span>
                                <div class="ml-auto inline-flex items-center justify-center group-[.is-collapsed]/sb:hidden"><i class="fas fa-lock text-xs text-white/30"></i></div>
                            </a>
                        @endcan

                        @can('fiscalizar contratos')
                            <a href="{{ route('admin.fiscalizacoes.index') }}" title="Fiscalização"
                               @if(request()->routeIs('admin.fiscalizacoes.*')) aria-current="page" @endif
                               class="{{ $navItemBase }} {{ request()->routeIs('admin.fiscalizacoes.*') ? $navItemActive : $navItemInactive }}">
                                <svg class="{{ request()->routeIs('admin.fiscalizacoes.*') ? $iconAtivo : $iconInativo }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="m9 14 2 2 4-4"/></svg>
                                <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">Fiscalização</span>
                            </a>
                        @else
                            <a href="#" data-locked="Módulo de Fiscalização bloqueado no seu plano" data-tip="1"
                               class="{{ $navItemBase }} text-white/40 cursor-not-allowed hover:bg-transparent hover:text-white/40">
                                <svg class="h-5 w-5 shrink-0 text-white/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="m9 14 2 2 4-4"/></svg>
                                <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">Fiscalização</span>
                                <div class="ml-auto inline-flex items-center justify-center group-[.is-collapsed]/sb:hidden"><i class="fas fa-lock text-xs text-white/30"></i></div>
                            </a>
                        @endcan
                    </div>

                    @if(auth()->user()->hasAnyRole(['diretor_licicon', 'gerente_licicon']))
                        <div class="flex flex-col gap-1">
                            <p class="px-3 pb-1.5 pt-3 text-[11px] font-bold uppercase tracking-[.08em] text-white/55 group-[.is-collapsed]/sb:hidden">Administração</p>
                            <div class="mx-2 mb-2 hidden h-px bg-white/10 group-[.is-collapsed]/sb:block"></div>

                            <a href="{{ route('admin.prefeituras.index') }}" title="Prefeituras"
                               @if(request()->routeIs('admin.prefeituras.*')) aria-current="page" @endif
                               class="{{ $navItemBase }} {{ request()->routeIs('admin.prefeituras.*') ? $navItemActive : $navItemInactive }}">
                                <svg class="{{ request()->routeIs('admin.prefeituras.*') ? $iconAtivo : $iconInativo }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"/><path d="M9 8h1"/><path d="M9 12h1"/><path d="M9 16h1"/><path d="M14 8h1"/><path d="M14 12h1"/><path d="M14 16h1"/><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/></svg>
                                <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">Prefeituras</span>
                            </a>

                            <a href="{{ route('admin.usuarios.index') }}" title="Usuários"
                               @if(request()->routeIs('admin.usuarios.*')) aria-current="page" @endif
                               class="{{ $navItemBase }} {{ request()->routeIs('admin.usuarios.*') ? $navItemActive : $navItemInactive }}">
                                <svg class="{{ request()->routeIs('admin.usuarios.*') ? $iconAtivo : $iconInativo }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">Usuários</span>
                            </a>

                            <a href="{{ route('admin.assinantes.index') }}" title="Assinantes"
                               @if(request()->routeIs('admin.assinantes.*')) aria-current="page" @endif
                               class="{{ $navItemBase }} {{ request()->routeIs('admin.assinantes.*') ? $navItemActive : $navItemInactive }}">
                                <svg class="{{ request()->routeIs('admin.assinantes.*') ? $iconAtivo : $iconInativo }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                <span class="flex-1 whitespace-nowrap group-[.is-collapsed]/sb:hidden">Assinantes</span>
                            </a>
                        </div>
                    @endif
                @endauth
            </nav>

            {{-- Rodapé: usuário, Perfil e Sair --}}
            @auth
            <div class="mt-3 flex flex-col gap-3 rounded-[14px] border border-white/10 bg-white/[.07] p-3 group-[.is-collapsed]/sb:px-1 group-[.is-collapsed]/sb:py-2">
                <div class="flex items-center gap-2.5 group-[.is-collapsed]/sb:justify-center">
                    <div class="flex h-[38px] w-[38px] shrink-0 items-center justify-center rounded-full bg-[#5eead4]/20 text-[#5eead4]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div class="min-w-0 group-[.is-collapsed]/sb:hidden">
                        <div class="truncate text-sm font-semibold text-white">{{ Auth::user()->name }}</div>
                    </div>
                </div>
                <div class="flex gap-2 group-[.is-collapsed]/sb:flex-col">
                    @can('perfil')
                        <a href="{{ route('profile.edit') }}" title="Meu perfil" aria-label="Meu perfil"
                           class="flex min-h-11 flex-1 items-center justify-center gap-2 rounded-[10px] border border-white/15 text-[13px] font-semibold text-white/85 transition hover:bg-white/10 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#5eead4]">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span class="group-[.is-collapsed]/sb:hidden">Perfil</span>
                        </a>
                    @endcan
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" title="Sair" aria-label="Sair"
                                class="flex min-h-11 w-full items-center justify-center gap-2 rounded-[10px] border border-white/15 text-[13px] font-semibold text-white/85 transition hover:bg-white/10 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#5eead4]">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                            <span class="group-[.is-collapsed]/sb:hidden">Sair</span>
                        </button>
                    </form>
                </div>
            </div>
            @endauth
        </aside>
        {{-- =================== FIM DO MENU LATERAL =================== --}}

        <div class="flex min-w-0 flex-1 flex-col">
            <main class="flex min-w-0 flex-1 flex-col gap-7 px-4 py-6 fade-in sm:px-10 sm:py-8">

                {{-- Botão do menu (só no celular) --}}
                <div class="lg:hidden">
                    <button type="button" @click="sidebarOpen = true" aria-label="Abrir menu" aria-controls="sidebar"
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-slate-700 shadow-sm ring-1 ring-slate-900/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>

                <section class="slide-in flex flex-col items-center gap-6 rounded-[20px] bg-[linear-gradient(110deg,#0b3b37_0%,#072926_55%,#051e1c_100%)] p-6 text-center text-white shadow-[0_8px_24px_rgba(5,30,28,.25)] sm:flex-row sm:items-center sm:justify-between sm:p-10 sm:text-left">
                    <div class="max-w-[640px]">
                        <h1 class="mb-2.5 text-2xl font-bold tracking-tight sm:text-[30px]">@yield('page-title', 'Olá, ' . (auth()->user()->name ?? 'Administrador') . '!')</h1>
                        <p class="text-[15px] font-medium leading-relaxed opacity-95">@yield('page-subtitle', 'Bem-vindo ao ' . ($systemName ?? ''))</p>
                    </div>
                    <div class="relative hidden h-16 w-16 shrink-0 items-center justify-center rounded-[18px] bg-white/[.07] sm:flex">
                        <i class="fas fa-@yield('page-icon', 'building-circle-check') text-[28px] text-[#2DC197]"></i>
                    </div>
                </section>

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')

    <!-- Aviso ao passar o mouse sobre item bloqueado do menu -->
    <div id="lock-tip" role="tooltip"
         class="pointer-events-none fixed z-50 hidden w-[250px] -translate-y-1/2 rounded-xl bg-slate-900 px-3.5 py-3 text-white opacity-0 shadow-[0_16px_40px_rgba(0,0,0,.35),0_0_0_1px_rgba(255,255,255,.08)] transition-opacity duration-150 lg:block">
      <span class="absolute -left-[5px] top-1/2 h-2.5 w-2.5 -translate-y-1/2 rotate-45 bg-slate-900"></span>
      <div class="mb-1.5 flex items-center gap-2 text-[13px] font-bold text-amber-400"><svg class="h-[15px] w-[15px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Acesso restrito</div>
      <div class="text-[12.5px] leading-normal text-white/85">Você não tem permissão para acessar <b data-tip-name></b>. Solicite acesso ao administrador do sistema.</div>
    </div>

    <!-- Aviso ao clicar em item bloqueado -->
    <div id="toast" role="alert" aria-live="assertive"
         class="pointer-events-none fixed bottom-7 right-4 z-[60] flex w-[340px] max-w-[calc(100vw-2rem)] translate-y-2 items-start gap-3 rounded-[14px] bg-slate-900 px-4 py-3.5 text-white opacity-0 shadow-[0_16px_40px_rgba(0,0,0,.35)] transition duration-200 sm:right-7">
      <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-400/15 text-amber-400"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
      <div>
        <b class="mb-0.5 block text-[13.5px]">Sem permissão para acessar</b>
        <span class="text-[12.5px] leading-normal text-white/80">Você não tem acesso a <span data-toast-name></span>. Solicite acesso ao administrador do sistema.</span>
      </div>
    </div>

    <script>
    /* Itens bloqueados: aviso ao passar o mouse/focar (menu) e ao clicar (menu e cartões) */
    (function () {
      var desktop = window.matchMedia('(min-width: 1024px)');
      var tip = document.getElementById('lock-tip');
      var tipName = tip.querySelector('[data-tip-name]');
      var toast = document.getElementById('toast');
      var toastName = toast.querySelector('[data-toast-name]');
      var timer = null;

      function showTip(el) {
        if (!desktop.matches) return;
        var r = el.getBoundingClientRect();
        tipName.textContent = el.getAttribute('data-locked');
        tip.style.top = (r.top + r.height / 2) + 'px';
        tip.style.left = (r.right + 22) + 'px';
        tip.classList.add('opacity-100'); tip.classList.remove('opacity-0');
      }
      function hideTip() {
        tip.classList.add('opacity-0'); tip.classList.remove('opacity-100');
      }
      function showToast(name) {
        toastName.textContent = name;
        toast.classList.remove('opacity-0', 'translate-y-2');
        toast.classList.add('opacity-100', 'translate-y-0');
        clearTimeout(timer);
        timer = setTimeout(function () {
          toast.classList.add('opacity-0', 'translate-y-2');
          toast.classList.remove('opacity-100', 'translate-y-0');
        }, 3500);
      }

      Array.prototype.forEach.call(document.querySelectorAll('[data-locked]'), function (el) {
        el.addEventListener('click', function () { showToast(el.getAttribute('data-locked')); });
        if (el.hasAttribute('data-tip')) {
          el.addEventListener('mouseenter', function () { showTip(el); });
          el.addEventListener('focus', function () { showTip(el); });
          el.addEventListener('mouseleave', hideTip);
          el.addEventListener('blur', hideTip);
        }
      });
      document.getElementById('sidebar').addEventListener('scroll', hideTip);
    })();
    </script>
</body>
</html>
