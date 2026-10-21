@extends('errors.layout')

@section('title', 'Instabilidade no Servidor')

@section('badge_classes', 'border-[var(--bad)]/30 bg-[var(--bad)]/10 text-[var(--bad)]')
@section('badge_text', 'Erro 500 • Falha no Servidor')

@section('illustration')
<div class="relative flex items-center justify-center w-32 h-32 sm:w-36 sm:h-36">
    <!-- Pulso e halo luminoso externo coral -->
    <div class="absolute inset-0 rounded-full bg-[radial-gradient(circle,rgba(252,165,165,0.22)_0%,transparent_70%)] animate-pulse"></div>
    <div class="absolute -inset-1 rounded-full border border-[var(--bad)]/20 border-dashed animate-spin" style="animation-duration: 25s;"></div>
    <div class="absolute inset-2 rounded-full border border-[var(--bad)]/15"></div>

    <!-- Container Central Estilizado -->
    <div class="relative flex h-24 w-24 items-center justify-center rounded-3xl border border-[var(--bad)]/40 bg-[var(--card-2)] shadow-[0_12px_36px_rgba(252,165,165,0.18)]">
        <svg class="h-12 w-12 text-[var(--bad)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <!-- Servidor e Nó de Diagnóstico -->
            <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
            <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
            <line x1="6" y1="6" x2="6.01" y2="6"></line>
            <line x1="6" y1="18" x2="6.01" y2="18"></line>
            <!-- Triângulo / Alerta de Falha -->
            <path d="M16 18l2-4 2 4h-4z"></path>
        </svg>
    </div>

    <!-- Chip orbital flutuante com código 500 -->
    <span class="absolute -bottom-1 -right-1 flex h-8 items-center justify-center rounded-xl border border-[var(--bad)]/40 bg-[var(--side-3)] px-2.5 text-xs font-extrabold text-[var(--bad)] shadow-[0_4px_12px_rgba(0,0,0,0.6)]">
        500
    </span>
</div>
@endsection

@section('message')
Identificamos uma falha inesperada durante o processamento desta solicitação no servidor. Nossa equipe de engenharia já pode investigar o registro para correção.
@endsection

@section('hint', 'Dica: Muitas vezes trata-se de uma oscilação momentânea. Clique em "Tentar Novamente" para recarregar a operação.')

@section('actions')
<button type="button" onclick="window.location.reload()" class="btn-licicon-primary">
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
    <span>Tentar Novamente</span>
</button>

@if(auth()->check())
    <a href="{{ route('admin.dashboard') }}" class="btn-licicon-secondary">
        <svg class="h-4 w-4 text-[var(--ink-3)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
        <span>Ir para o Dashboard</span>
    </a>
@else
    <a href="{{ url('/') }}" class="btn-licicon-secondary">
        <svg class="h-4 w-4 text-[var(--ink-3)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <span>Página Inicial</span>
    </a>
@endif
@endsection
