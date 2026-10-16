@extends('errors.layout')

@section('title', 'Página Não Encontrada')

@section('badge_classes', 'border-[#2DC197]/30 bg-[#2DC197]/10 text-[#5eead4]')
@section('badge_text', 'Erro 404 • Não Encontrado')

@section('illustration')
<div class="relative flex items-center justify-center w-32 h-32 sm:w-36 sm:h-36">
    <!-- Pulso e halo luminoso externo -->
    <div class="absolute inset-0 rounded-full bg-[radial-gradient(circle,rgba(45,193,151,0.25)_0%,transparent_70%)] animate-pulse"></div>
    <div class="absolute -inset-1 rounded-full border border-[#2DC197]/20 border-dashed animate-spin" style="animation-duration: 25s;"></div>
    <div class="absolute inset-2 rounded-full border border-[#2DC197]/15"></div>

    <!-- Container Central Estilizado -->
    <div class="relative flex h-24 w-24 items-center justify-center rounded-3xl border border-[#2DC197]/40 bg-[#0d1b19] shadow-[0_12px_36px_rgba(45,193,151,0.18)]">
        <svg class="h-12 w-12 text-[#2DC197]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <!-- Documento Base de Licitação / Processo -->
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <!-- Lupa de Busca de Registro -->
            <circle cx="11" cy="14" r="3"></circle>
            <path d="m13.3 16.3 3.2 3.2"></path>
            <!-- Linhas de Processo -->
            <line x1="8" y1="8" x2="10" y2="8"></line>
            <line x1="8" y1="11" x2="11" y2="11"></line>
        </svg>
    </div>

    <!-- Chip orbital flutuante com código 404 -->
    <span class="absolute -bottom-1 -right-1 flex h-8 items-center justify-center rounded-xl border border-[#2DC197]/40 bg-[#051e1c] px-2.5 text-xs font-extrabold text-[#2DC197] shadow-[0_4px_12px_rgba(0,0,0,0.6)]">
        404
    </span>
</div>
@endsection

@section('message')
O recurso, processo administrativo ou página que você tentou acessar não foi encontrado, foi arquivado ou o endereço digitado pode conter algum erro.
@endsection

@section('hint', 'Dica: Verifique se o endereço ou número do processo está digitado corretamente na barra de navegação.')
