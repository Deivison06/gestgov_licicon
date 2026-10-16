@extends('errors.layout')

@section('title', 'Acesso Não Autorizado')

@section('badge_classes', 'border-[#fcd34d]/30 bg-[#fcd34d]/10 text-[#fcd34d]')
@section('badge_text', 'Erro 403 • Acesso Restrito')

@section('illustration')
<div class="relative flex items-center justify-center w-32 h-32 sm:w-36 sm:h-36">
    <!-- Pulso e halo luminoso externo âmbar -->
    <div class="absolute inset-0 rounded-full bg-[radial-gradient(circle,rgba(252,211,77,0.22)_0%,transparent_70%)] animate-pulse"></div>
    <div class="absolute -inset-1 rounded-full border border-[#fcd34d]/20 border-dashed animate-spin" style="animation-duration: 25s;"></div>
    <div class="absolute inset-2 rounded-full border border-[#fcd34d]/15"></div>

    <!-- Container Central Estilizado -->
    <div class="relative flex h-24 w-24 items-center justify-center rounded-3xl border border-[#fcd34d]/40 bg-[#0d1b19] shadow-[0_12px_36px_rgba(252,211,77,0.18)]">
        <svg class="h-12 w-12 text-[#fcd34d]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <!-- Escudo de Segurança Institucional -->
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            <!-- Cadeado de Acesso Restrito -->
            <rect x="9" y="11" width="6" height="5" rx="1"></rect>
            <path d="M10 11V9a2 2 0 0 1 4 0v2"></path>
        </svg>
    </div>

    <!-- Chip orbital flutuante com código 403 -->
    <span class="absolute -bottom-1 -right-1 flex h-8 items-center justify-center rounded-xl border border-[#fcd34d]/40 bg-[#051e1c] px-2.5 text-xs font-extrabold text-[#fcd34d] shadow-[0_4px_12px_rgba(0,0,0,0.6)]">
        403
    </span>
</div>
@endsection

@section('message')
Você não possui as permissões necessárias para acessar este recurso, processo administrativo ou dados pertencentes a outra unidade ou prefeitura.
@endsection

@section('hint', 'Dica: Caso precise deste acesso para suas atribuições, solicite a liberação de perfil ao administrador do seu órgão.')
