@extends('errors.layout')

@section('title', 'Autenticação Necessária')

@section('badge_classes', 'border-[var(--mint)]/30 bg-[var(--mint)]/10 text-[var(--teal-text)]')
@section('badge_text', 'Erro 401 • Não Autenticado')

@section('illustration')
<div class="relative flex items-center justify-center w-32 h-32 sm:w-36 sm:h-36">
    <!-- Pulso e halo luminoso externo esmeralda -->
    <div class="absolute inset-0 rounded-full bg-[radial-gradient(circle,rgba(45,193,151,0.22)_0%,transparent_70%)] animate-pulse"></div>
    <div class="absolute -inset-1 rounded-full border border-[var(--mint)]/20 border-dashed animate-spin" style="animation-duration: 25s;"></div>
    <div class="absolute inset-2 rounded-full border border-[var(--mint)]/15"></div>

    <!-- Container Central Estilizado -->
    <div class="relative flex h-24 w-24 items-center justify-center rounded-3xl border border-[var(--mint)]/40 bg-[var(--card-2)] shadow-[0_12px_36px_rgba(45,193,151,0.18)]">
        <svg class="h-12 w-12 text-[var(--mint)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <!-- Usuário com chave/cadeado institucional -->
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
        </svg>
    </div>

    <!-- Chip orbital flutuante com código 401 -->
    <span class="absolute -bottom-1 -right-1 flex h-8 items-center justify-center rounded-xl border border-[var(--mint)]/40 bg-[var(--side-3)] px-2.5 text-xs font-extrabold text-[var(--mint)] shadow-[0_4px_12px_rgba(0,0,0,0.6)]">
        401
    </span>
</div>
@endsection

@section('message')
Para acessar este recurso, realizar assinaturas eletrônicas ou tramitar processos no Licicon, é obrigatório estar autenticado com seu usuário institucional.
@endsection

@section('hint', 'Dica: Entre com suas credenciais de acesso para prosseguir com segurança.')

@section('actions')
<a href="{{ route('login') }}" class="btn-licicon-primary">
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
    <span>Fazer Login</span>
</a>

<a href="{{ url('/') }}" class="btn-licicon-secondary">
    <svg class="h-4 w-4 text-[var(--ink-3)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    <span>Página Inicial</span>
</a>
@endsection
