@extends('errors.layout')

@section('title', 'Limite de Requisições')

@section('badge_classes', 'border-[var(--vio)]/30 bg-[var(--vio)]/10 text-[var(--vio)]')
@section('badge_text', 'Erro 429 • Taxa Excedida')

@section('illustration')
<div class="relative flex items-center justify-center w-32 h-32 sm:w-36 sm:h-36">
    <!-- Pulso e halo luminoso externo violeta -->
    <div class="absolute inset-0 rounded-full bg-[radial-gradient(circle,rgba(196,181,253,0.22)_0%,transparent_70%)] animate-pulse"></div>
    <div class="absolute -inset-1 rounded-full border border-[var(--vio)]/20 border-dashed animate-spin" style="animation-duration: 25s;"></div>
    <div class="absolute inset-2 rounded-full border border-[var(--vio)]/15"></div>

    <!-- Container Central Estilizado -->
    <div class="relative flex h-24 w-24 items-center justify-center rounded-3xl border border-[var(--vio)]/40 bg-[var(--card-2)] shadow-[0_12px_36px_rgba(196,181,253,0.18)]">
        <svg class="h-12 w-12 text-[var(--vio)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <!-- Velocímetro / Indicador de Taxa -->
            <path d="m12 14 4-4"></path>
            <path d="M3.34 19a10 10 0 1 1 17.32 0"></path>
            <circle cx="12" cy="14" r="2"></circle>
        </svg>
    </div>

    <!-- Chip orbital flutuante com código 429 -->
    <span class="absolute -bottom-1 -right-1 flex h-8 items-center justify-center rounded-xl border border-[var(--vio)]/40 bg-[var(--side-3)] px-2.5 text-xs font-extrabold text-[var(--vio)] shadow-[0_4px_12px_rgba(0,0,0,0.6)]">
        429
    </span>
</div>
@endsection

@section('message')
Identificamos um volume elevado de requisições simultâneas em um intervalo curto (ex.: geração de IA ou consultas consecutivas). Para preservar o sistema, o tráfego foi pausado temporariamente.
@endsection

@section('hint', 'Dica: Aguarde cerca de 30 segundos antes de efetuar uma nova solicitação.')

@section('actions')
<button type="button" onclick="window.location.reload()" class="btn-licicon-primary">
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
    <span>Recarregar Página</span>
</button>

@if(auth()->check())
    <a href="{{ route('admin.dashboard') }}" class="btn-licicon-secondary">
        <svg class="h-4 w-4 text-[var(--ink-3)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
        <span>Voltar ao Dashboard</span>
    </a>
@else
    <a href="{{ url('/') }}" class="btn-licicon-secondary">
        <svg class="h-4 w-4 text-[var(--ink-3)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <span>Página Inicial</span>
    </a>
@endif
@endsection
