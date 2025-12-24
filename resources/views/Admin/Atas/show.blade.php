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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
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
                    <p class="text-sm text-gray-500">Itens</p>
                    <p class="font-bold text-gray-900">{{ count($dadosAtas) }} itens</p>
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
                    <p class="text-sm text-gray-500">Valor Total</p>
                    <p class="font-bold text-gray-900">
                        R$ {{ number_format(collect($dadosAtas)->sum('valor_total_contratado'), 2, ',', '.') }}
                    </p>
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
                <button onclick="mostrarAba('gerar-ata')" 
                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm" 
                    data-tab="gerar-ata">
                    Gerar Ata
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
            <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhuma contratação</h3>
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
                                    <th class="px-6 py-3 text-left">Selecionar</th>
                                    <th class="px-6 py-3 text-left">Item</th>
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
                                        @if($contratacao->status === 'PENDENTE')
                                        <input type="checkbox" 
                                            value="{{ $contratacao->id }}" 
                                            class="contratacao-checkbox w-4 h-4 text-blue-600 rounded"
                                            onchange="atualizarSelecionados()"
                                            @if(in_array($contratacao->id, $contratacoesSelecionadas)) checked @endif>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $contratacao->lote->item }}</div>
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
                                            <button onclick="editarContratacao({{ $contratacao->id }})" 
                                                class="text-blue-600 hover:text-blue-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
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

    <!-- Aba: Gerar Ata -->
    <div id="aba-gerar-ata" class="tab-content hidden">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Configurar Ata</h3>
                <p class="text-gray-600">Preencha os dados para gerar a ata</p>
            </div>
            
            <!-- Contador de selecionados -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-medium text-blue-800" id="contador-texto">
                            @if(count($contratacoesSelecionadas) > 0)
                                {{ count($contratacoesSelecionadas) }} item(s) selecionado(s)
                            @else
                                Nenhum item selecionado
                            @endif
                        </p>
                        <p class="text-sm text-blue-600" id="valor-total-texto"></p>
                    </div>
                    <button onclick="selecionarTodos()" class="text-sm text-blue-600 hover:text-blue-800">
                        Selecionar todos
                    </button>
                </div>
            </div>
            
            <!-- Campos da Ata (USANDO OS MESMOS CAMPOS DO CONTRATO) -->
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
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Assinantes da Ata</h3>
                        <p class="text-gray-600">Adicione as pessoas que irão assinar a ata</p>
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
                    Gerar e Salvar Ata
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
    let contratacoesSelecionadas = @json($contratacoesSelecionadas);
    let assinantes = @json($assinantesAta);

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
        
        // Atualizar contador inicial
        atualizarContador();
    });

    async function carregarDadosContrato() {
        try {
            const response = await fetch(`/admin/processos/${processoId}/ata/dados`);
            const data = await response.json();

            if (data.success && data.dados) {
                preencherCamposContrato(data.dados);
            }
        } catch (error) {
            console.error('Erro ao carregar dados do contrato:', error);
        }
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
            
            await fetch(`/admin/processos/${processoId}/ata/salvar-campo`, {
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

    async function salvarContratacoesSelecionadas() {
        try {
            const dados = {
                contratacoes_selecionadas: contratacoesSelecionadas
            };
            
            await fetch(`/admin/processos/${processoId}/ata/salvar-contratacoes`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(dados)
            });
            
            console.log('Contratações selecionadas salvas');
        } catch (error) {
            console.error('Erro ao salvar contratações:', error);
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
            
            const response = await fetch(`/admin/processos/${processoId}/ata/salvar-assinantes`, {
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
    // GERENCIAMENTO DE CONTRATAÇÕES
    // ==============================================

    function atualizarSelecionados() {
        const checkboxes = document.querySelectorAll('.contratacao-checkbox:checked');
        contratacoesSelecionadas = Array.from(checkboxes).map(cb => parseInt(cb.value));
        
        atualizarContador();
        salvarContratacoesSelecionadas();
    }

    function selecionarTodos() {
        const checkboxes = document.querySelectorAll('.contratacao-checkbox');
        const todosSelecionados = checkboxes.length > 0 && 
            Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(cb => {
            cb.checked = !todosSelecionados;
        });
        
        atualizarSelecionados();
    }

    function atualizarContador() {
        const contador = document.getElementById('contador-texto');
        const valorTotal = document.getElementById('valor-total-texto');
        
        if (contratacoesSelecionadas.length === 0) {
            contador.textContent = 'Nenhum item selecionado';
            valorTotal.textContent = '';
        } else {
            contador.textContent = `${contratacoesSelecionadas.length} item(s) selecionado(s)`;
            
            // Calcular valor total
            let total = 0;
            contratacoesSelecionadas.forEach(id => {
                const row = document.querySelector(`[data-contratacao-id="${id}"]`);
                if (row) {
                    const valorText = row.querySelector('td:nth-child(4) .font-bold').textContent;
                    const valor = parseFloat(valorText.replace('R$ ', '').replace('.', '').replace(',', '.'));
                    if (!isNaN(valor)) total += valor;
                }
            });
            
            valorTotal.textContent = `Valor total: R$ ${total.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
        }
    }

    // ==============================================
    // MODAL DE CONTRATAÇÃO
    // ==============================================

    function abrirModalContratacao() {
        document.getElementById('modal-contratacao').classList.remove('hidden');
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
        
        try {
            const response = await fetch(`/admin/processos/${processoId}/vencedores/${vencedorId}/lotes-disponiveis`);
            const data = await response.json();
            
            if(data.success) {
                let html = `
                    <h4 class="font-bold mb-4">Itens Disponíveis</h4>
                    <div class="overflow-y-auto max-h-96">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr class="text-xs text-gray-500">
                                    <th class="px-4 py-2 text-left">Item</th>
                                    <th class="px-4 py-2 text-left">Disponível</th>
                                    <th class="px-4 py-2 text-left">Valor</th>
                                    <th class="px-4 py-2 text-left">Quantidade</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                data.lotes.forEach(lote => {
                    html += `
                        <tr class="border-b">
                            <td class="px-4 py-3">
                                <div class="font-medium">${lote.item}</div>
                                <div class="text-sm text-gray-500">${lote.descricao}</div>
                            </td>
                            <td class="px-4 py-3">${parseFloat(lote.quantidade_disponivel).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="px-4 py-3">R$ ${parseFloat(lote.vl_unit).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="px-4 py-3">
                                <input type="number" 
                                    data-lote-id="${lote.id}"
                                    min="0" 
                                    max="${lote.quantidade_disponivel}"
                                    step="0.01"
                                    class="w-24 px-2 py-1 border rounded quantidade-input"
                                    value="0">
                            </td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button onclick="voltarStep1()" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Voltar
                        </button>
                        <button onclick="salvarContratacoesLote()" 
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                            Salvar Contratações
                        </button>
                    </div>
                `;
                
                document.getElementById('modal-step-1').classList.add('hidden');
                document.getElementById('modal-step-2').innerHTML = html;
                document.getElementById('modal-step-2').classList.remove('hidden');
            } else {
                mostrarMensagem('Erro ao carregar itens: ' + data.message, 'error');
            }
        } catch(error) {
            console.error('Erro:', error);
            mostrarMensagem('Erro ao carregar itens', 'error');
        }
    }

    function voltarStep1() {
        document.getElementById('modal-step-2').classList.add('hidden');
        document.getElementById('modal-step-1').classList.remove('hidden');
    }

    async function salvarContratacoesLote() {
        const vencedorId = document.getElementById('vencedor_select').value;
        const inputs = document.querySelectorAll('.quantidade-input');
        const contratacoes = [];
        
        inputs.forEach(input => {
            const quantidade = parseFloat(input.value);
            if(quantidade > 0) {
                contratacoes.push({
                    vencedor_id: vencedorId,
                    lote_id: input.dataset.loteId,
                    quantidade_contratada: quantidade
                });
            }
        });
        
        if(contratacoes.length === 0) {
            mostrarMensagem('Selecione pelo menos um item', 'warning');
            return;
        }
        
        try {
            const response = await fetch(`/admin/processos/${processoId}/contratacoes-em-lote`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ contratacoes })
            });
            
            const data = await response.json();
            
            if(data.success) {
                mostrarMensagem('Contratações salvas com sucesso!', 'success');
                fecharModal();
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                mostrarMensagem('Erro: ' + data.message, 'error');
            }
        } catch(error) {
            console.error('Erro:', error);
            mostrarMensagem('Erro ao salvar contratações', 'error');
        }
    }

    // ==============================================
    // FUNÇÕES DE EDIÇÃO
    // ==============================================

    async function editarContratacao(id) {
        try {
            const response = await fetch(`/admin/processos/${processoId}/contratacao/${id}/edit`);
            const data = await response.json();
            
            if(data.success) {
                // Abrir modal de edição
                abrirModalEdicaoContratacao(data.contratacao, data.disponivel_atual);
            } else {
                mostrarMensagem('Erro ao carregar contratação', 'error');
            }
        } catch(error) {
            console.error('Erro:', error);
            mostrarMensagem('Erro ao carregar contratação', 'error');
        }
    }

    function abrirModalEdicaoContratacao(contratacao, disponivelAtual) {
        // Implementar modal de edição
        mostrarMensagem('Modal de edição em desenvolvimento', 'info');
    }

    // ==============================================
    // FUNÇÕES DE GERAÇÃO DE ATA
    // ==============================================

    function getCamposAta() {
        const campos = {};
        
        // Usar os mesmos campos do contrato
        const camposIds = [
            'numero_contrato',
            'data_assinatura_contrato',
            'numero_extrato',
            'comarca',
            'fonte_recurso',
            'subcontratacao'
        ];
        
        camposIds.forEach(id => {
            const elemento = document.getElementById(id);
            if (elemento && elemento.value) {
                campos[id] = elemento.value;
            }
        });
        
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

    async function gerarAtaPreview() {
        if (!validarCamposObrigatorios()) {
            mostrarMensagem('Preencha todos os campos obrigatórios', 'error');
            return;
        }
        
        if (contratacoesSelecionadas.length === 0) {
            mostrarMensagem('Selecione pelo menos uma contratação', 'error');
            return;
        }
        
        const assinantesData = getAssinantesAta();
        if (assinantesData.length === 0) {
            mostrarMensagem('Adicione pelo menos um assinante', 'error');
            return;
        }
        
        try {
            // Primeiro salvar os dados
            await salvarAssinantesAta();
            
            const campos = getCamposAta();
            const queryParams = new URLSearchParams({
                preview: 'true',
                campos: JSON.stringify(campos),
                assinantes: JSON.stringify(assinantesData),
                contratacoes: JSON.stringify(contratacoesSelecionadas)
            });
            
            // Abrir preview em nova aba
            window.open(`/admin/atas/${processoId}/gerar?${queryParams.toString()}`, '_blank');
            
        } catch (error) {
            console.error('Erro ao gerar preview:', error);
            mostrarMensagem('Erro ao gerar pré-visualização', 'error');
        }
    }

    async function gerarAtaFinal() {
        if (!validarCamposObrigatorios()) {
            mostrarMensagem('Preencha todos os campos obrigatórios', 'error');
            return;
        }
        
        if (contratacoesSelecionadas.length === 0) {
            mostrarMensagem('Selecione pelo menos uma contratação', 'error');
            return;
        }
        
        const assinantesData = getAssinantesAta();
        if (assinantesData.length === 0) {
            mostrarMensagem('Adicione pelo menos um assinante', 'error');
            return;
        }
        
        try {
            mostrarMensagem('Gerando ata...', 'info');
            
            // Prepara os campos
            const campos = getCamposAta();
            const assinantes = getAssinantesAta();
            
            const dados = {
                campos: campos,
                assinantes: assinantes,
                contratacoes_selecionadas: contratacoesSelecionadas,
                data: document.getElementById('data_assinatura_contrato').value
            };
            
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
                mostrarMensagem('Ata gerada com sucesso!', 'success');
                
                // Se quiser fazer download automático:
                if (data.download_url) {
                    const link = document.createElement('a');
                    link.href = data.download_url;
                    link.target = '_blank';
                    link.click();
                }
                
                // Recarrega a página após 3 segundos
                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                mostrarMensagem(data.message || 'Erro ao gerar ata', 'error');
            }
        } catch (error) {
            console.error('Erro ao gerar ata:', error);
            mostrarMensagem('Erro ao gerar ata', 'error');
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
    `;
    document.head.appendChild(estilo);
</script>
@endsection