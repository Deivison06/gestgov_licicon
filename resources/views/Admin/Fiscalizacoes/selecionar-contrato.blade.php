@extends('layouts.app')
@section('page-title', 'Iniciar Nova Fiscalização')
@section('page-subtitle', 'Selecione um contrato para registrar a inspeção')

@section('content')
{{-- Definimos a lógica de busca diretamente no x-data para o Alpine reconhecer --}}
<div x-data="{
        search: '',
        active: null,
        matchSearch(empresa) {
            if (!this.search) return true;
            const term = this.search.toLowerCase();

            // Busca na empresa (Nome ou CNPJ)
            const inEmpresa = empresa.nome.toLowerCase().includes(term) || empresa.cnpj.includes(term);

            // Busca nos contratos daquela empresa (Número ou Objeto)
            const inContratos = empresa.contratos.some(c =>
                c.numero.toLowerCase().includes(term) ||
                c.objeto.toLowerCase().includes(term)
            );

            return inEmpresa || inContratos;
        }
    }">

    {{-- Barra de Busca --}}
    <div class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex gap-4 items-center">
        <div class="flex-1 relative">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" x-model="search"
                   placeholder="Buscar por empresa, CNPJ, número de contrato ou objeto..."
                   class="w-full pl-11 pr-4 py-3 rounded-lg border-gray-200 focus:border-[#009496] focus:ring-[#009496] transition-all">
        </div>
        <a href="{{ route('admin.fiscalizacoes.index') }}" class="px-6 py-3 bg-gray-100 text-gray-600 rounded-lg font-medium hover:bg-gray-200">
            Voltar
        </a>
    </div>

   <div class="space-y-4">
        @forelse($empresas as $empresa)
            {{-- Agora o matchSearch funciona pois está definido no x-data --}}
            <div x-show="matchSearch({{ json_encode($empresa) }})"
                 class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all"
                 x-cloak>

                {{-- Header da Empresa (Accordion) --}}
                <div @click="active = (active === {{ $loop->index }} ? null : {{ $loop->index }})"
                     class="px-6 py-4 flex items-center justify-between cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-[#062F43] text-white flex items-center justify-center font-bold shadow-sm">
                            {{ substr($empresa['nome'], 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">{{ $empresa['nome'] }}</h4>
                            <span class="text-xs text-gray-500 font-medium tracking-wider">CNPJ/CPF: {{ $empresa['cnpj'] }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($empresa['pendentes'] > 0)
                            <span class="px-2 py-1 bg-red-100 text-red-600 rounded-lg text-[10px] font-bold uppercase animate-pulse">
                                {{ $empresa['pendentes'] }} para fiscalizar
                            </span>
                        @endif
                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold">
                            {{ count($empresa['contratos']) }} Total
                        </span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform duration-300"
                           :class="active === {{ $loop->index }} ? 'rotate-180' : ''"></i>
                    </div>
                </div>

                {{-- Listagem de Contratos --}}
                <div x-show="active === {{ $loop->index }}" x-collapse x-cloak class="border-t border-gray-50 bg-gray-50/50">
                    <div class="p-4 space-y-3">
                        @foreach($empresa['contratos'] as $contrato)
                            <div class="bg-white p-4 rounded-lg border {{ !$contrato['ultima_fiscalizacao'] ? 'border-amber-200 bg-amber-50/30' : 'border-gray-100' }} flex flex-col md:flex-row justify-between items-center gap-4 hover:shadow-md transition-all">

                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-bold text-[#009496]">Contrato {{ $contrato['numero'] }}</span>
                                        @if(!$contrato['ultima_fiscalizacao'])
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-bold uppercase">Pendente</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 line-clamp-2 leading-relaxed">{{ $contrato['objeto'] }}</p>
                                </div>

                                <div class="flex items-center gap-4 shrink-0">
                                    {{-- Se já foi fiscalizado, mostra opções de histórico --}}
                                    @if($contrato['ultima_fiscalizacao'])
                                        <div class="flex items-center gap-2 pr-4 border-r border-gray-100">
                                            {{-- Corrigido para as IDs que vêm do Controller --}}
                                            @php
                                                $ultimaId = $contrato['ultima_fiscal_id'] ?? $contrato['ultima_fiscalizacao_id'];
                                            @endphp

                                            <a href="{{ route('admin.fiscalizacoes.show', $ultimaId) }}"
                                               class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Ver Última Fiscalização">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            {{-- Corrigido o nome da rota para 'gerar-relatorio' conforme o Controller --}}
                                            <a href="{{ route('admin.fiscalizacoes.pdf', $ultimaId) }}"
                                               target="_blank"
                                               class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Imprimir Relatório Anterior">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </div>
                                    @endif

                                    {{-- Botão Principal de Ação --}}
                                    <a href="{{ route('admin.fiscalizacoes.create', ['id' => $contrato['id'], 'type' => $contrato['type']]) }}"
                                       class="px-5 py-2.5 bg-[#062F43] text-white text-sm font-bold rounded-lg hover:bg-[#009496] transition-colors flex items-center gap-2 shadow-sm">
                                        <i class="fas fa-clipboard-check"></i>
                                        {{ $contrato['ultima_fiscalizacao'] ? 'Nova Inspeção' : 'Fiscalizar' }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-20 bg-white rounded-xl border border-dashed border-gray-300">
                <i class="fas fa-file-contract text-5xl text-gray-200 mb-4 block"></i>
                <h3 class="text-xl font-bold text-gray-400">Nenhum contrato ativo encontrado</h3>
            </div>
        @endforelse
    </div>
</div>
@endsection
