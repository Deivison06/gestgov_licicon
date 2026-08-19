@extends('layouts.app')
@section('page-title', 'Registrar Ocorrência')
@section('page-subtitle', 'Registre uma ocorrência contratual vinculada a um contrato')

@section('content')

<style>
    /* ========================================= */
    /* BUSCA DE CONTRATOS — CUSTOM AUTOCOMPLETE  */
    /* (mesmo padrão visual do módulo de Fiscalização) */
    /* ========================================= */
    .search-wrapper { position: relative; }
    .search-input-container { position: relative; }
    .search-input-container input {
        width: 100%;
        padding: 12px 16px 12px 44px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 0.95rem;
        color: #1f2937;
        background: #fff;
        transition: all 0.2s ease;
        outline: none;
    }
    .search-input-container input:focus {
        border-color: #009496;
        box-shadow: 0 0 0 3px rgba(0, 148, 150, 0.12);
    }
    .search-input-container input::placeholder { color: #9ca3af; }
    .search-input-container .search-icon {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        color: #9ca3af; font-size: 1rem; pointer-events: none; transition: color 0.2s;
    }
    .search-input-container input:focus + .search-icon,
    .search-input-container input:focus ~ .search-icon { color: #009496; }
    .search-input-container .search-spinner {
        position: absolute; right: 14px; top: 50%; transform: translateY(-50%); display: none;
    }
    .search-input-container .search-spinner.active { display: block; }
    .search-input-container .clear-btn {
        position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
        background: #f3f4f6; border: none; border-radius: 50%; width: 24px; height: 24px;
        display: none; align-items: center; justify-content: center; cursor: pointer;
        color: #6b7280; font-size: 12px; transition: all 0.15s;
    }
    .search-input-container .clear-btn:hover { background: #e5e7eb; color: #374151; }
    .search-input-container .clear-btn.active { display: flex; }
    @keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }
    .spinner-icon { animation: spin 0.8s linear infinite; }

    .search-results {
        position: absolute; top: calc(100% + 6px); left: 0; right: 0;
        background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12), 0 4px 10px rgba(0, 0, 0, 0.04);
        max-height: 360px; overflow-y: auto; z-index: 50; display: none; scrollbar-width: thin;
    }
    .search-results.active { display: block; }
    .search-result-item { padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #f3f4f6; transition: background 0.15s ease; }
    .search-result-item:last-child { border-bottom: none; }
    .search-result-item:hover { background: #f0fdfa; }
    .search-result-item .result-title { font-weight: 600; font-size: 0.9rem; color: #1f2937; display: flex; align-items: center; gap: 8px; }
    .search-result-item .result-title .badge-origem { font-size: 0.65rem; font-weight: 600; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.03em; }
    .badge-manual { background: #dbeafe; color: #1e40af; }
    .badge-sistema { background: #d1fae5; color: #065f46; }
    .search-result-item .result-detail { font-size: 0.8rem; color: #6b7280; margin-top: 3px; display: flex; align-items: center; gap: 6px; }
    .search-result-item .result-detail i { width: 14px; text-align: center; color: #9ca3af; }
    .search-results-empty { padding: 24px 16px; text-align: center; color: #9ca3af; }
    .search-results-empty i { font-size: 1.5rem; margin-bottom: 6px; display: block; opacity: 0.5; }

    .selected-contract-chip {
        display: none; align-items: center; gap: 10px; padding: 10px 16px;
        background: linear-gradient(135deg, #f0fdfa 0%, #e0f7f7 100%);
        border: 2px solid #009496; border-radius: 12px; margin-top: 8px;
    }
    .selected-contract-chip.active { display: flex; }
    .selected-contract-chip .chip-icon {
        width: 36px; height: 36px; border-radius: 10px; background: #009496; color: white;
        display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;
    }
    .selected-contract-chip .chip-text { flex: 1; min-width: 0; }
    .selected-contract-chip .chip-text .chip-title { font-weight: 600; font-size: 0.9rem; color: #115e59; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .selected-contract-chip .chip-text .chip-sub { font-size: 0.78rem; color: #6b7280; margin-top: 1px; }
    .selected-contract-chip .chip-remove {
        width: 28px; height: 28px; border-radius: 8px; border: none; background: rgba(0, 0, 0, 0.06); color: #6b7280;
        display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s; flex-shrink: 0;
    }
    .selected-contract-chip .chip-remove:hover { background: #fecaca; color: #dc2626; }

    .readonly-field { background-color: #f9fafb; border: 1px solid #e5e7eb; color: #6b7280; cursor: default; }
</style>

<div class="overflow-hidden bg-white shadow-sm rounded-xl">

    @if($errors->any())
        <div class="p-4 mb-4 bg-red-50 border border-red-200 rounded-lg mx-6 mt-6">
            <div class="flex items-center">
                <i class="mr-2 text-red-500 fas fa-exclamation-triangle"></i>
                <h3 class="font-semibold text-red-800">Erros no Formulário</h3>
            </div>
            <ul class="mt-2 ml-5 text-sm text-red-700 list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="px-6 py-6" action="{{ route('admin.ocorrencias.store') }}" method="POST" id="formOcorrencia" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="status" id="status_input" value="{{ old('status', 'registrada') }}">

        {{-- ============================================================ --}}
        {{-- SEÇÃO 1: BUSCA E SELEÇÃO DE CONTRATO                       --}}
        {{-- ============================================================ --}}
        <div class="pb-6 border-b border-gray-100">
            <h3 class="flex items-center gap-2 text-lg font-medium text-gray-700">
                <i class="fas fa-search text-[#009496]"></i> Buscar Contrato
            </h3>
            <p class="mt-1 text-sm text-gray-500">Busque pelo número do contrato, objeto, processo ou nome/CNPJ da empresa</p>

            <div class="mt-4 search-wrapper" id="searchWrapper">
                <div class="search-input-container" id="searchInputContainer">
                    <input type="text" id="searchContratoInput" autocomplete="off"
                           placeholder="Ex: 001/2026 ou Empresa X ou Aquisição de..." aria-label="Buscar contrato">
                    <i class="fas fa-search search-icon"></i>
                    <span class="search-spinner" id="searchSpinner">
                        <i class="fas fa-circle-notch spinner-icon" style="color: #009496; font-size: 1rem;"></i>
                    </span>
                    <button type="button" class="clear-btn" id="clearSearch" title="Limpar busca">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="search-results" id="searchResults"></div>

                <div class="selected-contract-chip" id="selectedChip">
                    <div class="chip-icon"><i class="fas fa-file-contract"></i></div>
                    <div class="chip-text">
                        <div class="chip-title" id="chipTitle"></div>
                        <div class="chip-sub" id="chipSub"></div>
                    </div>
                    <button type="button" class="chip-remove" id="chipRemove" title="Remover contrato">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <input type="hidden" name="fiscalizavel_id" id="fiscalizavel_id"
                value="{{ old('fiscalizavel_id', $contratoPreSelecionado['id_puro'] ?? '') }}">
            <input type="hidden" name="fiscalizavel_type" id="fiscalizavel_type"
                value="{{ old('fiscalizavel_type', $contratoPreSelecionado['type_puro'] ?? '') }}">
        </div>

        {{-- ============================================================ --}}
        {{-- SEÇÃO 2: DADOS DO CONTRATO (READONLY — preenchidos via JS)  --}}
        {{-- ============================================================ --}}
        <div class="py-6 border-b border-gray-100" id="secao_dados_contrato" style="display: none;">
            <h3 class="flex items-center gap-2 text-lg font-medium text-gray-700">
                <i class="fas fa-file-contract text-[#009496]"></i> Dados do Contrato
                <span id="badge_origem" class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800"></span>
            </h3>

            <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Secretaria</label>
                    <input type="text" id="info_secretaria" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Nº Contrato</label>
                    <input type="text" id="info_numero_contrato" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Nº Processo</label>
                    <input type="text" id="info_numero_processo" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" readonly>
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-500">Objeto</label>
                    <textarea id="info_objeto" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" rows="2" readonly></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Modalidade</label>
                    <input type="text" id="info_modalidade" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Razão Social</label>
                    <input type="text" id="info_razao_social" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">CNPJ</label>
                    <input type="text" id="info_cnpj" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Endereço</label>
                    <input type="text" id="info_endereco" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Responsável pela Empresa</label>
                    <input type="text" id="info_representante" class="w-full mt-1 px-3 py-2 rounded-lg readonly-field" readonly>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- SEÇÃO 3: DADOS DA OCORRÊNCIA                                --}}
        {{-- ============================================================ --}}
        <div class="py-6 border-b border-gray-100">
            <h3 class="flex items-center gap-2 text-lg font-medium text-gray-700">
                <i class="fas fa-triangle-exclamation text-[#009496]"></i> Dados da Ocorrência
            </h3>

            <div class="grid grid-cols-1 gap-6 mt-4 md:grid-cols-3">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        <span class="text-red-500">*</span> Número da Ocorrência
                    </label>
                    <input type="text" name="numero_ocorrencia" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                           value="{{ old('numero_ocorrencia') }}" placeholder="Ex: 001/2026">
                    @error('numero_ocorrencia')
                        <p class="mt-1 text-sm text-red-600"><i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        <span class="text-red-500">*</span> Data da Ocorrência
                    </label>
                    <input type="date" name="data_ocorrencia" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                           value="{{ old('data_ocorrencia') }}">
                    @error('data_ocorrencia')
                        <p class="mt-1 text-sm text-red-600"><i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Local</label>
                    <input type="text" name="local"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                           value="{{ old('local') }}" placeholder="Ex: Sede da Secretaria de Obras">
                </div>
            </div>

            <div class="mt-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Descrição do Fato
                </label>
                <textarea name="descricao_fato" rows="4" required
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                          placeholder="Descreva detalhadamente o fato observado...">{{ old('descricao_fato') }}</textarea>
                @error('descricao_fato')
                    <p class="mt-1 text-sm text-red-600"><i class="mr-1 fas fa-exclamation-circle"></i>{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    <i class="mr-1 fas fa-paperclip text-[#009496]"></i> Anexar Fotografias ou Documentos (PDF)
                </label>
                <input type="file" name="anexos_fato[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp"
                       class="block w-full px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#009496] file:text-white hover:file:bg-[#244853] transition-colors cursor-pointer focus:ring-2 focus:ring-[#009496] focus:border-[#009496]">
                <p class="mt-2 text-xs text-gray-500">
                    <i class="mr-1 fas fa-info-circle"></i> Opcional. Formatos aceitos: PDF, JPEG, PNG ou WEBP, até 10MB por arquivo.
                </p>
            </div>

            <div class="mt-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">Obrigação Descumprida</label>
                <textarea name="obrigacao_descumprida" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                          placeholder="Descreva a obrigação contratual descumprida...">{{ old('obrigacao_descumprida') }}</textarea>
            </div>

            <div class="mt-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">Prazo para Resposta / Solução</label>
                <input type="text" name="prazo_resposta"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                       value="{{ old('prazo_resposta') }}" placeholder="Ex: 10 dias úteis">
            </div>

            {{-- Meio de Comprovação --}}
            <div class="p-4 mt-4 border border-gray-200 rounded-lg bg-gray-50">
                <p class="mb-3 text-sm font-medium text-gray-700">Meio de Comprovação</p>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach(\App\Models\Ocorrencia::TIPOS_COMPROVACAO as $chave => $rotulo)
                        <label class="flex items-start gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="tipo_comprovacao[{{ $chave }}]" value="1"
                                   class="mt-0.5 rounded text-[#009496] focus:ring-[#009496]"
                                   {{ old('tipo_comprovacao.'.$chave) ? 'checked' : '' }}
                                   {{ $chave === 'outros' ? 'id=tipo_comprovacao_outros_check' : '' }}>
                            {{ $rotulo }}
                        </label>
                    @endforeach
                </div>
                <div class="mt-3 campo-variavel {{ old('tipo_comprovacao.outros') ? '' : 'campo-oculto' }}" id="campo_outros_descricao">
                    <input type="text" name="tipo_comprovacao_outro"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496] transition-colors"
                           value="{{ old('tipo_comprovacao_outro') }}" placeholder="Especifique o outro meio de comprovação">
                </div>
            </div>

            {{-- Situação --}}
            <div class="mt-6">
                <label class="block mb-2 text-sm font-medium text-gray-700">Situação</label>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    @foreach(\App\Enums\SituacaoOcorrenciaEnum::cases() as $situacao)
                        <label class="flex items-center gap-2 p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-[#009496] transition-all has-[:checked]:border-[#009496] has-[:checked]:bg-[#f0fdfa]">
                            <input type="radio" name="situacao" value="{{ $situacao->value }}"
                                   class="text-[#009496] focus:ring-[#009496]"
                                   {{ old('situacao') === $situacao->value ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">{{ $situacao->getDisplayName() }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- BOTÕES DE AÇÃO                                              --}}
        {{-- ============================================================ --}}
        <div class="flex justify-end gap-3 pt-6">
            <a href="{{ route('admin.ocorrencias.selecionar-contrato') }}"
                class="flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors bg-gray-100 rounded-lg hover:bg-gray-200 shadow-sm">
                <i class="mr-2 fas fa-times"></i> Cancelar
            </a>
            <button type="submit" onclick="document.getElementById('status_input').value = 'rascunho';"
                    class="flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm">
                <i class="mr-2 fas fa-file-pen"></i> Salvar Rascunho
            </button>
            <button type="submit" onclick="document.getElementById('status_input').value = 'registrada';"
                    class="flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-white transition-colors bg-[#009496] rounded-lg hover:bg-[#244853] shadow-sm">
                <i class="mr-2 fas fa-save"></i> Registrar Ocorrência
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchContratoInput');
    const searchResults = document.getElementById('searchResults');
    const searchSpinner = document.getElementById('searchSpinner');
    const clearBtn = document.getElementById('clearSearch');
    const searchInputCont = document.getElementById('searchInputContainer');
    const selectedChip = document.getElementById('selectedChip');
    const chipTitle = document.getElementById('chipTitle');
    const chipSub = document.getElementById('chipSub');
    const chipRemove = document.getElementById('chipRemove');
    const fiscalizavelId = document.getElementById('fiscalizavel_id');
    const fiscalizavelType = document.getElementById('fiscalizavel_type');
    const helperCadastrar = document.getElementById('helper_cadastrar_contrato');

    const SEARCH_URL = '{{ route('admin.fiscalizacoes.buscar-contratos') }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';

    @if($contratoPreSelecionado ?? null)
        selecionarContrato({
            id: @json($contratoPreSelecionado['id']),
            numero_contrato: @json($contratoPreSelecionado['numero_contrato']),
            numero_processo: @json($contratoPreSelecionado['numero_processo']),
            objeto: @json($contratoPreSelecionado['objeto']),
            modalidade: @json($contratoPreSelecionado['modalidade']),
            secretaria: @json($contratoPreSelecionado['secretaria']),
            razao_social: @json($contratoPreSelecionado['razao_social']),
            cnpj: @json($contratoPreSelecionado['cnpj']),
            endereco: @json($contratoPreSelecionado['endereco']),
            representante: @json($contratoPreSelecionado['representante']),
            origem: @json($contratoPreSelecionado['origem'])
        });
    @endif

    let debounceTimer = null;
    let currentAbortController = null;
    let ultimosResultados = [];

    searchInput.addEventListener('input', function () {
        const termo = this.value.trim();
        clearBtn.classList.toggle('active', termo.length > 0);

        if (currentAbortController) currentAbortController.abort();

        if (termo.length < 2) {
            searchResults.classList.remove('active');
            searchResults.innerHTML = '';
            searchSpinner.classList.remove('active');
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () { buscarContratos(termo); }, 350);
    });

    async function buscarContratos(termo) {
        searchSpinner.classList.add('active');
        clearBtn.classList.remove('active');
        currentAbortController = new AbortController();

        try {
            const url = SEARCH_URL + '?q=' + encodeURIComponent(termo);
            const response = await fetch(url, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                signal: currentAbortController.signal
            });

            if (!response.ok) throw new Error('Erro na requisição: ' + response.status);

            const data = await response.json();
            renderizarResultados(data.results || []);
        } catch (err) {
            if (err.name === 'AbortError') return;
            searchResults.innerHTML = `
                <div class="search-results-empty">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Erro ao buscar contratos. Tente novamente.</p>
                </div>`;
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
                </div>`;
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
                <div class="search-result-item" data-index="${index}" style="padding: 14px 16px; border-left: 4px solid transparent; transition: all 0.2s;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                        <span style="font-weight: 700; color: #115e59; font-size: 0.95rem;">
                            <i class="fas fa-file-signature mr-1" style="color: #009496;"></i>
                            ${escapeHtml(item.numero_contrato || 'S/N')}
                        </span>
                        <span class="badge-origem ${badgeClass}" style="font-size: 0.6rem; padding: 1px 8px;">${badgeText}</span>
                    </div>
                    <div style="font-size: 0.82rem; color: #374151; font-weight: 500; margin-bottom: 8px; line-height: 1.4;">
                        ${escapeHtml(truncate(item.objeto, 100))}
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 4px; padding-top: 8px; border-top: 1px solid #f3f4f6;">
                        <div class="result-detail" style="margin: 0;">
                            <i class="fas fa-building" style="color: #64748b; font-size: 0.75rem;"></i>
                            <span style="color: #475569; font-size: 0.78rem;"><strong>Empresa:</strong> ${escapeHtml(item.razao_social || '—')}</span>
                        </div>
                        <div style="display: flex; gap: 12px;">
                            <div class="result-detail" style="margin: 0;">
                                <i class="fas fa-id-card" style="color: #64748b; font-size: 0.75rem;"></i>
                                <span style="color: #64748b; font-size: 0.75rem;">${escapeHtml(item.cnpj || '—')}</span>
                            </div>
                            <div class="result-detail" style="margin: 0;">
                                <i class="fas fa-folder-open" style="color: #64748b; font-size: 0.75rem;"></i>
                                <span style="color: #64748b; font-size: 0.75rem;">Proc: ${escapeHtml(item.numero_processo || '—')}</span>
                            </div>
                        </div>
                    </div>
                </div>`;
        });

        searchResults.innerHTML = html;
        searchResults.classList.add('active');
        searchResults.querySelectorAll('.search-result-item').forEach(function (el) {
            el.addEventListener('mouseenter', () => el.style.borderLeftColor = '#009496');
            el.addEventListener('mouseleave', () => el.style.borderLeftColor = 'transparent');
            el.addEventListener('click', function () {
                selecionarContrato(ultimosResultados[Number(this.getAttribute('data-index'))]);
            });
        });
    }

    function selecionarContrato(data) {
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

        document.getElementById('secao_dados_contrato').style.display = 'block';

        chipTitle.textContent = (data.numero_contrato || 'S/N') + ' — ' + truncate(data.objeto, 45);
        chipSub.textContent = (data.razao_social || '—') + ' • ' + (data.origem || '');
        selectedChip.classList.add('active');

        searchInputCont.style.display = 'none';
        searchResults.classList.remove('active');
        searchInput.value = '';
        clearBtn.classList.remove('active');

        if (helperCadastrar) helperCadastrar.style.display = 'none';
    }

    function removerContrato() {
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
        if (!e.target.closest('#searchWrapper')) searchResults.classList.remove('active');
    });

    // Toggle do campo de texto livre "Outros" no Meio de Comprovação
    const checkOutros = document.getElementById('tipo_comprovacao_outros_check');
    const campoOutros = document.getElementById('campo_outros_descricao');
    if (checkOutros && campoOutros) {
        checkOutros.addEventListener('change', function () {
            campoOutros.classList.toggle('campo-oculto', !this.checked);
        });
    }

    document.getElementById('formOcorrencia').addEventListener('submit', function (e) {
        if (!fiscalizavelId.value) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Contrato Obrigatório',
                text: 'Por favor, selecione um contrato antes de salvar a ocorrência.',
                confirmButtonColor: '#009496',
            });
            return false;
        }
        return true;
    });

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
});
</script>

@endsection
