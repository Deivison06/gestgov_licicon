@extends('errors.layout')

@section('title', 'Sessão Expirada')

@section('badge_classes', 'border-[var(--warn)]/30 bg-[var(--warn)]/10 text-[var(--warn)]')
@section('badge_text', 'Erro 419 • Sessão Expirada')

@section('illustration')
<div class="relative flex items-center justify-center w-32 h-32 sm:w-36 sm:h-36">
    <!-- Pulso e halo luminoso externo âmbar -->
    <div class="absolute inset-0 rounded-full bg-[radial-gradient(circle,rgba(252,211,77,0.22)_0%,transparent_70%)] animate-pulse"></div>
    <div class="absolute -inset-1 rounded-full border border-[var(--warn)]/20 border-dashed animate-spin" style="animation-duration: 25s;"></div>
    <div class="absolute inset-2 rounded-full border border-[var(--warn)]/15"></div>

    <!-- Container Central Estilizado -->
    <div class="relative flex h-24 w-24 items-center justify-center rounded-3xl border border-[var(--warn)]/40 bg-[var(--card-2)] shadow-[0_12px_36px_rgba(252,211,77,0.18)]">
        <svg class="h-12 w-12 text-[var(--warn)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <!-- Ampulheta com giro -->
            <path d="M5 22h14"></path>
            <path d="M5 2h14"></path>
            <path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"></path>
            <path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"></path>
        </svg>
    </div>

    <!-- Chip orbital flutuante com código 419 -->
    <span class="absolute -bottom-1 -right-1 flex h-8 items-center justify-center rounded-xl border border-[var(--warn)]/40 bg-[var(--side-3)] px-2.5 text-xs font-extrabold text-[var(--warn)] shadow-[0_4px_12px_rgba(0,0,0,0.6)]">
        419
    </span>
</div>
@endsection

@section('message')
Por questões de segurança e proteção aos dados governamentais, o token de validação do formulário ou sua sessão de acesso expirou devido ao tempo em inatividade.
@endsection

@section('hint', 'Dica: Basta atualizar a página para gerar um novo token de segurança e continuar de onde parou.')

@section('actions')
<button type="button" onclick="window.location.reload()" class="btn-licicon-primary">
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
    <span>Atualizar Página</span>
</button>

<a href="{{ route('login') }}" class="btn-licicon-secondary">
    <svg class="h-4 w-4 text-[var(--ink-3)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
    <span>Fazer Login Novamente</span>
</a>
@endsection
