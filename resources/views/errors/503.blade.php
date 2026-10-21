@extends('errors.layout')

@section('title', 'Sistema em Manutenção')

@section('badge_classes', 'border-[var(--info)]/30 bg-[var(--info)]/10 text-[var(--info)]')
@section('badge_text', 'Status 503 • Manutenção')

@section('illustration')
<div class="relative flex items-center justify-center w-32 h-32 sm:w-36 sm:h-36">
    <!-- Pulso e halo luminoso externo azul suave -->
    <div class="absolute inset-0 rounded-full bg-[radial-gradient(circle,rgba(147,197,253,0.22)_0%,transparent_70%)] animate-pulse"></div>
    <div class="absolute -inset-1 rounded-full border border-[var(--info)]/20 border-dashed animate-spin" style="animation-duration: 25s;"></div>
    <div class="absolute inset-2 rounded-full border border-[var(--info)]/15"></div>

    <!-- Container Central Estilizado -->
    <div class="relative flex h-24 w-24 items-center justify-center rounded-3xl border border-[var(--info)]/40 bg-[var(--card-2)] shadow-[0_12px_36px_rgba(147,197,253,0.18)]">
        <svg class="h-12 w-12 text-[var(--info)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <!-- Nuvem com engrenagem/chave de manutenção -->
            <path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"></path>
            <path d="m14 15 2 2 4-4"></path>
        </svg>
    </div>

    <!-- Chip orbital flutuante com código 503 -->
    <span class="absolute -bottom-1 -right-1 flex h-8 items-center justify-center rounded-xl border border-[var(--info)]/40 bg-[var(--side-3)] px-2.5 text-xs font-extrabold text-[var(--info)] shadow-[0_4px_12px_rgba(0,0,0,0.6)]">
        503
    </span>
</div>
@endsection

@section('message')
Estamos realizando manutenções preventivas e melhorias de performance na plataforma Licicon. Os serviços serão normalizados em instantes.
@endsection

@section('hint', 'Dica: As atualizações programadas costumam levar apenas alguns minutos. Clique abaixo para verificar.')

@section('actions')
<button type="button" onclick="window.location.reload()" class="btn-licicon-primary">
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
    <span>Verificar Disponibilidade</span>
</button>

<a href="https://wa.me/558988060983" target="_blank" rel="noopener noreferrer" class="btn-licicon-secondary">
    <svg class="h-4 w-4 text-[var(--mint)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
    <span>Acompanhar com o Suporte</span>
</a>
@endsection
