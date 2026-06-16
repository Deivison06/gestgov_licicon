@extends('layouts.app')

@section('title', 'Meu Perfil')

@section('content')
<div class="py-6">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Meu Perfil</h2>
            <p class="mt-1 text-sm text-gray-500">Gerencie suas informações de perfil e segurança</p>
        </div>

        <div class="space-y-6">
            <!-- Atualizar Informações do Perfil -->
            <div class="overflow-hidden bg-white shadow-sm rounded-xl">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-700">Informações do Perfil</h3>
                </div>
                <div class="p-6">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Atualizar Senha -->
            <div class="overflow-hidden bg-white shadow-sm rounded-xl">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-700">Alterar Senha</h3>
                </div>
                <div class="p-6">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- Dados de Assinatura (read-only) — só para users com is_assinante=true --}}
            @if (auth()->user()->is_assinante)
                <div class="overflow-hidden bg-white shadow-sm rounded-xl border-l-4 border-l-[#009496]">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-teal-50 to-white">
                        <h3 class="text-lg font-medium text-gray-700">Dados de Assinatura</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Informações usadas no carimbo das suas assinaturas digitais.
                            Para alterar, contate o administrador da prefeitura.
                        </p>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Prefeitura</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-900">
                                    {{ optional(auth()->user()->prefeitura)->nome
                                        ?? optional(auth()->user()->prefeitura)->cidade
                                        ?? '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Unidade / Setor</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-900">
                                    {{ optional(auth()->user()->unidade)->nome ?? '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Nº Portaria</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-900">
                                    {{ auth()->user()->numero_portaria ?: '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Data da Portaria</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-900">
                                    {{ auth()->user()->data_portaria
                                        ? auth()->user()->data_portaria->format('d/m/Y')
                                        : '—' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            @endif

            {{-- Excluir Conta — escondida para assinantes (preserva integridade das assinaturas) --}}
            @unless (auth()->user()->is_assinante)
                <div class="overflow-hidden bg-white shadow-sm rounded-xl">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-700">Excluir Conta</h3>
                    </div>
                    <div class="p-6">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            @endunless
        </div>
    </div>
</div>
@endsection
