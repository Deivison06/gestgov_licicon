@extends('layouts.app')
@section('page-title', 'Nova Fiscalização de Contrato')
@section('page-subtitle', 'Registre uma nova fiscalização vinculada a um contrato')

@section('content')

<style>
    /* ========================================= */
    /* BUSCA DE CONTRATOS — CUSTOM AUTOCOMPLETE  */
    /* ========================================= */
    .search-wrapper {
        position: relative;
    }
    .search-input-container {
        position: relative;
    }
    .search-input-container input {
        width: 100%;
        padding: 12px 16px 12px 44px;
        border: 1px solid #24403c;
        border-radius: 12px;
        font-size: 0.95rem;
        color: #e6f0ee;
        background: #0a1514;
        transition: all 0.2s ease;
        outline: none;
    }
    .search-input-container input:focus {
        border-color: #2DC197;
        box-shadow: 0 0 0 3px rgba(45, 193, 151, 0.15);
    }
    .search-input-container input::placeholder {
        color: #a2b9b5;
        opacity: 0.6;
    }
    .search-input-container .search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #a2b9b5;
        font-size: 1rem;
        pointer-events: none;
        transition: color 0.2s;
    }
    .search-input-container input:focus + .search-icon,
    .search-input-container input:focus ~ .search-icon {
        color: #2DC197;
    }
    .search-input-container .search-spinner {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        display: none;
    }
    .search-input-container .search-spinner.active {
        display: block;
    }
    .search-input-container .clear-btn {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        background: #142825;
        border: 1px solid #24403c;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #a2b9b5;
        font-size: 12px;
        transition: all 0.15s;
    }
    .search-input-container .clear-btn:hover {
        background: #24403c;
        color: #e6f0ee;
    }
    .search-input-container .clear-btn.active {
        display: flex;
    }
    @keyframes spin {
        to { transform: translateY(-50%) rotate(360deg); }
    }
    .spinner-icon {
        animation: spin 0.8s linear infinite;
    }

    /* ===== DROPDOWN DE RESULTADOS ===== */
    .search-results {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        background: #10201e;
        border: 1px solid #24403c;
        border-radius: 12px;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.4), 0 4px 10px rgba(0, 0, 0, 0.2);
        max-height: 360px;
        overflow-y: auto;
        z-index: 50;
        display: none;
        scrollbar-width: thin;
    }
    .search-results.active {
        display: block;
    }
    .search-result-item {
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #24403c;
        transition: background 0.15s ease;
    }
    .search-result-item:last-child {
        border-bottom: none;
    }
    .search-result-item:hover {
        background: #142825;
    }
    .search-result-item .result-title {
        font-weight: 600;
        font-size: 0.9rem;
        color: #e6f0ee;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .search-result-item .result-title .badge-origem {
        font-size: 0.65rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .badge-manual {
        background: #142825; color: #a2b9b5; border: 1px solid #24403c;
    }
    .badge-sistema {
        background: #0d2a23; color: #2DC197; border: 1px solid #184e40;
    }
    .search-result-item .result-detail {
        font-size: 0.8rem;
        color: #a2b9b5;
        margin-top: 3px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .search-result-item .result-detail i {
        width: 14px;
        text-align: center;
        color: #2DC197;
    }
    .search-results-empty {
        padding: 24px 16px;
        text-align: center;
        color: #a2b9b5;
    }
    .search-results-empty i {
        font-size: 1.5rem;
        margin-bottom: 6px;
        display: block;
        opacity: 0.5;
    }

    /* ===== CONTRATO SELECIONADO (CHIP) ===== */
    .selected-contract-chip {
        display: none;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        background: #0d2a23;
        border: 1px solid #2DC197;
        border-radius: 12px;
        margin-top: 8px;
    }
    .selected-contract-chip.active {
        display: flex;
    }
    .selected-contract-chip .chip-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: #2DC197;
        color: #041d1a;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
        font-weight: bold;
    }
    .selected-contract-chip .chip-text {
        flex: 1;
        min-width: 0;
    }
    .selected-contract-chip .chip-text .chip-title {
        font-weight: 600;
        font-size: 0.9rem;
        color: #2DC197;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .selected-contract-chip .chip-text .chip-sub {
        font-size: 0.78rem;
        color: #a2b9b5;
        margin-top: 1px;
    }
    .selected-contract-chip .chip-remove {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        border: none;
        background: rgba(255, 255, 255, 0.08);
        color: #a2b9b5;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s;
        flex-shrink: 0;
    }
    .selected-contract-chip .chip-remove:hover {
        background: #3d1a1e;
        color: #ff7b72;
    }

    /* ===== CAMPOS READONLY (DADOS DO CONTRATO) ===== */
    .readonly-field {
        background-color: #0a1514;
        border: 1px solid #24403c;
        color: #a2b9b5;
        cursor: default;
    }

    /* ===== CAMPOS VARIÁVEIS ===== */
    .campo-variavel { transition: all 0.3s ease; }
    .campo-oculto { display: none !important; }

    /* ===== RADIO CONCLUSÃO ===== */
    .radio-conclusao:checked + label {
        border-color: #2DC197;
        background-color: #0d2a23;
        box-shadow: 0 0 0 2px rgba(45, 193, 151, 0.25);
    }
</style>

<div class="card !rounded-2xl overflow-hidden">

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="p-4 mb-4 bg-[var(--bad-bg)] border border-[var(--bad-border)] rounded-xl mx-6 mt-6">
            <div class="flex items-center">
                <i class="mr-2 text-[var(--bad-strong)] fas fa-exclamation-triangle"></i>
                <h3 class="font-semibold text-[var(--bad-strong)]">Erros no Formulário</h3>
            </div>
            <ul class="mt-2 ml-5 text-sm text-[var(--bad-strong)]/90 list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="px-6 py-6" action="{{ route('admin.fiscalizacoes.store') }}" method="POST" id="formFiscalizacao" enctype="multipart/form-data">
        @csrf

        {{-- ============================================================ --}}
        {{-- SEÇÃO 1: BUSCA E SELEÇÃO DE CONTRATO                       --}}
        {{-- ============================================================ --}}
        <div class="pb-6 border-b border-[var(--line)]">
            <h3 class="flex items-center gap-2 text-lg font-bold text-[var(--ink)]">
                <i class="fas fa-search text-[var(--mint)]"></i> Buscar Contrato
            </h3>
            <p class="mt-1 text-sm text-[var(--ink-2)]">Busque pelo número do contrato, objeto, processo ou nome/CNPJ da empresa</p>

            <div class="mt-4 search-wrapper" id="searchWrapper">
                {{-- Input de busca --}}
                <div class="search-input-container" id="searchInputContainer">
                    <input type="text"
                           id="searchContratoInput"
                           autocomplete="off"
                           placeholder="Ex: 001/2026 ou Empresa X ou Aquisição de..."
                           aria-label="Buscar contrato">
                    <i class="fas fa-search search-icon"></i>
                    <span class="search-spinner" id="searchSpinner">
                        <i class="fas fa-circle-notch spinner-icon" style="color: #2DC197; font-size: 1rem;"></i>
                    </span>
                    <button type="button" class="clear-btn" id="clearSearch" title="Limpar busca">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Dropdown de resultados --}}
                <div class="search-results" id="searchResults"></div>

                {{-- Chip do contrato selecionado --}}
                <div class="selected-contract-chip" id="selectedChip">
                    <div class="chip-icon">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <div class="chip-text">
                        <div class="chip-title" id="chipTitle"></div>
                        <div class="chip-sub" id="chipSub"></div>
                    </div>
                    <button type="button" class="chip-remove" id="chipRemove" title="Remover contrato">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div id="helper_cadastrar_contrato" class="mt-3 flex items-center justify-between px-2">
                <p class="text-sm text-[var(--ink-2)] italic">
                    <i class="mr-1 fas fa-info-circle text-[var(--mint)]"></i>
                    Não encontrou o contrato na lista?
                </p>
                <a href="{{ route('admin.contratos.create') }}" target="_blank"
                class="text-xs bg-[var(--hover-bg)] hover:bg-[var(--card-3)] text-[var(--mint)] px-3 py-1.5 rounded-xl font-bold transition-all flex items-center gap-2 border border-[var(--line)]">
                    <i class="fas fa-plus-circle"></i> Cadastrar Novo Contrato
                </a>
            </div>

            {{-- Hidden fields para o polimorfismo --}}
            <input type="hidden" name="fiscalizavel_id" id="fiscalizavel_id"
                value="{{ old('fiscalizavel_id', $contratoPreSelecionado['id_puro'] ?? '') }}">
            <input type="hidden" name="fiscalizavel_type" id="fiscalizavel_type"
                value="{{ old('fiscalizavel_type', $contratoPreSelecionado['type_puro'] ?? '') }}">
        </div>

        {{-- ============================================================ --}}
        {{-- SEÇÃO 2: DADOS DO CONTRATO (READONLY — preenchidos via JS)  --}}
        {{-- ============================================================ --}}
        <div class="py-6 border-b border-[var(--line)]" id="secao_dados_contrato" style="display: none;">
            <h3 class="flex items-center gap-2 text-lg font-bold text-[var(--ink)]">
                <i class="fas fa-file-contract text-[var(--mint)]"></i> Dados do Contrato
                <span id="badge_origem" class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-[var(--hover-bg)] border border-[var(--line)] text-[var(--mint)]"></span>
            </h3>

            <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-semibold text-[var(--ink-2)]">Secretaria</label>
                    <input type="text" id="info_secretaria" class="w-full mt-1 px-3 py-2 rounded-xl readonly-field" readonly>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[var(--ink-2)]">Nº Contrato</label>
                    <input type="text" id="info_numero_contrato" class="w-full mt-1 px-3 py-2 rounded-xl readonly-field" readonly>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[var(--ink-2)]">Nº Processo</label>
                    <input type="text" id="info_numero_processo" class="w-full mt-1 px-3 py-2 rounded-xl readonly-field" readonly>
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-semibold text-[var(--ink-2)]">Objeto</label>
                    <textarea id="info_objeto" class="w-full mt-1 px-3 py-2 rounded-xl readonly-field" rows="2" readonly></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[var(--ink-2)]">Modalidade</label>
                    <input type="text" id="info_modalidade" class="w-full mt-1 px-3 py-2 rounded-xl readonly-field" readonly>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[var(--ink-2)]">Razão Social</label>
                    <input type="text" id="info_razao_social" class="w-full mt-1 px-3 py-2 rounded-xl readonly-field" readonly>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[var(--ink-2)]">CNPJ</label>
                    <input type="text" id="info_cnpj" class="w-full mt-1 px-3 py-2 rounded-xl readonly-field" readonly>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[var(--ink-2)]">Endereço</label>
                    <input type="text" id="info_endereco" class="w-full mt-1 px-3 py-2 rounded-xl readonly-field" readonly>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[var(--ink-2)]">Responsável pela Empresa</label>
                    <input type="text" id="info_representante" class="w-full mt-1 px-3 py-2 rounded-xl readonly-field" readonly>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- SEÇÃO 3: TIPO DE CONTRATO E IDENTIFICAÇÃO DA FISCALIZAÇÃO   --}}
        {{-- ============================================================ --}}
        <div class="py-6 border-b border-[var(--line)]">
            <h3 class="flex items-center gap-2 text-lg font-bold text-[var(--ink)]">
                <i class="fas fa-clipboard-check text-[var(--mint)]"></i> Dados da Fiscalização
            </h3>

            <div class="grid grid-cols-1 gap-6 mt-4 md:grid-cols-3">
                {{-- Tipo de Contrato --}}
                <div>
                    <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">
                        <span class="text-red-400">*</span> Tipo de Contrato
                    </label>
                    <select name="tipo_contrato" id="tipo_contrato" required
                            class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors outline-none">
                        <option value="" class="bg-[var(--card)] text-[var(--ink-2)]">Selecione o tipo</option>
                        @foreach($tiposFiscalizacao as $tipo)
                            <option value="{{ $tipo->value }}" {{ old('tipo_contrato') == $tipo->value ? 'selected' : '' }} class="bg-[var(--card)] text-[var(--ink)]">
                                {{ $tipo->getDisplayName() }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_contrato')
                        <p class="mt-1 text-sm text-[var(--bad-strong)]"><i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Data da Fiscalização --}}
                <div>
                    <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">
                        <span class="text-red-400">*</span> Data da Fiscalização
                    </label>
                    <input type="date" name="data_fiscalizacao" required
                           class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors outline-none"
                           value="{{ old('data_fiscalizacao') }}">
                    @error('data_fiscalizacao')
                        <p class="mt-1 text-sm text-[var(--bad-strong)]"><i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Número da Fiscalização --}}
                <div>
                    <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">
                        <span class="text-red-400">*</span> Número da Fiscalização
                    </label>
                    <input type="text" name="numero_fiscalizacao" required
                           class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors outline-none"
                           value="{{ old('numero_fiscalizacao') }}" placeholder="Ex: 001/2026">
                    @error('numero_fiscalizacao')
                        <p class="mt-1 text-sm text-[var(--bad-strong)]"><i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Relatório Fotográfico --}}
            <div class="mt-6">
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">
                    <i class="mr-1 fas fa-camera text-[var(--mint)]"></i> Anexar Relatório Fotográfico
                </label>
                <input type="file" name="relatorio_fotografico" id="relatorio_fotografico" accept="image/*"
                       class="block w-full px-3 py-2 text-sm text-[var(--ink-2)] bg-[var(--bg)] border border-[var(--line)] rounded-xl file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-[var(--mint)] file:text-[var(--on-mint)] hover:file:bg-[var(--mint-h)] transition-colors cursor-pointer focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)]">
                <p class="mt-2 text-xs text-[var(--ink-2)]">
                    <i class="mr-1 fas fa-info-circle text-[var(--mint)]"></i> Opcional. Formatos aceitos: JPEG, PNG ou WEBP, tamanho máximo: 5MB.
                </p>
                @error('relatorio_fotografico')
                    <p class="mt-1 text-sm text-[var(--bad-strong)]"><i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- SEÇÃO 4: CAMPOS DE AVALIAÇÃO (Dinâmicos por tipo)           --}}
        {{-- ============================================================ --}}
        <div class="py-6 border-b border-[var(--line)]" id="secao_avaliacao" style="display: none;">
            <h3 class="flex items-center gap-2 text-lg font-bold text-[var(--ink)]">
                <i class="fas fa-tasks text-[var(--mint)]"></i> Avaliação do cumprimento do contrato
            </h3>

            {{-- Checklist de Verificação (Compras e Serviços) --}}
            <div class="p-4 mt-4 border border-[var(--line)] rounded-xl campo-variavel campo-oculto bg-[var(--card-2)]" id="secao_checklist">
                <p class="mb-3 text-sm font-semibold text-[var(--ink-2)]">Antes de preencher, confira se você conhece estas informações do contrato</p>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach(\App\Models\Fiscalizacao::CHECKLIST_ITENS as $chave => $rotulo)
                        <label class="flex items-start gap-2 text-sm text-[var(--ink)]">
                            <input type="checkbox" name="checklist_fiscalizacao[{{ $chave }}]" value="1"
                                   class="mt-0.5 rounded text-[var(--mint)] focus:ring-[var(--mint)] bg-[var(--bg)] border-[var(--line)]"
                                   {{ old('checklist_fiscalizacao.'.$chave) ? 'checked' : '' }}>
                            {{ $rotulo }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Campo exclusivo: Metodologia (Serviços e Obras) --}}
            <div class="mt-4 campo-variavel campo-oculto" id="campo_metodologia">
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">Metodologia Aplicada na Fiscalização</label>
                <textarea name="metodologia_fiscalizacao" rows="4"
                          class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors outline-none"
                          placeholder="Descreva a metodologia utilizada...">{{ old('metodologia_fiscalizacao') }}</textarea>
            </div>

            {{-- Execução do Objeto / Execução no Período (label dinâmica) --}}
            <div class="mt-4 campo-variavel">
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]" id="label_execucao_objeto">Execução do Objeto</label>
                <textarea name="execucao_objeto" rows="4"
                          class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors outline-none"
                          placeholder="Descreva a execução do objeto...">{{ old('execucao_objeto') }}</textarea>
            </div>

            {{-- Ocorrências --}}
            <div class="mt-4">
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]" id="label_ocorrencias">Durante a vigência do contrato foi observado alguma irregularidade?</label>

                {{-- Toggle Houve/Não houve ocorrência (Compras e Serviços) --}}
                <div class="flex flex-wrap gap-4 mb-3 campo-variavel campo-oculto" id="campo_toggle_ocorrencia">
                    <label class="inline-flex items-center gap-2 text-sm text-[var(--ink)]">
                        <input type="radio" name="houve_ocorrencia" value="0" id="houve_ocorrencia_nao"
                               class="text-[var(--mint)] focus:ring-[var(--mint)] bg-[var(--bg)] border-[var(--line)]" disabled
                               {{ old('houve_ocorrencia') === '0' ? 'checked' : '' }}>
                        Não houve problemas ou ocorrências durante este período
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-[var(--ink)]">
                        <input type="radio" name="houve_ocorrencia" value="1" id="houve_ocorrencia_sim"
                               class="text-[var(--mint)] focus:ring-[var(--mint)] bg-[var(--bg)] border-[var(--line)]" disabled
                               {{ old('houve_ocorrencia') === '1' ? 'checked' : '' }}>
                        Houve ocorrências, conforme descrição abaixo
                    </label>
                </div>

                <div id="campo_detalhes_ocorrencia">
                    <textarea name="irregularidade_observada" id="irregularidade_observada" rows="4"
                              class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors outline-none"
                              placeholder="Descreva as irregularidades observadas, se houver...">{{ old('irregularidade_observada') }}</textarea>

                    {{-- Providências adotadas (Compras e Serviços) --}}
                    <div class="mt-3 campo-variavel campo-oculto" id="campo_providencias_adotadas">
                        <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">Providências adotadas</label>
                        <textarea name="providencias_adotadas" rows="3"
                                  class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors outline-none"
                                  placeholder="Descreva as providências adotadas para a correção...">{{ old('providencias_adotadas') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Qualidade das Entregas (label dinâmica) --}}
            <div class="mt-4 campo-variavel">
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]" id="label_qualidade_entregas">Qualidade das Entregas</label>
                <textarea name="qualidade_entregas" rows="4"
                          class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors outline-none"
                          placeholder="Avalie a qualidade...">{{ old('qualidade_entregas') }}</textarea>
            </div>

            {{-- Pontualidade / Prazos --}}
            <div class="mt-4">
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">A empresa cumpriu os prazos de entrega ou de execução previstos no contrato?</label>
                <textarea name="pontualidade_prazos" rows="4"
                          class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors outline-none"
                          placeholder="Avalie a pontualidade e cumprimento dos prazos...">{{ old('pontualidade_prazos') }}</textarea>
            </div>

            {{-- Regularidade Fiscal --}}
            <div class="mt-4">
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">A empresa está com a regularidade fiscal e trabalhista em dia?</label>

                {{-- Toggle Sim/Não (Compras e Serviços) --}}
                <div class="flex max-w-xs gap-3 campo-variavel campo-oculto" id="campo_regularidade_simnao">
                    <label class="flex-1">
                        <input type="radio" name="regularidade_fiscal_trabalhista" value="Sim" id="regularidade_sim"
                               class="sr-only peer" disabled
                               {{ old('regularidade_fiscal_trabalhista') === 'Sim' ? 'checked' : '' }}>
                        <span class="block p-2.5 font-bold text-center border-2 rounded-xl cursor-pointer transition-all border-[var(--line)] bg-[var(--bg)] text-[var(--ink-2)] peer-checked:border-green-500 peer-checked:bg-green-950/40 peer-checked:text-green-400">Sim</span>
                    </label>
                    <label class="flex-1">
                        <input type="radio" name="regularidade_fiscal_trabalhista" value="Não" id="regularidade_nao"
                               class="sr-only peer" disabled
                               {{ old('regularidade_fiscal_trabalhista') === 'Não' ? 'checked' : '' }}>
                        <span class="block p-2.5 font-bold text-center border-2 rounded-xl cursor-pointer transition-all border-[var(--line)] bg-[var(--bg)] text-[var(--ink-2)] peer-checked:border-red-500 peer-checked:bg-red-950/40 peer-checked:text-red-400">Não</span>
                    </label>
                </div>

                {{-- Texto livre (Obras) --}}
                <textarea name="regularidade_fiscal_trabalhista" id="regularidade_textarea" rows="4"
                          class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors outline-none"
                          placeholder="Descreva a situação da regularidade fiscal e trabalhista...">{{ old('regularidade_fiscal_trabalhista') }}</textarea>
            </div>

            {{-- Comunicação e Atendimento (Obras) --}}
            <div class="mt-4 campo-variavel campo-oculto" id="campo_comunicacao">
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">Comunicação e Atendimento</label>
                <textarea name="comunicacao_atendimento" id="comunicacao_atendimento" rows="4"
                          class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors outline-none"
                          placeholder="Avalie a comunicação e o atendimento...">{{ old('comunicacao_atendimento') }}</textarea>
            </div>

            {{-- Observações do Servidor (label dinâmica) --}}
            <div class="mt-4 campo-variavel">
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]" id="label_observacoes_servidor">Observações indicadas por servidor</label>
                <textarea name="observacoes_servidor" rows="4"
                          class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors outline-none"
                          placeholder="Registre as observações...">{{ old('observacoes_servidor') }}</textarea>
            </div>

            {{-- Recomendações ao Gestor --}}
            <div class="mt-4">
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">Recomendações a serem adotadas pelo Gestor</label>
                <textarea name="recomendacoes_gestor" rows="4"
                          class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors outline-none"
                          placeholder="Liste as recomendações para o gestor...">{{ old('recomendacoes_gestor') }}</textarea>
            </div>

            {{-- Recomendações à Empresa --}}
            <div class="mt-4">
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">Recomendações a serem adotadas pela Empresa</label>
                <textarea name="recomendacoes_empresa" rows="4"
                          class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors outline-none"
                          placeholder="Liste as recomendações para a empresa...">{{ old('recomendacoes_empresa') }}</textarea>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- SEÇÃO 5: CONCLUSÃO DO FISCAL                                --}}
        {{-- ============================================================ --}}
        <div class="py-6 border-b border-[var(--line)]" id="secao_conclusao" style="display: none;">
            <h3 class="flex items-center gap-2 text-lg font-bold text-[var(--ink)]">
                <i class="fas fa-gavel text-[var(--mint)]"></i> Qual é a sua conclusão sobre o cumprimento do contrato neste período?
            </h3>
            <p class="mt-1 text-sm text-[var(--ink-2)]">Selecione a conclusão que melhor descreve a situação do contrato</p>

            <div class="mt-4 space-y-3">
                @foreach($conclusoes as $conclusao)
                    <div class="flex items-start">
                        <input type="radio" name="conclusao_fiscal" id="conclusao_{{ $conclusao->value }}"
                               value="{{ $conclusao->value }}"
                               class="radio-conclusao sr-only"
                               {{ old('conclusao_fiscal') == $conclusao->value ? 'checked' : '' }}>
                        <label for="conclusao_{{ $conclusao->value }}"
                               class="flex-1 p-4 border-2 border-[var(--line)] bg-[var(--card-2)] rounded-xl cursor-pointer hover:border-[var(--mint)] hover:bg-[var(--hover-bg)] transition-all">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold
                                    @if($conclusao->value === 1) bg-[var(--card-3)] text-[var(--mint)] border border-[var(--line-3)]
                                    @elseif($conclusao->value === 2) bg-[var(--warn-bg)] text-[var(--warn-2)] border border-[var(--warn-border)]
                                    @else bg-[var(--bad-bg)] text-[var(--bad-strong)] border border-[var(--bad-border)]
                                    @endif">
                                    {{ $conclusao->value }}
                                </span>
                                <span class="text-sm text-[var(--ink)] leading-relaxed">{{ $conclusao->getTextoCompleto() }}</span>
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>

            @error('conclusao_fiscal')
                <p class="mt-2 text-sm text-[var(--bad-strong)]"><i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}</p>
            @enderror
        </div>

        {{-- ============================================================ --}}
        {{-- BOTÕES DE AÇÃO                                              --}}
        {{-- ============================================================ --}}
        <div class="flex justify-end gap-3 pt-6">
            <a href="{{ request()->has('id') ? route('admin.fiscalizacoes.selecionar-contrato') : route('admin.fiscalizacoes.index') }}"
                class="flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-[var(--ink)] transition-colors bg-[var(--card)] border border-[var(--line)] rounded-xl hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] shadow-sm">
                <i class="mr-2 fas fa-times"></i>
                Cancelar
            </a>
            <button type="submit"
                    class="flex items-center justify-center px-5 py-2.5 text-sm font-bold text-[var(--on-mint)] transition-all bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] hover:scale-105 shadow-sm">
                <i class="mr-2 fas fa-save"></i>
                Salvar Fiscalização
            </button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('🔧 Inicializando formulário de fiscalização...');

    // ==========================================================
    // ELEMENTOS DO DOM
    // ==========================================================
    const searchInput       = document.getElementById('searchContratoInput');
    const searchResults     = document.getElementById('searchResults');
    const searchSpinner     = document.getElementById('searchSpinner');
    const clearBtn          = document.getElementById('clearSearch');
    const searchInputCont   = document.getElementById('searchInputContainer');
    const selectedChip      = document.getElementById('selectedChip');
    const chipTitle         = document.getElementById('chipTitle');
    const chipSub           = document.getElementById('chipSub');
    const chipRemove        = document.getElementById('chipRemove');
    const fiscalizavelId    = document.getElementById('fiscalizavel_id');
    const fiscalizavelType  = document.getElementById('fiscalizavel_type');
    const helperCadastrar   = document.getElementById('helper_cadastrar_contrato'); // Adicione esta

    const SEARCH_URL = @json(route('admin.fiscalizacoes.buscar-contratos'));
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const preSelecionado = @json($contratoPreSelecionado);

    if (preSelecionado) {
        console.log('💡 Aplicando pré-seleção da Lobby...');

        if (helperCadastrar) helperCadastrar.style.display = 'none';

        selecionarContrato({
            id: preSelecionado.id_puro + '|' + preSelecionado.type_puro,
            numero_contrato: preSelecionado.numero_contrato,
            objeto: preSelecionado.objeto,
            secretaria: preSelecionado.secretaria,
            modalidade: preSelecionado.modalidade,
            razao_social: preSelecionado.razao_social,
            cnpj: preSelecionado.cnpj,
            endereco: preSelecionado.endereco,
            representante: preSelecionado.representante,
            origem: preSelecionado.origem
        });
    }

    let debounceTimer = null;
    let currentAbortController = null;
    let ultimosResultados = [];

    // ==========================================================
    // 📖 MAPA DE LABELS POR TIPO DE CONTRATO
    // ==========================================================
    const labelsMap = {
        compras: {
            execucao_objeto: 'Como o objeto do contrato foi executado neste período?',
            qualidade_entregas: 'Os produtos entregues ou os serviços executados estão de acordo com a qualidade exigida no contrato?',
            observacoes_servidor: 'Observações indicadas por servidor próximo a execução',
            label_ocorrencias: 'Houve alguma ocorrência ou problema durante a execução do contrato neste período?',
            mostrar_metodologia: false,
            mostrar_comunicacao: false,
            mostrar_checklist: true,
            ocorrencia_estruturada: true
        },
        servicos: {
            execucao_objeto: 'Como o objeto do contrato foi executado neste período?',
            qualidade_entregas: 'Os produtos entregues ou os serviços executados estão de acordo com a qualidade exigida no contrato?',
            observacoes_servidor: 'Observações indicadas por servidor próximo a execução',
            label_ocorrencias: 'Houve alguma ocorrência ou problema durante a execução do contrato neste período?',
            mostrar_metodologia: false,
            mostrar_comunicacao: false,
            mostrar_checklist: true,
            ocorrencia_estruturada: true
        },
        obras: {
            execucao_objeto: 'Execução do Objeto',
            qualidade_entregas: 'Qualidade dos serviços Executados',
            observacoes_servidor: 'Observações indicadas por servidor Fiscal de Engenharia',
            label_ocorrencias: 'Durante a vigência do contrato foi observado alguma irregularidade?',
            mostrar_metodologia: true,
            mostrar_comunicacao: true,
            mostrar_checklist: false,
            ocorrencia_estruturada: false
        }
    };

    // ==========================================================
    // 🔍 BUSCA AJAX COM DEBOUNCE (Vanilla JS — fetch)
    // ==========================================================
    searchInput.addEventListener('input', function () {
        const termo = this.value.trim();

        clearBtn.classList.toggle('active', termo.length > 0);

        if (currentAbortController) {
            currentAbortController.abort();
        }

        if (termo.length < 2) {
            searchResults.classList.remove('active');
            searchResults.innerHTML = '';
            searchSpinner.classList.remove('active');
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            buscarContratos(termo);
        }, 350);
    });

    async function buscarContratos(termo) {
        searchSpinner.classList.add('active');
        clearBtn.classList.remove('active');

        currentAbortController = new AbortController();

        try {
            const url = SEARCH_URL + '?q=' + encodeURIComponent(termo);
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
                signal: currentAbortController.signal
            });

            if (!response.ok) {
                throw new Error('Erro na requisição: ' + response.status);
            }

            const data = await response.json();
            console.log('📥 Resultados recebidos:', data.results?.length || 0);

            renderizarResultados(data.results || []);

        } catch (err) {
            if (err.name === 'AbortError') {
                console.log('🔄 Requisição cancelada (nova busca iniciada)');
                return;
            }
            console.error('❌ Erro na busca AJAX:', err);
            searchResults.innerHTML = `
                <div class="search-results-empty">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Erro ao buscar contratos. Tente novamente.</p>
                </div>
            `;
            searchResults.classList.add('active');
        } finally {
            searchSpinner.classList.remove('active');
            clearBtn.classList.toggle('active', searchInput.value.trim().length > 0);
        }
    }

    function renderizarResultados(results) {
        if (results.length === 0) {
            searchResults.innerHTML = `
                <div class="search-results-empty">
                    <i class="fas fa-search"></i>
                    <p style="font-size: 0.85rem; font-weight: 500;">Nenhum contrato encontrado</p>
                    <p style="font-size: 0.78rem; margin-top: 4px;">Tente buscar por número, objeto ou empresa</p>
                </div>
            `;
            searchResults.classList.add('active');
            return;
        }

        ultimosResultados = results;

        let html = '';
        results.forEach(function (item, index) {
            const isManual = item.origem === 'Contrato Manual';
            const badgeClass = isManual ? 'badge-manual' : 'badge-sistema';
            const badgeText = isManual ? 'Manual' : 'Sistema';

            html += `
                <div class="search-result-item" data-index="${index}"
                     style="padding: 14px 16px; border-left: 4px solid transparent; transition: all 0.2s;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                        <span style="font-weight: 700; color: #2DC197; font-size: 0.95rem;">
                            <i class="fas fa-file-signature mr-1" style="color: #2DC197;"></i>
                            ${escapeHtml(item.numero_contrato || 'S/N')}
                        </span>
                        <span class="badge-origem ${badgeClass}" style="font-size: 0.6rem; padding: 1px 8px;">${badgeText}</span>
                    </div>

                    <div style="font-size: 0.82rem; color: #e6f0ee; font-weight: 500; margin-bottom: 8px; line-height: 1.4;">
                        ${escapeHtml(truncate(item.objeto, 100))}
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 4px; padding-top: 8px; border-top: 1px solid #24403c;">
                        <div class="result-detail" style="margin: 0;">
                            <i class="fas fa-building" style="color: #2DC197; font-size: 0.75rem;"></i>
                            <span style="color: #a2b9b5; font-size: 0.78rem;">
                                <strong>Empresa:</strong> ${escapeHtml(item.razao_social || '—')}
                            </span>
                        </div>
                        <div style="display: flex; gap: 12px;">
                            <div class="result-detail" style="margin: 0;">
                                <i class="fas fa-id-card" style="color: #2DC197; font-size: 0.75rem;"></i>
                                <span style="color: #a2b9b5; font-size: 0.75rem;">${escapeHtml(item.cnpj || '—')}</span>
                            </div>
                            <div class="result-detail" style="margin: 0;">
                                <i class="fas fa-folder-open" style="color: #2DC197; font-size: 0.75rem;"></i>
                                <span style="color: #a2b9b5; font-size: 0.75rem;">Proc: ${escapeHtml(item.numero_processo || '—')}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        searchResults.innerHTML = html;
        searchResults.classList.add('active');
        searchResults.querySelectorAll('.search-result-item').forEach(function (el) {
            el.addEventListener('mouseenter', () => el.style.borderLeftColor = '#2DC197');
            el.addEventListener('mouseleave', () => el.style.borderLeftColor = 'transparent');

            el.addEventListener('click', function () {
                const data = ultimosResultados[Number(this.getAttribute('data-index'))];
                selecionarContrato(data);
            });
        });
    }

    // ==========================================================
    // ✅ SELECIONAR CONTRATO
    // ==========================================================
    function selecionarContrato(data) {
        console.log('✅ Contrato selecionado:', data);

        const parts = data.id.split('|');
        fiscalizavelId.value = parts[0];
        fiscalizavelType.value = parts[1];

        document.getElementById('info_secretaria').value = data.secretaria || '—';
        document.getElementById('info_numero_contrato').value = data.numero_contrato || '—';
        document.getElementById('info_numero_processo').value = data.numero_processo || '—';
        document.getElementById('info_objeto').value = data.objeto || '—';
        document.getElementById('info_modalidade').value = data.modalidade || '—';
        document.getElementById('info_razao_social').value = data.razao_social || '—';
        document.getElementById('info_cnpj').value = data.cnpj || '—';
        document.getElementById('info_endereco').value = data.endereco || '—';
        document.getElementById('info_representante').value = data.representante || '—';
        document.getElementById('badge_origem').textContent = data.origem || '';

        // Mostrar seção de dados e chip
        document.getElementById('secao_dados_contrato').style.display = 'block';

        chipTitle.textContent = (data.numero_contrato || 'S/N') + ' — ' + truncate(data.objeto, 45);
        chipSub.textContent = (data.razao_social || '—') + ' • ' + (data.origem || '');
        selectedChip.classList.add('active');

        // Esconder input de busca e resultados
        searchInputCont.style.display = 'none';
        searchResults.classList.remove('active');
        searchInput.value = '';
        clearBtn.classList.remove('active');

        if (helperCadastrar) helperCadastrar.style.display = 'none';
    }

    // ==========================================================
    // ❌ REMOVER CONTRATO SELECIONADO
    // ==========================================================
    function removerContrato() {
        console.log('🔄 Contrato removido');

        fiscalizavelId.value = '';
        fiscalizavelType.value = '';

        ['info_secretaria', 'info_numero_contrato', 'info_numero_processo', 'info_objeto',
         'info_modalidade', 'info_razao_social', 'info_cnpj', 'info_endereco', 'info_representante']
            .forEach(function (id) { document.getElementById(id).value = ''; });

        document.getElementById('badge_origem').textContent = '';
        document.getElementById('secao_dados_contrato').style.display = 'none';

        selectedChip.classList.remove('active');
        searchInputCont.style.display = 'block';
        searchInput.focus();

        if (helperCadastrar) helperCadastrar.style.display = 'flex';
    }

    chipRemove.addEventListener('click', removerContrato);

    clearBtn.addEventListener('click', function () {
        searchInput.value = '';
        searchInput.focus();
        searchResults.classList.remove('active');
        searchResults.innerHTML = '';
        clearBtn.classList.remove('active');
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('#searchWrapper')) {
            searchResults.classList.remove('active');
        }
    });

    // ==========================================================
    // 🔄 FORMULÁRIO DINÂMICO — TIPO DE CONTRATO
    // ==========================================================
    const tipoSelect = document.getElementById('tipo_contrato');
    const secaoAvaliacao = document.getElementById('secao_avaliacao');
    const secaoConclusao = document.getElementById('secao_conclusao');
    const campoMetodologia = document.getElementById('campo_metodologia');
    const secaoChecklist = document.getElementById('secao_checklist');
    const campoComunicacao = document.getElementById('campo_comunicacao');
    const campoToggleOcorrencia = document.getElementById('campo_toggle_ocorrencia');
    const campoDetalhesOcorrencia = document.getElementById('campo_detalhes_ocorrencia');
    const campoProvidenciasAdotadas = document.getElementById('campo_providencias_adotadas');
    const campoRegularidadeSimNao = document.getElementById('campo_regularidade_simnao');
    const regularidadeTextarea = document.getElementById('regularidade_textarea');

    // Estado atual da configuração dinâmica (usado pelos listeners abaixo)
    let ocorrenciaEstruturadaAtiva = false;

    function atualizarDetalhesOcorrencia() {
        if (!ocorrenciaEstruturadaAtiva) {
            campoDetalhesOcorrencia.style.display = 'block';
            campoProvidenciasAdotadas.classList.add('campo-oculto');
            return;
        }

        var marcado = document.querySelector('input[name="houve_ocorrencia"]:checked');
        var houve = !!marcado && marcado.value === '1';
        campoDetalhesOcorrencia.style.display = houve ? 'block' : 'none';
        campoProvidenciasAdotadas.classList.toggle('campo-oculto', !houve);
    }

    document.querySelectorAll('input[name="houve_ocorrencia"]').forEach(function (radio) {
        radio.addEventListener('change', atualizarDetalhesOcorrencia);
    });

    function atualizarFormulario(tipo) {
        if (!tipo || !labelsMap[tipo]) {
            secaoAvaliacao.style.display = 'none';
            secaoConclusao.style.display = 'none';
            return;
        }

        var config = labelsMap[tipo];
        console.log('📝 Atualizando formulário para tipo:', tipo);

        document.getElementById('label_execucao_objeto').textContent = config.execucao_objeto;
        document.getElementById('label_qualidade_entregas').textContent = config.qualidade_entregas;
        document.getElementById('label_observacoes_servidor').textContent = config.observacoes_servidor;
        document.getElementById('label_ocorrencias').textContent = config.label_ocorrencias;

        campoMetodologia.classList.toggle('campo-oculto', !config.mostrar_metodologia);

        secaoChecklist.classList.toggle('campo-oculto', !config.mostrar_checklist);

        campoComunicacao.classList.toggle('campo-oculto', !config.mostrar_comunicacao);
        document.getElementById('comunicacao_atendimento').disabled = !config.mostrar_comunicacao;

        // Ocorrências: toggle Houve/Não houve (Compras/Serviços) x texto livre sempre visível (Obras)
        ocorrenciaEstruturadaAtiva = !!config.ocorrencia_estruturada;
        campoToggleOcorrencia.classList.toggle('campo-oculto', !ocorrenciaEstruturadaAtiva);
        document.querySelectorAll('input[name="houve_ocorrencia"]').forEach(function (radio) {
            radio.disabled = !ocorrenciaEstruturadaAtiva;
        });
        atualizarDetalhesOcorrencia();

        // Regularidade Fiscal/Trabalhista: toggle Sim/Não (Compras/Serviços) x texto livre (Obras)
        var estruturaSimNao = !!config.ocorrencia_estruturada;
        campoRegularidadeSimNao.classList.toggle('campo-oculto', !estruturaSimNao);
        document.querySelectorAll('input[name="regularidade_fiscal_trabalhista"][type="radio"]').forEach(function (radio) {
            radio.disabled = !estruturaSimNao;
        });
        regularidadeTextarea.classList.toggle('campo-oculto', estruturaSimNao);
        regularidadeTextarea.disabled = estruturaSimNao;

        secaoAvaliacao.style.display = 'block';
        secaoConclusao.style.display = 'block';
    }

    tipoSelect.addEventListener('change', function () {
        atualizarFormulario(this.value);
    });

    if (tipoSelect.value) {
        atualizarFormulario(tipoSelect.value);
    }

    // ==========================================================
    // 🚫 VALIDAÇÃO NO SUBMIT
    // ==========================================================
    document.getElementById('formFiscalizacao').addEventListener('submit', function (e) {
        if (!fiscalizavelId.value) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Contrato Obrigatório',
                text: 'Por favor, selecione um contrato antes de salvar a fiscalização.',
                confirmButtonColor: '#2DC197',
            });
            return false;
        }

        if (!tipoSelect.value) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Tipo Obrigatório',
                text: 'Por favor, selecione o tipo de contrato.',
                confirmButtonColor: '#2DC197',
            });
            return false;
        }

        var conclusao = document.querySelector('input[name="conclusao_fiscal"]:checked');
        if (!conclusao) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Conclusão Obrigatória',
                text: 'Por favor, selecione a conclusão do fiscal.',
                confirmButtonColor: '#2DC197',
            });
            document.getElementById('secao_conclusao').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }

        console.log('✅ Validação OK, enviando formulário...');
        return true;
    });

    // ==========================================================
    // FUNÇÕES UTILITÁRIAS
    // ==========================================================
    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function truncate(str, maxLen) {
        if (!str) return '';
        return str.length > maxLen ? str.substring(0, maxLen) + '…' : str;
    }

    console.log('✅ Formulário de fiscalização inicializado com sucesso!');
});
</script>
@endpush
