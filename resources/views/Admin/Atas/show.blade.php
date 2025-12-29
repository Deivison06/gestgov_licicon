@extends('layouts.app')

@section('title', 'Ata de Contratação - ' . $processo->numero_processo)

@section('content')
<div class="py-6">
    @php
        $unidadesData = $processo->prefeitura->unidades->map(function ($unidade) {
            return [
                'id' => $unidade->id,
                'nome' => $unidade->nome,
                'servidor_responsavel' => $unidade->servidor_responsavel,
                'cargo_responsavel' => $unidade->cargo_responsavel ?? '',
                'numero_portaria' => $unidade->numero_portaria,
                'data_portaria' => $unidade->data_portaria,
            ];
        });
        
        // Decodificar os dados da ata se existirem
        $camposAta = $contrato ? [
            'numero_contrato' => $contrato->numero_contrato,
            'data_assinatura_contrato' => $contrato->data_assinatura_contrato,
            'numero_extrato' => $contrato->numero_extrato,
            'comarca' => $contrato->comarca,
            'fonte_recurso' => $contrato->fonte_recurso,
            'subcontratacao' => $contrato->subcontratacao,
        ] : [];
        
        $assinantesAta = $dadosAta ? json_decode($dadosAta->assinantes ?? '[]', true) : [];
        $contratacoesSelecionadas = $dadosAta ? json_decode($dadosAta->contratacoes_selecionadas ?? '[]', true) : [];
    @endphp
    
    <!-- Cabeçalho -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <a href="{{ route('admin.atas.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Voltar para Atas
            </a>
        </div>
        
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Ata de Contratação
                    <span class="text-blue-600">#{{ $processo->numero_processo }}</span>
                </h1>
                <p class="mt-1 text-gray-600">{!!  Str::limit($processo->objeto, 120) !!}</p>
            </div>
            
            <div class="mt-4 lg:mt-0">
                <button onclick="abrirModalContratacao()" 
                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nova Contratação
                </button>
            </div>
        </div>
    </div>

    <!-- Mensagens -->
    <div id="message-container" class="mb-6"></div>

    <!-- Cards de Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-lg mr-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Prefeitura</p>
                    <p class="font-bold text-gray-900">{{ $processo->prefeitura->nome }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-lg mr-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Itens Contratados</p>
                    <p class="font-bold text-gray-900">{{ $totalContratacoes }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center">
                <div class="bg-purple-100 p-3 rounded-lg mr-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Valor Total Contratado</p>
                    <p class="font-bold text-gray-900">
                        R$ {{ number_format($valorTotalContratado, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center">
                <div class="bg-yellow-100 p-3 rounded-lg mr-4">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Contratos Gerados</p>
                    <p class="font-bold text-gray-900">{{ $totalContratos }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Abas -->
    <div class="mb-8">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8">
                <button onclick="mostrarAba('contratacoes')" 
                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm" 
                    data-tab="contratacoes">
                    Contratações
                </button>
                <button onclick="mostrarAba('contratos')" 
                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm" 
                    data-tab="contratos">
                    Contratos Gerados
                </button>
                <button onclick="mostrarAba('gerar-ata')" 
                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm" 
                    data-tab="gerar-ata">
                    Gerar Contrato
                </button>
                <button onclick="mostrarAba('itens')" 
                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm" 
                    data-tab="itens">
                    Itens da Ata
                </button>
            </nav>
        </div>
    </div>

    <!-- Aba: Contratações -->
    <div id="aba-contratacoes" class="tab-content hidden">
        @if($contratacoes->isEmpty())
        <div class="text-center py-12 bg-white rounded-xl shadow">
            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhuma contratação pendente</h3>
            <p class="mt-2 text-gray-500">Clique em "Nova Contratação" para começar</p>
        </div>
        @else
        <div class="space-y-6">
            @foreach($processo->vencedores as $vencedor)
                @php
                    $vencedorContratacoes = $contratacoes[$vencedor->id] ?? collect([]);
                @endphp
                
                @if($vencedorContratacoes->isNotEmpty())
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="p-6 border-b">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-gray-900">{{ $vencedor->razao_social }}</h3>
                                <p class="text-sm text-gray-600">{{ $vencedor->cnpj }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Total</p>
                                <p class="font-bold text-green-600">
                                    R$ {{ number_format($vencedorContratacoes->sum('valor_total'), 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr class="text-xs text-gray-500 uppercase">
                                    <th class="px-6 py-3 text-left">Item</th>
                                    <th class="px-6 py-3 text-left">Descrição</th>
                                    <th class="px-6 py-3 text-left">Quantidade</th>
                                    <th class="px-6 py-3 text-left">Valor</th>
                                    <th class="px-6 py-3 text-left">Status</th>
                                    <th class="px-6 py-3 text-left">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($vencedorContratacoes as $contratacao)
                                <tr class="hover:bg-gray-50" data-contratacao-id="{{ $contratacao->id }}">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $contratacao->lote->item }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500">{{ $contratacao->lote->descricao }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ number_format($contratacao->quantidade_contratada, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold">R$ {{ number_format($contratacao->valor_total, 2, ',', '.') }}</div>
                                        <div class="text-sm text-gray-500">
                                            R$ {{ number_format($contratacao->valor_unitario, 2, ',', '.') }} un
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($contratacao->status === 'CONTRATADO')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Contratado
                                        </span>
                                        @else
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pendente
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            <button onclick="marcarComoContratado({{ $contratacao->id }})" 
                                                class="text-green-600 hover:text-green-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
        @endif
    </div>

    <!-- Aba: Contratos Gerados -->
    <div id="aba-contratos" class="tab-content hidden">
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900">Contratos Gerados</h3>
                <p class="text-sm text-gray-600 mt-1">Lista de todos os contratos em PDF já gerados</p>
            </div>
            
            @if($documentos->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhum contrato gerado ainda</h3>
                <p class="mt-2 text-gray-500">Gere o primeiro contrato na aba "Gerar Contrato"</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="text-xs text-gray-500 uppercase">
                            <th class="px-6 py-3 text-left">Data Geração</th>
                            <th class="px-6 py-3 text-left">Contrato Nº</th>
                            <th class="px-6 py-3 text-left">Itens Incluídos</th>
                            <th class="px-6 py-3 text-left">Valor Total</th>
                            <th class="px-6 py-3 text-left">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($documentos as $documento)
                        @php
                            // Tenta acessar de duas formas: do JSON do documento ou do modelo Contrato
                            $camposJson = json_decode($documento->campos ?? '{}', true);
                            $numeroContrato = $camposJson['numero_contrato'] ?? 'Não informado';
                            $dataAssinatura = $documento->campos['data_assinatura_contrato'] ?? $documento->contrato->data_assinatura_contrato ?? null;
                            $contratacoesIncluidas = $documento->contratacoes_selecionadas ?? [];
                            $valorTotal = $documento->valor_total ?? 0;
                            $quantidadeItens = $documento->quantidade_itens ?? 0;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium">
                                    {{ \Carbon\Carbon::parse($documento->gerado_em)->format('d/m/Y') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($documento->gerado_em)->format('H:i') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">
                                    {{ $numeroContrato }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium">{{ $quantidadeItens }} itens</div>
                                <button onclick="mostrarItensContrato({{ $documento->id }})" 
                                    class="text-blue-600 hover:text-blue-800 text-sm mt-1">
                                    Ver detalhes
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold">R$ {{ number_format($valorTotal, 2, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <a href="{{ url($documento->caminho) }}" 
                                    target="_blank"
                                    class="text-blue-600 hover:text-blue-800 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Visualizar
                                    </a>
                                    <a href="{{ route('admin.atas.download', $processo->id) }}" 
                                    class="text-green-600 hover:text-green-800 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Baixar
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Modal para mostrar itens do contrato -->
            <div id="modal-itens-contrato" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl">
                        <div class="px-6 py-4 border-b">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-bold">Itens do Contrato</h3>
                                <button onclick="fecharModalItens()" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <div id="conteudo-modal-itens"></div>
                        </div>
                        
                        <div class="px-6 py-4 border-t bg-gray-50 rounded-b-xl">
                            <div class="flex justify-end">
                                <button onclick="fecharModalItens()" 
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    Fechar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Aba: Gerar Contrato -->
    <div id="aba-gerar-ata" class="tab-content hidden">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Configurar Contrato</h3>
                <p class="text-gray-600">Preencha os dados para gerar o contrato</p>
            </div>
            
            <!-- Tabela de Contratações Pendentes -->
            <div class="mb-6">
                <h4 class="font-bold text-gray-700 mb-3">Contratações Pendentes</h4>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr class="text-xs text-gray-500 uppercase">
                                <th class="px-4 py-2 text-left">Selecionar</th>
                                <th class="px-4 py-2 text-left">Item</th>
                                <th class="px-4 py-2 text-left">Vencedor</th>
                                <th class="px-4 py-2 text-left">Quantidade</th>
                                <th class="px-4 py-2 text-left">Valor Unitário</th>
                                <th class="px-4 py-2 text-left">Valor Total</th>
                            </tr>
                        </thead>
                        <tbody id="tabela-contratacoes-pendentes">
                            <!-- As linhas serão preenchidas via JavaScript -->
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="5" class="px-4 py-2 text-right font-bold">Total:</td>
                                <td id="total-contratacoes-pendentes" class="px-4 py-2 font-bold text-green-600">R$ 0,00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <!-- Campos do Contrato -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Número do Contrato -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Número do Contrato *
                    </label>
                    <input type="text" id="numero_contrato" name="numero_contrato"
                        value="{{ $camposAta['numero_contrato'] ?? '' }}"
                        placeholder="Ex: 001/2024"
                        class="campo-ata w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        onblur="salvarCampo('numero_contrato', this.value)">
                </div>
                
                <!-- Data de Assinatura -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Data de Assinatura *
                    </label>
                    <input type="date" id="data_assinatura_contrato" name="data_assinatura_contrato"
                        value="{{ $camposAta['data_assinatura_contrato'] ?? now()->format('Y-m-d') }}"
                        class="campo-ata w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        onblur="salvarCampo('data_assinatura_contrato', this.value)">
                </div>
                
                <!-- Número do Extrato -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Número do Extrato
                    </label>
                    <input type="text" id="numero_extrato" name="numero_extrato"
                        value="{{ $camposAta['numero_extrato'] ?? '' }}"
                        placeholder="Ex: EXT/001/2024"
                        class="campo-ata w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        onblur="salvarCampo('numero_extrato', this.value)">
                </div>

                <!-- Comarca -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Comarca *
                    </label>
                    <input type="text" id="comarca" name="comarca"
                        value="{{ $camposAta['comarca'] ?? '' }}"
                        placeholder="Ex: Comarca de São Paulo"
                        class="campo-ata w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        onblur="salvarCampo('comarca', this.value)">
                </div>

                <!-- Fonte de Recurso -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Fonte de Recurso *
                    </label>
                    <textarea id="fonte_recurso" name="fonte_recurso" rows="3"
                        class="campo-ata w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        onblur="salvarCampo('fonte_recurso', this.value)"
                        placeholder="Descreva a fonte de recurso...">{{ $camposAta['fonte_recurso'] ?? '' }}</textarea>
                </div>

                <!-- Subcontratação -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Subcontratação
                    </label>
                    <select id="subcontratacao" name="subcontratacao"
                        class="campo-ata w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        onchange="salvarCampo('subcontratacao', this.value)">
                        <option value="">Selecione...</option>
                        <option value="1" @if(($camposAta['subcontratacao'] ?? '') == '1') selected @endif>Sim</option>
                        <option value="0" @if(($camposAta['subcontratacao'] ?? '') == '0') selected @endif>Não</option>
                    </select>
                </div>
            </div>

            <!-- Seção de Assinantes -->
            <div class="border-t pt-8 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Assinantes do Contrato</h3>
                        <p class="text-gray-600">Adicione as pessoas que irão assinar o contrato</p>
                    </div>
                    <button onclick="adicionarAssinanteAta()" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Adicionar Assinante
                    </button>
                </div>
                
                <!-- Lista de Assinantes -->
                <div id="assinantes-container-ata" class="space-y-4">
                    @if(count($assinantesAta) > 0)
                        @foreach($assinantesAta as $index => $assinante)
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg assinante-item">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Unidade -->
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-gray-600">
                                        Unidade *
                                    </label>
                                    <select name="assinante_unidade[]"
                                            class="assinante-unidade w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateResponsavelAta(this)">
                                        <option value="">Selecione a Unidade</option>
                                        @foreach ($processo->prefeitura->unidades as $unidade)
                                            <option value="{{ $unidade->id }}" 
                                                    @if($assinante['unidade_id'] == $unidade->id) selected @endif>
                                                {{ $unidade->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Responsável -->
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-gray-600">
                                        Responsável *
                                    </label>
                                    <input type="text"
                                        name="assinante_responsavel[]"
                                        value="{{ $assinante['responsavel'] ?? '' }}"
                                        placeholder="Nome do Responsável"
                                        required
                                        class="assinante-responsavel w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <!-- Cargo -->
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-gray-600">
                                        Cargo
                                    </label>
                                    <input type="text"
                                        name="assinante_cargo[]"
                                        value="{{ $assinante['cargo'] ?? '' }}"
                                        placeholder="Cargo do Responsável"
                                        class="assinante-cargo w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <!-- Número da Portaria -->
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-gray-600">
                                        Nº Portaria
                                    </label>
                                    <input type="text"
                                        name="assinante_portaria[]"
                                        value="{{ $assinante['numero_portaria'] ?? '' }}"
                                        placeholder="Número da Portaria"
                                        class="assinante-portaria w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <!-- Data da Portaria -->
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-gray-600">
                                        Data Portaria
                                    </label>
                                    <input type="date"
                                        name="assinante_data_portaria[]"
                                        value="{{ $assinante['data_portaria'] ?? '' }}"
                                        class="assinante-data-portaria w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <!-- Botão Remover -->
                            <div class="flex justify-end mt-4">
                                <button type="button"
                                        onclick="removerAssinanteAta(this)"
                                        class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Remover Assinante
                                </button>
                            </div>
                        </div>
                        @endforeach
                    @else
                    <!-- Assinante padrão -->
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg assinante-item">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Unidade -->
                            <div>
                                <label class="block mb-1 text-xs font-medium text-gray-600">
                                    Unidade *
                                </label>
                                <select name="assinante_unidade[]"
                                        class="assinante-unidade w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateResponsavelAta(this)">
                                    <option value="">Selecione a Unidade</option>
                                    @foreach ($processo->prefeitura->unidades as $unidade)
                                        <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Responsável -->
                            <div>
                                <label class="block mb-1 text-xs font-medium text-gray-600">
                                    Responsável *
                                </label>
                                <input type="text"
                                    name="assinante_responsavel[]"
                                    placeholder="Nome do Responsável"
                                    required
                                    class="assinante-responsavel w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Cargo -->
                            <div>
                                <label class="block mb-1 text-xs font-medium text-gray-600">
                                    Cargo
                                </label>
                                <input type="text"
                                    name="assinante_cargo[]"
                                    placeholder="Cargo do Responsável"
                                    class="assinante-cargo w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Número da Portaria -->
                            <div>
                                <label class="block mb-1 text-xs font-medium text-gray-600">
                                    Nº Portaria
                                </label>
                                <input type="text"
                                    name="assinante_portaria[]"
                                    placeholder="Número da Portaria"
                                    class="assinante-portaria w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Data da Portaria -->
                            <div>
                                <label class="block mb-1 text-xs font-medium text-gray-600">
                                    Data Portaria
                                </label>
                                <input type="date"
                                    name="assinante_data_portaria[]"
                                    class="assinante-data-portaria w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Botões -->
            <div class="flex justify-end space-x-4 pt-6 border-t">
                <button onclick="gerarAtaFinal()" 
                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Gerar e Salvar Contrato
                </button>
            </div>
        </div>
    </div>

    <!-- Aba: Itens da Ata -->
    <div id="aba-itens" class="tab-content">
        @if(count($dadosAtas) > 0)
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="text-xs text-gray-500 uppercase">
                            <th class="px-6 py-3 text-left">Item</th>
                            <th class="px-6 py-3 text-left">Vencedor</th>
                            <th class="px-6 py-3 text-left">Quantidade</th>
                            <th class="px-6 py-3 text-left">Valor</th>
                            <th class="px-6 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($dadosAtas as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $item['item'] }}</div>
                                <div class="text-sm text-gray-500">{{ $item['descricao'] }}</div>
                            </td>
                            <td class="px-6 py-4">
                                {{ $item['vencedor'] }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium">{{ number_format($item['quantidade_contratada'], 2, ',', '.') }}</div>
                                <div class="text-sm text-gray-500">
                                    Disp: {{ number_format($item['quantidade_disponivel'], 2, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold">R$ {{ number_format($item['valor_total_contratado'], 2, ',', '.') }}</div>
                                <div class="text-sm text-gray-500">
                                    R$ {{ number_format($item['valor_unitario'], 2, ',', '.') }} un
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($item['status'] === 'ESGOTADO')
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    Esgotado
                                </span>
                                @else
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Disponível
                                </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="text-center py-12 bg-white rounded-xl shadow">
            <p class="text-gray-500">Nenhum item disponível para exibição</p>
        </div>
        @endif
    </div>
</div>

<!-- Modal de Contratação -->
<div id="modal-contratacao" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl">
            <div class="px-6 py-4 border-b">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold">Nova Contratação</h3>
                    <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <div id="modal-step-1">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Selecione o Vencedor
                    </label>
                    <select id="vencedor_select" class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-6">
                        <option value="">Selecione...</option>
                        @foreach($processo->vencedores as $vencedor)
                        <option value="{{ $vencedor->id }}">{{ $vencedor->razao_social }}</option>
                        @endforeach
                    </select>
                    
                    <div class="flex justify-end">
                        <button onclick="avancarStep2()" 
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                            Próximo
                        </button>
                    </div>
                </div>
                
                <div id="modal-step-2" class="hidden">
                    <!-- Conteúdo será carregado via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const unidadesAssinantes = @json($unidadesData);
    const processoId = {{ $processo->id }};
    const csrfToken = '{{ csrf_token() }}';
    let vencedorSelecionado = null;
    let lotesSelecionados = [];

    // ==============================================
    // FUNÇÕES DE INICIALIZAÇÃO
    // ==============================================

    document.addEventListener('DOMContentLoaded', async function() {
        // Inicializar sistema de abas
        mostrarAba('contratacoes');
        
        // Inicializar eventos
        inicializarEventos();
        
        // Carregar dados do contrato
        await carregarDadosContrato();
        
        // Inicializar autopreenchimento de assinantes
        inicializarAutopreenchimentoAssinantes();
        
        // Carregar contratações pendentes
        await carregarContratacoesPendentes();
    });

    async function carregarDadosContrato() {
        try {
            const response = await fetch(`/admin/atas/${processoId}/dados`);
            const data = await response.json();

            if (data.success && data.dados) {
                preencherCamposContrato(data.dados);
            }
        } catch (error) {
            console.error('Erro ao carregar dados do contrato:', error);
        }
    }

    async function carregarContratacoesPendentes() {
        try {
            const response = await fetch(`/admin/atas/${processoId}/get-contratacoes-pendentes`);
            const data = await response.json();
            
            if (data.success && data.contratacoes) {
                preencherTabelaContratacoesPendentes(data.contratacoes);
            }
        } catch (error) {
            console.error('Erro ao carregar contratações pendentes:', error);
        }
    }

    function preencherTabelaContratacoesPendentes(contratacoes) {
        const tbody = document.getElementById('tabela-contratacoes-pendentes');
        const totalElement = document.getElementById('total-contratacoes-pendentes');
        
        tbody.innerHTML = '';
        let total = 0;
        
        contratacoes.forEach(contratacao => {
            total += parseFloat(contratacao.valor_total);
            
            const row = document.createElement('tr');
            row.className = 'border-b hover:bg-gray-50';
            row.innerHTML = `
                <td class="px-4 py-2">
                    <input type="checkbox" 
                        class="contratacao-pendente w-4 h-4 text-blue-600 rounded"
                        value="${contratacao.id}"
                        checked
                        onchange="atualizarTotalSelecionados()">
                </td>
                <td class="px-4 py-2">
                    <div class="font-medium">${contratacao.lote.item}</div>
                    <div class="text-sm text-gray-500">${contratacao.lote.descricao}</div>
                </td>
                <td class="px-4 py-2">
                    ${contratacao.vencedor.razao_social}
                </td>
                <td class="px-4 py-2">
                    ${parseFloat(contratacao.quantidade_contratada).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                </td>
                <td class="px-4 py-2">
                    R$ ${parseFloat(contratacao.valor_unitario).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                </td>
                <td class="px-4 py-2 font-bold">
                    R$ ${parseFloat(contratacao.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                </td>
            `;
            tbody.appendChild(row);
        });
        
        totalElement.textContent = `R$ ${total.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
    }

    function atualizarTotalSelecionados() {
        const checkboxes = document.querySelectorAll('.contratacao-pendente:checked');
        const totalElement = document.getElementById('total-contratacoes-pendentes');
        
        let total = 0;
        checkboxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            const valorTexto = row.querySelector('td:nth-child(6)').textContent;
            const valor = parseFloat(valorTexto.replace('R$ ', '').replace('.', '').replace(',', '.'));
            if (!isNaN(valor)) total += valor;
        });
        
        totalElement.textContent = `R$ ${total.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
    }

    function preencherCamposContrato(dados) {
        // Preencher campos do contrato
        if (dados.numero_contrato) {
            const input = document.getElementById('numero_contrato');
            if (input) input.value = dados.numero_contrato;
        }

        if (dados.data_assinatura_contrato) {
            const input = document.getElementById('data_assinatura_contrato');
            if (input) {
                const data = new Date(dados.data_assinatura_contrato);
                if (!isNaN(data.getTime())) {
                    input.value = data.toISOString().split('T')[0];
                }
            }
        }

        if (dados.numero_extrato) {
            const input = document.getElementById('numero_extrato');
            if (input) input.value = dados.numero_extrato;
        }

        if (dados.comarca) {
            const input = document.getElementById('comarca');
            if (input) input.value = dados.comarca;
        }

        if (dados.fonte_recurso) {
            const textarea = document.getElementById('fonte_recurso');
            if (textarea) textarea.value = dados.fonte_recurso;
        }

        if (dados.subcontratacao !== undefined) {
            const select = document.getElementById('subcontratacao');
            if (select) select.value = dados.subcontratacao.toString();
        }
    }

    function inicializarEventos() {
        // Evento para salvar campos automaticamente
        document.querySelectorAll('.campo-ata').forEach(campo => {
            campo.addEventListener('blur', function() {
                salvarCampo(this.name, this.value);
            });
        });
    }

    function inicializarAutopreenchimentoAssinantes() {
        // Adicionar evento de change a todos os selects de unidade
        document.querySelectorAll('.assinante-unidade').forEach(select => {
            select.addEventListener('change', function() {
                updateResponsavelAta(this);
            });
            
            // Preencher automaticamente se já tiver valor
            if (select.value) {
                updateResponsavelAta(select);
            }
        });
    }

    // ==============================================
    // SISTEMA DE ABAS
    // ==============================================

    function mostrarAba(aba) {
        // Atualizar botões
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active');
            if(btn.dataset.tab === aba) {
                btn.classList.add('active');
            }
        });
        
        // Mostrar conteúdo
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
            content.classList.add('hidden');
        });
        
        document.getElementById('aba-' + aba).classList.remove('hidden');
        document.getElementById('aba-' + aba).classList.add('active');
    }

    // ==============================================
    // FUNÇÕES DE DADOS (SALVAR/CARREGAR)
    // ==============================================

    async function salvarCampo(nome, valor) {
        try {
            const dados = {
                campo: nome,
                valor: valor
            };
            
            await fetch(`/admin/atas/${processoId}/salvar-campo`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(dados)
            });
            
            console.log('Campo salvo automaticamente');
        } catch (error) {
            console.error('Erro ao salvar campo:', error);
        }
    }

    // ==============================================
    // GERENCIAMENTO DE ASSINANTES
    // ==============================================

    function updateResponsavelAta(select) {
        const selectedUnidadeId = select.value;
        const selectedUnidade = unidadesAssinantes.find(u => u.id == selectedUnidadeId);
        const assinanteDiv = select.closest('.assinante-item');

        if (selectedUnidade) {
            const responsavelInput = assinanteDiv.querySelector('.assinante-responsavel');
            if (responsavelInput && !responsavelInput.value) {
                responsavelInput.value = selectedUnidade.servidor_responsavel || '';
            }

            const cargoInput = assinanteDiv.querySelector('.assinante-cargo');
            if (cargoInput && !cargoInput.value) {
                cargoInput.value = selectedUnidade.cargo_responsavel || '';
            }

            const portariaInput = assinanteDiv.querySelector('.assinante-portaria');
            if (portariaInput && !portariaInput.value) {
                portariaInput.value = selectedUnidade.numero_portaria || '';
            }

            const dataPortariaInput = assinanteDiv.querySelector('.assinante-data-portaria');
            if (dataPortariaInput && !dataPortariaInput.value) {
                dataPortariaInput.value = selectedUnidade.data_portaria || '';
            }
        }
    }

    function adicionarAssinanteAta() {
        const container = document.getElementById('assinantes-container-ata');
        const novoAssinante = document.createElement('div');
        novoAssinante.className = 'p-4 bg-gray-50 border border-gray-200 rounded-lg assinante-item';
        novoAssinante.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Unidade -->
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-600">
                        Unidade *
                    </label>
                    <select name="assinante_unidade[]"
                            class="assinante-unidade w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="updateResponsavelAta(this)">
                        <option value="">Selecione a Unidade</option>
                        @foreach ($processo->prefeitura->unidades as $unidade)
                            <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Responsável -->
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-600">
                        Responsável *
                    </label>
                    <input type="text"
                        name="assinante_responsavel[]"
                        placeholder="Nome do Responsável"
                        required
                        class="assinante-responsavel w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Cargo -->
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-600">
                        Cargo
                    </label>
                    <input type="text"
                        name="assinante_cargo[]"
                        placeholder="Cargo do Responsável"
                        class="assinante-cargo w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Número da Portaria -->
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-600">
                        Nº Portaria
                    </label>
                    <input type="text"
                        name="assinante_portaria[]"
                        placeholder="Número da Portaria"
                        class="assinante-portaria w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Data da Portaria -->
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-600">
                        Data Portaria
                    </label>
                    <input type="date"
                        name="assinante_data_portaria[]"
                        class="assinante-data-portaria w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Botão Remover -->
            <div class="flex justify-end mt-4">
                <button type="button"
                        onclick="removerAssinanteAta(this)"
                        class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Remover Assinante
                </button>
            </div>
        `;
        container.appendChild(novoAssinante);
    }

    function removerAssinanteAta(botao) {
        const container = document.getElementById('assinantes-container-ata');
        const assinanteDiv = botao.closest('.assinante-item');
        const todosAssinantes = container.querySelectorAll('.assinante-item');

        if (todosAssinantes.length > 1) {
            assinanteDiv.style.transition = 'opacity 0.3s ease';
            assinanteDiv.style.opacity = '0';
            setTimeout(() => assinanteDiv.remove(), 300);
            mostrarMensagem('Assinante removido', 'info');
        } else {
            mostrarMensagem('É obrigatório ter pelo menos um assinante.', 'error');
        }
    }

    async function salvarAssinantesAta() {
        try {
            const assinantesData = getAssinantesAta();
            
            const dados = {
                assinantes: assinantesData
            };
            
            const response = await fetch(`/admin/atas/${processoId}/salvar-assinantes`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(dados)
            });
            
            const data = await response.json();
            return data.success;
        } catch (error) {
            console.error('Erro ao salvar assinantes:', error);
            return false;
        }
    }

    function getAssinantesAta() {
        const container = document.getElementById('assinantes-container-ata');
        const selects = container.querySelectorAll('.assinante-unidade');
        const assinantesData = [];

        selects.forEach((select, index) => {
            const assinanteDiv = select.closest('.assinante-item');
            const responsavelInput = assinanteDiv.querySelector('.assinante-responsavel');
            const cargoInput = assinanteDiv.querySelector('.assinante-cargo');
            const portariaInput = assinanteDiv.querySelector('.assinante-portaria');
            const dataPortariaInput = assinanteDiv.querySelector('.assinante-data-portaria');

            if (select.value && responsavelInput.value) {
                const unidade = unidadesAssinantes.find(u => u.id == select.value);
                
                assinantesData.push({
                    unidade_id: select.value,
                    unidade_nome: unidade ? unidade.nome : '',
                    responsavel: responsavelInput.value,
                    cargo: cargoInput?.value || '',
                    numero_portaria: portariaInput?.value || '',
                    data_portaria: dataPortariaInput?.value || ''
                });
            }
        });
        return assinantesData;
    }

    // ==============================================
    // MODAL DE CONTRATAÇÃO (NOVA LÓGICA)
    // ==============================================

    function abrirModalContratacao() {
        document.getElementById('modal-contratacao').classList.remove('hidden');
        // Resetar modal
        document.getElementById('vencedor_select').value = '';
        document.getElementById('modal-step-1').classList.remove('hidden');
        document.getElementById('modal-step-2').classList.add('hidden');
        document.getElementById('modal-step-2').innerHTML = '';
        vencedorSelecionado = null;
        lotesSelecionados = [];
    }

    function fecharModal() {
        document.getElementById('modal-contratacao').classList.add('hidden');
    }

    async function avancarStep2() {
        const vencedorId = document.getElementById('vencedor_select').value;
        if(!vencedorId) {
            mostrarMensagem('Selecione um vencedor', 'error');
            return;
        }
        
        vencedorSelecionado = vencedorId;
        
        try {
            mostrarMensagem('Carregando itens disponíveis...', 'info');
            
            const response = await fetch(`/admin/atas/${processoId}/lotes-disponiveis/${vencedorId}`);
            const data = await response.json();
            
            console.log('Dados recebidos:', data);
            
            if(data.success && data.lotes && data.lotes.length > 0) {
                let html = `
                    <h4 class="font-bold mb-4">Itens Disponíveis - ${data.vencedor.razao_social}</h4>
                    <p class="text-sm text-gray-600 mb-4">Informe a quantidade desejada para cada item</p>
                    <div class="overflow-y-auto max-h-96 mb-4">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr class="text-xs text-gray-500">
                                    <th class="px-4 py-2 text-left">Item</th>
                                    <th class="px-4 py-2 text-left">Disponível</th>
                                    <th class="px-4 py-2 text-left">Valor Unitário</th>
                                    <th class="px-4 py-2 text-left">Quantidade</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                data.lotes.forEach(lote => {
                    html += `
                        <tr class="border-b hover:bg-gray-50" data-lote-id="${lote.id}">
                            <td class="px-4 py-3">
                                <div class="font-medium">${lote.item}</div>
                                <div class="text-sm text-gray-500">${lote.descricao}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium">${parseFloat(lote.quantidade_disponivel).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                                <span class="text-xs text-gray-500 ml-1">${lote.unidade || 'un'}</span>
                            </td>
                            <td class="px-4 py-3">
                                R$ ${parseFloat(lote.vl_unit).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" 
                                    id="quantidade-${lote.id}"
                                    data-lote-id="${lote.id}"
                                    min="0.01" 
                                    max="${lote.quantidade_disponivel}"
                                    step="0.01"
                                    placeholder="0.00"
                                    class="quantidade-lote w-24 px-2 py-1 border rounded"
                                    onchange="atualizarLoteQuantidade(${lote.id}, this.value)"
                                    oninput="validarQuantidade(${lote.id}, this)">
                            </td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="resumo-selecionados" class="mb-4 p-4 bg-blue-50 rounded-lg">
                        <h5 class="font-bold text-blue-800 mb-2">Resumo da Contratação</h5>
                        <div id="itens-selecionados-lista"></div>
                        <div class="mt-2 text-right font-bold text-blue-900">
                            Total: R$ <span id="valor-total-modal">0.00</span>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button onclick="voltarStep1()" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Voltar
                        </button>
                        <button onclick="salvarContratacoesSelecionadasModal()" 
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg"
                            id="btn-salvar-modal">
                            Salvar e Ir para Gerar Contrato
                        </button>
                    </div>
                `;
                
                document.getElementById('modal-step-1').classList.add('hidden');
                document.getElementById('modal-step-2').innerHTML = html;
                document.getElementById('modal-step-2').classList.remove('hidden');
                
                // Inicializar lotes selecionados com quantidade 0
                data.lotes.forEach(lote => {
                    lotesSelecionados.push({
                        id: lote.id,
                        item: lote.item,
                        descricao: lote.descricao,
                        quantidade: 0,
                        quantidadeMax: lote.quantidade_disponivel,
                        valorUnitario: lote.vl_unit,
                        valorTotal: 0
                    });
                });
                
                // Atualizar resumo inicial
                atualizarResumoModal();
                
                mostrarMensagem(`${data.lotes.length} item(s) disponível(is) encontrado(s)`, 'success');
            } else {
                let mensagem = 'Nenhum item disponível para este vencedor.';
                if (data.lotes && data.lotes.length === 0) {
                    mensagem = 'Todos os itens deste vencedor já foram contratados ou não há quantidade disponível.';
                }
                mostrarMensagem(mensagem, 'warning');
            }
        } catch(error) {
            console.error('Erro detalhado:', error);
            mostrarMensagem('Erro ao carregar itens. Verifique o console para mais detalhes.', 'error');
        }
    }

    function voltarStep1() {
        document.getElementById('modal-step-2').classList.add('hidden');
        document.getElementById('modal-step-1').classList.remove('hidden');
    }

    function validarQuantidade(loteId, input) {
        const valor = parseFloat(input.value) || 0;
        const loteData = lotesSelecionados.find(lote => lote.id === loteId);
        
        if (loteData && valor > loteData.quantidadeMax) {
            input.value = loteData.quantidadeMax;
            mostrarMensagem(`Quantidade máxima: ${loteData.quantidadeMax}`, 'warning');
        }
    }

    function atualizarLoteQuantidade(loteId, quantidade) {
        const quantidadeNum = parseFloat(quantidade) || 0;
        const loteIndex = lotesSelecionados.findIndex(lote => lote.id === loteId);
        
        if (loteIndex !== -1) {
            lotesSelecionados[loteIndex].quantidade = quantidadeNum;
            lotesSelecionados[loteIndex].valorTotal = quantidadeNum * lotesSelecionados[loteIndex].valorUnitario;
        }
        
        atualizarResumoModal();
    }

    function atualizarResumoModal() {
        const listaDiv = document.getElementById('itens-selecionados-lista');
        const totalSpan = document.getElementById('valor-total-modal');
        
        let html = '';
        let total = 0;
        
        lotesSelecionados.forEach(lote => {
            if (lote.quantidade > 0) {
                const valorItem = lote.valorTotal;
                total += valorItem;
                
                html += `
                    <div class="text-sm text-blue-700 mb-1">
                        ${lote.item}: ${lote.quantidade.toLocaleString('pt-BR', {minimumFractionDigits: 2})} x 
                        R$ ${lote.valorUnitario.toLocaleString('pt-BR', {minimumFractionDigits: 2})} = 
                        R$ ${valorItem.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                    </div>
                `;
            }
        });
        
        // Se nenhum item tem quantidade > 0, mostrar mensagem
        if (html === '') {
            html = '<div class="text-sm text-blue-600 italic">Nenhuma quantidade informada</div>';
        }
        
        listaDiv.innerHTML = html;
        totalSpan.textContent = total.toLocaleString('pt-BR', {minimumFractionDigits: 2});
    }

    async function salvarContratacoesSelecionadasModal() {
        const itensParaSalvar = lotesSelecionados.filter(lote => lote.quantidade > 0);
        
        if (itensParaSalvar.length === 0) {
            mostrarMensagem('Informe a quantidade para pelo menos um item', 'warning');
            return;
        }
        
        mostrarMensagem('Salvando contratações...', 'info');
        
        try {
            const btnSalvar = document.getElementById('btn-salvar-modal');
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = 'Salvando...';
            
            // Salvar cada contratação individualmente
            for (const lote of itensParaSalvar) {
                const dados = {
                    vencedor_id: vencedorSelecionado,
                    lote_id: lote.id,
                    quantidade: lote.quantidade
                };
                
                const response = await fetch(`/admin/atas/${processoId}/contratacao-direta`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dados)
                });
                
                const data = await response.json();
                
                if (!data.success) {
                    mostrarMensagem(`Erro ao salvar item ${lote.item}: ${data.message}`, 'error');
                    btnSalvar.disabled = false;
                    btnSalvar.innerHTML = 'Salvar e Ir para Gerar Contrato';
                    return;
                }
            }
            
            mostrarMensagem(`${itensParaSalvar.length} contratações salvas com sucesso!`, 'success');
            fecharModal();
            
            // Atualizar a aba de contratações
            await atualizarAbaContratacoes();
            
            // Redirecionar para aba de gerar contrato
            mostrarAba('gerar-ata');
            
        } catch(error) {
            console.error('Erro:', error);
            mostrarMensagem('Erro ao salvar contratações', 'error');
            
            const btnSalvar = document.getElementById('btn-salvar-modal');
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = 'Salvar e Ir para Gerar Contrato';
        }
    }

    async function atualizarAbaContratacoes() {
        try {
            const response = await fetch(`/admin/atas/${processoId}/get-contratacoes-atualizadas`);
            const data = await response.json();
            
            if (data.success && data.html) {
                // Substituir o conteúdo da aba de contratações
                const container = document.getElementById('aba-contratacoes');
                if (container) {
                    container.innerHTML = data.html;
                }
                
                // Atualizar contadores no cabeçalho
                if (data.totalItens !== undefined) {
                    const totalItensElement = document.querySelector('.bg-green-100 + div .font-bold');
                    if (totalItensElement) {
                        totalItensElement.textContent = `${data.totalItens} itens`;
                    }
                }
                
                if (data.valorTotal !== undefined) {
                    const valorTotalElement = document.querySelector('.bg-purple-100 + div .font-bold');
                    if (valorTotalElement) {
                        valorTotalElement.textContent = `R$ ${data.valorTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
                    }
                }
                
                // Atualizar tabela de contratações pendentes
                await carregarContratacoesPendentes();
                
                mostrarMensagem('Contratações atualizadas!', 'success');
            }
        } catch (error) {
            console.error('Erro ao atualizar aba de contratações:', error);
        }
    }

    // ==============================================
    // FUNÇÕES DE EDIÇÃO
    // ==============================================

    async function marcarComoContratado(contratacaoId) {
        if (!confirm('Tem certeza que deseja marcar esta contratação como CONTRATADO?')) {
            return;
        }
        
        try {
            const response = await fetch(`/admin/atas/${processoId}/marcar-contratado`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    contratacoes: [contratacaoId]
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                mostrarMensagem('Contratação marcada como CONTRATADO!', 'success');
                // Atualizar a tabela
                await atualizarAbaContratacoes();
            } else {
                mostrarMensagem('Erro: ' + data.message, 'error');
            }
        } catch(error) {
            console.error('Erro:', error);
            mostrarMensagem('Erro ao atualizar status', 'error');
        }
    }

    // ==============================================
    // FUNÇÕES DOS CONTRATOS GERADOS
    // ==============================================

    async function mostrarItensContrato(documentoId) {
        try {
            const response = await fetch(`/admin/atas/${processoId}/contrato-itens/${documentoId}`);
            const data = await response.json();
            
            if (data.success && data.itens) {
                let html = `
                    <h4 class="font-bold text-gray-900 mb-4">Itens incluídos neste contrato</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr class="text-xs text-gray-500 uppercase">
                                    <th class="px-4 py-2 text-left">Item</th>
                                    <th class="px-4 py-2 text-left">Vencedor</th>
                                    <th class="px-4 py-2 text-left">Quantidade</th>
                                    <th class="px-4 py-2 text-left">Valor Unitário</th>
                                    <th class="px-4 py-2 text-left">Valor Total</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                data.itens.forEach(item => {
                    html += `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2">
                                <div class="font-medium">${item.item}</div>
                                <div class="text-sm text-gray-500">${item.descricao}</div>
                            </td>
                            <td class="px-4 py-2">${item.vencedor}</td>
                            <td class="px-4 py-2">${item.quantidade}</td>
                            <td class="px-4 py-2">${item.valor_unitario}</td>
                            <td class="px-4 py-2 font-bold">${item.valor_total}</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-right font-bold">Total do Contrato:</td>
                                    <td class="px-4 py-2 font-bold text-green-600">${data.total_contrato}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `;
                
                document.getElementById('conteudo-modal-itens').innerHTML = html;
                document.getElementById('modal-itens-contrato').classList.remove('hidden');
            } else {
                mostrarMensagem('Não foi possível carregar os itens do contrato.', 'error');
            }
        } catch(error) {
            console.error('Erro ao carregar itens do contrato:', error);
            mostrarMensagem('Erro ao carregar detalhes do contrato.', 'error');
        }
    }

    function fecharModalItens() {
        document.getElementById('modal-itens-contrato').classList.add('hidden');
    }

    // ==============================================
    // FUNÇÕES DE GERAÇÃO DE CONTRATO
    // ==============================================

    function getCamposAta() {
        const campos = {};
        
        const camposIds = [
            'numero_contrato',
            'data_assinatura_contrato',
            'numero_extrato',
            'comarca',
            'fonte_recurso',
            'subcontratacao'
        ];
        
        console.log('DEBUG - getCamposAta() chamada');
        
        camposIds.forEach(id => {
            const elemento = document.getElementById(id);
            console.log(`DEBUG - Campo ${id}:`, elemento ? elemento.value : 'elemento não encontrado');
            
            if (elemento) {
                campos[id] = elemento.value;
            }
        });
        
        console.log('DEBUG - Campos coletados:', campos);
        return campos;
    }

    function validarCamposObrigatorios() {
        const obrigatorios = [
            'numero_contrato',
            'data_assinatura_contrato',
            'comarca',
            'fonte_recurso'
        ];
        
        for (const campo of obrigatorios) {
            const elemento = document.getElementById(campo);
            if (elemento && !elemento.value.trim()) {
                mostrarMensagem(`O campo "${getNomeCampo(campo)}" é obrigatório`, 'error');
                return false;
            }
        }
        
        return true;
    }

    function getNomeCampo(campo) {
        const nomes = {
            'numero_contrato': 'Número do Contrato',
            'data_assinatura_contrato': 'Data de Assinatura',
            'comarca': 'Comarca',
            'fonte_recurso': 'Fonte de Recurso',
            'subcontratacao': 'Subcontratação'
        };
        
        return nomes[campo] || campo;
    }

    function validarGeracaoContrato() {
        // Campos obrigatórios
        const obrigatorios = [
            { id: 'numero_contrato', nome: 'Número do Contrato' },
            { id: 'data_assinatura_contrato', nome: 'Data de Assinatura' },
            { id: 'comarca', nome: 'Comarca' },
            { id: 'fonte_recurso', nome: 'Fonte de Recurso' }
        ];
        
        for (const campo of obrigatorios) {
            const elemento = document.getElementById(campo.id);
            if (elemento && !elemento.value.trim()) {
                mostrarMensagem(`O campo "${campo.nome}" é obrigatório`, 'error');
                elemento.focus();
                return false;
            }
        }
        
        // Verificar assinantes
        const assinantesContainer = document.getElementById('assinantes-container-ata');
        const assinantes = assinantesContainer.querySelectorAll('.assinante-item');
        
        let assinantesValidos = 0;
        assinantes.forEach(assinante => {
            const unidade = assinante.querySelector('.assinante-unidade').value;
            const responsavel = assinante.querySelector('.assinante-responsavel').value;
            
            if (unidade && responsavel.trim()) {
                assinantesValidos++;
            }
        });
        
        if (assinantesValidos === 0) {
            mostrarMensagem('Adicione pelo menos um assinante válido', 'error');
            return false;
        }
        
        // Verificar contratações selecionadas
        const checkboxes = document.querySelectorAll('.contratacao-pendente:checked');
        if (checkboxes.length === 0) {
            mostrarMensagem('Selecione pelo menos uma contratação', 'error');
            return false;
        }
        
        return true;
    }

    function getContratacoesSelecionadas() {
        const checkboxes = document.querySelectorAll('.contratacao-pendente:checked');
        const selecionadas = [];
        
        checkboxes.forEach(checkbox => {
            selecionadas.push(parseInt(checkbox.value));
        });
        
        return selecionadas;
    }

    async function gerarAtaFinal() {
        if (!validarCamposObrigatorios()) {
            mostrarMensagem('Preencha todos os campos obrigatórios', 'error');
            return;
        }
        
        const assinantesData = getAssinantesAta();
        if (assinantesData.length === 0) {
            mostrarMensagem('Adicione pelo menos um assinante', 'error');
            return;
        }
        
        const contratacoesSelecionadas = getContratacoesSelecionadas();
        if (contratacoesSelecionadas.length === 0) {
            mostrarMensagem('Selecione pelo menos uma contratação pendente', 'error');
            return;
        }
        
        if (!confirm('Tem certeza que deseja gerar o contrato? Esta ação marcará as contratações selecionadas como CONTRATADO.')) {
            return;
        }
        
         try {
            mostrarMensagem('Gerando contrato...', 'info');
            
            // Prepara os campos - VERIFIQUE SE ESTÁ CORRETO
            const campos = getCamposAta();
            const assinantes = getAssinantesAta();
            
            // DEBUG: Verifique no console o que está sendo enviado
            console.log('DEBUG - Campos a serem enviados:', campos);
            console.log('DEBUG - Numero contrato:', campos.numero_contrato);
            console.log('DEBUG - Assinantes:', assinantes);
            
            const dados = {
                campos: campos,
                assinantes: assinantes,
                contratacoes_selecionadas: contratacoesSelecionadas,
                data: document.getElementById('data_assinatura_contrato').value
            };

            console.log('DEBUG - Dados completos:', dados);
            
            const response = await fetch(`/admin/atas/${processoId}/gerar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(dados)
            });
            
            const data = await response.json();
            
            if (data.success) {
                mostrarMensagem('Contrato gerado com sucesso!', 'success');
                
                // Fazer download automático
                if (data.download_url) {
                    const link = document.createElement('a');
                    link.href = data.download_url;
                    link.target = '_blank';
                    link.click();
                }
                
                // Atualizar a página
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                mostrarMensagem(data.message || 'Erro ao gerar contrato', 'error');
            }
        } catch (error) {
            console.error('Erro ao gerar contrato:', error);
            mostrarMensagem('Erro ao gerar contrato', 'error');
        }
    }

    // ==============================================
    // FUNÇÕES AUXILIARES (MENSAGENS)
    // ==============================================

    function mostrarMensagem(texto, tipo = 'info') {
        const container = document.getElementById('message-container');
        const cores = {
            success: 'bg-green-100 border-green-400 text-green-800',
            error: 'bg-red-100 border-red-400 text-red-800',
            warning: 'bg-yellow-100 border-yellow-400 text-yellow-800',
            info: 'bg-blue-100 border-blue-400 text-blue-800'
        };
        
        const icones = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        
        container.innerHTML = `
            <div class="p-4 mb-4 border-l-4 rounded ${cores[tipo]} animate-fade-in">
                <div class="flex items-center">
                    <span class="mr-2 text-lg">${icones[tipo]}</span>
                    <span class="font-medium">${texto}</span>
                </div>
            </div>
        `;
        
        // Remover mensagem após 5 segundos
        setTimeout(() => {
            if (container.innerHTML.includes(texto)) {
                container.innerHTML = '';
            }
        }, 5000);
    }

    // ==============================================
    // ANIMAÇÕES CSS
    // ==============================================
    const estilo = document.createElement('style');
    estilo.textContent = `
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
        .tab-button.active {
            border-color: #3b82f6;
            color: #1f2937;
        }
        .tab-content.active {
            display: block;
        }
        .hidden {
            display: none;
        }
    `;
    document.head.appendChild(estilo);
</script>
@endsection