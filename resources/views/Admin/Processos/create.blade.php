@extends('layouts.app')
@section('page-title', 'Gestão de Processos')
@section('page-subtitle', 'Preencha os dados do processo')

@section('content')
<!-- TomSelect CSS/JS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>

<div class="py-6">
    <div class="card !rounded-2xl overflow-hidden">
        <div class="card-h">
            <h3 class="text-lg font-bold text-[var(--ink)] flex items-center gap-2">
                <i class="fas fa-folder-plus text-[var(--mint)]"></i> Informações do Processo
            </h3>
        </div>

        <div class="p-8">
            @if ($etp)
            <div class="flex items-center p-4 mb-6 border border-[var(--line)] rounded-xl bg-[var(--card-3)]/70 text-[var(--mint)]">
                <i class="mr-3 fas fa-link text-[var(--mint)]"></i>
                <p class="text-sm font-medium text-[var(--ink)]">
                    Este processo será criado já vinculado ao
                    <span class="font-bold text-[var(--mint)]">ETP-{{ str_pad($etp->id, 4, '0', STR_PAD_LEFT) }}/{{ $etp->created_at->format('y') }}</span>.
                </p>
            </div>
            @endif

            <form action="{{ route('admin.processos.store') }}" method="POST">
                @csrf

                @if ($etp)
                <input type="hidden" name="etp_id" value="{{ $etp->id }}">
                @endif

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {{-- PREFEITURA --}}
                    <div>
                        <label for="prefeitura_id" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Prefeitura <span class="text-red-400">*</span></label>
                        <select name="prefeitura_id" id="prefeitura_id"
                            {{ $etp ? 'disabled' : '' }}
                            class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors {{ $etp ? 'opacity-60 cursor-not-allowed' : '' }}">
                            <option value="">Selecione a prefeitura</option>
                            @foreach ($prefeituras as $prefeitura)
                            <option value="{{ $prefeitura->id }}"
                                {{ ($etp ? $etp->prefeitura_id : old('prefeitura_id')) == $prefeitura->id ? 'selected' : '' }}>
                                {{ $prefeitura->nome }}
                            </option>
                            @endforeach
                        </select>
                        @if ($etp)
                        <input type="hidden" name="prefeitura_id" value="{{ $etp->prefeitura_id }}">
                        @endif
                        @error('prefeitura_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- MODALIDADE --}}
                    <div>
                        <label for="modalidade" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Modalidade <span class="text-red-400">*</span></label>
                        <select name="modalidade" id="modalidade" class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                            <option value="">Selecione a modalidade</option>
                            @foreach (\App\Enums\ModalidadeEnum::cases() as $modalidade)
                            <option value="{{ $modalidade->value }}" {{ old('modalidade') == $modalidade->value ? 'selected' : '' }}>
                                {{ $modalidade->getDisplayName() }}
                            </option>
                            @endforeach
                        </select>
                        @error('modalidade')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nº PROCESSO --}}
                    <div>
                        <label for="numero_processo" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Nº do Processo Administrativo <span class="text-red-400">*</span></label>
                        <input type="text" name="numero_processo" id="numero_processo" value="{{ old('numero_processo') }}"
                            class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                        @error('numero_processo')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nº PROCEDIMENTO --}}
                    <div>
                        <label for="numero_procedimento" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Nº do Procedimento</label>
                        <input type="text" name="numero_procedimento" id="numero_procedimento" value="{{ old('numero_procedimento') }}"
                            class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                        @error('numero_procedimento')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- TIPO DE PROCEDIMENTO --}}
                    <div id="tipo_procedimento_wrapper">
                        <label for="tipo_procedimento" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Tipo de Procedimento</label>
                        <select name="tipo_procedimento" id="tipo_procedimento"
                            class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                            <option value="">Selecione o tipo de procedimento</option>
                            @foreach (\App\Enums\TipoProcedimentoEnum::cases() as $enum)
                            <option value="{{ $enum->value }}" {{ old('tipo_procedimento') == $enum->value ? 'selected' : '' }}>
                                {{ $enum->getDisplayName() }}
                            </option>
                            @endforeach
                        </select>
                        @error('tipo_procedimento')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- TIPO DE CONTRATAÇÃO --}}
                    <div id="tipo_contratacao_wrapper">
                        <label for="tipo_contratacao" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Tipo de Contratação</label>
                        <select name="tipo_contratacao" id="tipo_contratacao"
                            class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                            <option value="">Selecione o tipo de contratação</option>
                            @foreach (\App\Enums\TipoContratacaoEnum::cases() as $enum)
                            <option value="{{ $enum->value }}" {{ old('tipo_contratacao') == $enum->value ? 'selected' : '' }}>
                                {{ $enum->getDisplayName() }}
                            </option>
                            @endforeach
                        </select>
                        @error('tipo_contratacao')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- RESPONSÁVEL POR NUMERAR O PROCESSO --}}
                    <div class="md:col-span-2 p-5 bg-[var(--card-2)] border border-[var(--line)] rounded-xl">
                        <h4 class="mb-3 text-sm font-bold text-[var(--ink)] border-b border-[var(--line)] pb-2 flex items-center gap-2">
                            <i class="fas fa-sort-numeric-up text-[var(--mint)]"></i> Responsável por Numerar o Processo
                        </h4>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label for="unidade_numeracao" class="block mb-1 text-xs font-semibold text-[var(--ink-2)]">Unidade</label>
                                <select name="unidade_numeracao" id="unidade_numeracao"
                                    class="block w-full px-3 py-2 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)]">
                                    <option value="">Selecione a unidade</option>
                                </select>
                                @error('unidade_numeracao')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="responsavel_numeracao" class="block mb-1 text-xs font-semibold text-[var(--ink-2)]">Responsável</label>
                                <input type="text" name="responsavel_numeracao" id="responsavel_numeracao"
                                    value="{{ old('responsavel_numeracao') }}"
                                    class="block w-full px-3 py-2 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)]">
                                @error('responsavel_numeracao')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="portaria_numeracao" class="block mb-1 text-xs font-semibold text-[var(--ink-2)]">Portaria</label>
                                <input type="text" name="portaria_numeracao" id="portaria_numeracao"
                                    value="{{ old('portaria_numeracao') }}"
                                    class="block w-full px-3 py-2 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)]">
                                @error('portaria_numeracao')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- IDENTIFICAÇÃO DE CONTROLE / NOME RESUMIDO --}}
                    <div class="md:col-span-2">
                        <label for="nome_resumido" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">
                            Identificação de Controle / Nome Resumido
                            <span class="ml-1 text-xs text-[var(--ink-2)]/60 font-normal">— utilizado no quadro de planejamento</span>
                        </label>
                        <input type="text" name="nome_resumido" id="nome_resumido"
                            value="{{ old('nome_resumido') }}"
                            placeholder="Ex: Reforma da Escola X, Aquisição de Computadores..."
                            class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder:text-[var(--ink-2)]/40 transition-colors">
                        @error('nome_resumido')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Flag de Dispensa de Licitação Fracassada -->
                    <div id="bloco_dispensa_fracassada" class="col-span-1 md:col-span-2 hidden mt-2">
                        <div class="bg-amber-950/30 border border-amber-500/30 p-4 rounded-xl">
                            <label class="flex items-center space-x-2 text-sm font-medium text-amber-300 cursor-pointer">
                                <input type="checkbox" name="is_oriundo_fracassado" id="is_oriundo_fracassado" value="1"
                                    class="rounded border-[var(--line)] bg-[var(--bg)] text-[var(--mint)] focus:ring-[var(--mint)] w-5 h-5">
                                <span>Processo oriundo de certame fracassado (Art. 75, III, alínea "a")</span>
                            </label>

                            <div id="selecao_processo_fracassado" class="mt-4 hidden">
                                <label for="processo_fracassado_id" class="block text-sm font-semibold text-[var(--ink-2)] mb-1">Selecione ou Pesquise o Certame Fracassado</label>
                                
                                <select id="processo_fracassado_id" name="processo_fracassado_id" placeholder="Digite para buscar pelo número ou objeto..." class="mt-1 block w-full rounded-xl bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] text-sm">
                                </select>
                                
                                <p class="mt-1 text-xs text-[var(--ink-2)]">Ao selecionar, o tipo e objeto da contratação serão preenchidos automaticamente.</p>
                            </div>
                        </div>
                    </div>

                    {{-- OBJETO --}}
                    <div class="md:col-span-2">
                        <label for="objeto" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Objeto</label>
                        <textarea name="objeto" id="objeto" rows="4" class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">{{ old('objeto', $etp->objeto_licitacao ?? '') }}</textarea>
                        @error('objeto')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- BOTÕES --}}
                <div class="flex justify-end gap-3 pt-6 mt-8 border-t border-[var(--line)]">
                    <a href="{{ route('admin.processos.index') }}" class="px-5 py-2.5 text-sm font-semibold text-[var(--ink)] bg-[var(--card)] border border-[var(--line)] rounded-xl hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] transition-colors shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-sm hover:scale-105">
                        <i class="fas fa-save"></i>
                        Salvar Processo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // TinyMCE
        tinymce.init({
            selector: 'textarea#objeto',
            plugins: 'lists link table code charmap emoticons',
            toolbar: 'undo redo | bold italic underline | bullist numlist | link table | emoticons charmap | code',
            menubar: false,
            branding: false,
            height: 300,
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });

        // Unidades por prefeitura
        const unidadesPorPrefeitura = {!! $prefeituras->mapWithKeys(function($prefeitura) {
            return [
                $prefeitura->id => $prefeitura->unidades->map(function($unidade) {
                    return [
                        'id' => $unidade->id,
                        'nome' => $unidade->nome,
                        'responsavel' => $unidade->servidor_responsavel,
                        'portaria' => $unidade->numero_portaria,
                    ];
                })
            ];
        })->toJson() !!};

        const prefeituraSelect = document.getElementById('prefeitura_id');
        const unidadeSelect = document.getElementById('unidade_numeracao');
        const responsavelInput = document.getElementById('responsavel_numeracao');
        const portariaInput = document.getElementById('portaria_numeracao');

        function carregarUnidades() {
            const prefeituraId = prefeituraSelect.value;
            unidadeSelect.innerHTML = '<option value="">Selecione a unidade</option>';
            responsavelInput.value = '';
            portariaInput.value = '';

            if (prefeituraId && unidadesPorPrefeitura[prefeituraId]) {
                unidadesPorPrefeitura[prefeituraId].forEach(unidade => {
                    const option = document.createElement('option');
                    option.value = unidade.nome;
                    option.textContent = unidade.nome;
                    option.setAttribute('data-responsavel', unidade.responsavel);
                    option.setAttribute('data-portaria', unidade.portaria);
                    unidadeSelect.appendChild(option);
                });
            }
        }

        prefeituraSelect.addEventListener('change', function() {
            carregarUnidades();
            carregarNumeroProcesso();
        });

        function carregarNumeroProcesso() {
            const prefeituraId = prefeituraSelect.value;
            const inputNumero = document.getElementById('numero_processo');

            if (prefeituraId) {
                const valorAtual = inputNumero.value;
                const ehValorPadrao = !valorAtual || valorAtual.includes('/');

                if (ehValorPadrao) {
                    inputNumero.placeholder = 'Gerando número...';
                    fetch(`{{ route('admin.processos.gerar-numeros') }}?prefeitura_id=${prefeituraId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                inputNumero.value = data.numero_processo;
                                inputNumero.placeholder = '';
                            }
                        })
                        .catch(() => { inputNumero.placeholder = ''; });
                }
            }
        }

        unidadeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption) {
                responsavelInput.value = selectedOption.getAttribute('data-responsavel') || '';
                portariaInput.value = selectedOption.getAttribute('data-portaria') || '';
            }
        });

        if (prefeituraSelect.value) {
            carregarUnidades();
            if (!"{{ old('numero_processo') }}") carregarNumeroProcesso();
            const oldUnidade = "{{ old('unidade_numeracao') }}";
            if (oldUnidade) {
                setTimeout(() => {
                    unidadeSelect.value = oldUnidade;
                    unidadeSelect.dispatchEvent(new Event('change'));
                }, 100);
            }
        }

        // Modalidade x Tipos
        const modalidadeSelect = document.getElementById('modalidade');
        const tipoProcedimentoSelect = document.getElementById('tipo_procedimento');
        const tipoProcedimentoDiv = document.getElementById('tipo_procedimento_wrapper');
        const tipoContratacaoDiv = document.getElementById('tipo_contratacao_wrapper');

        function atualizarVisibilidadeTipos() {
            const modalidade = modalidadeSelect.value;
            const tipoProcedimento = tipoProcedimentoSelect.value;

            tipoProcedimentoDiv.style.display = '';
            tipoContratacaoDiv.style.display = '';

            if (modalidade === "1") {
                tipoProcedimentoDiv.style.display = 'none';
                tipoContratacaoDiv.style.display = 'none';
                return;
            }

            if (modalidade === "2" && tipoProcedimento === "3") {
                tipoContratacaoDiv.style.display = 'none';
            }
        }

        modalidadeSelect.addEventListener('change', atualizarVisibilidadeTipos);
        tipoProcedimentoSelect.addEventListener('change', atualizarVisibilidadeTipos);
        atualizarVisibilidadeTipos();

        // ===== DISPENSA FRACASSADA COM TOMSELECT (BUSCA DINÂMICA) =====
        const blocoFracassada   = document.getElementById('bloco_dispensa_fracassada');
        const chkOriundo        = document.getElementById('is_oriundo_fracassado');
        const selecaoFracassado = document.getElementById('selecao_processo_fracassado');
        const selectFracassado  = document.getElementById('processo_fracassado_id');
        const objetoEl          = document.getElementById('objeto');
        const tipoProcEl        = document.getElementById('tipo_procedimento');
        const fracassadosUrl    = '{{ route('admin.api.processos.fracassados') }}';

        let tomSelectInstance = null;

        function initTomSelect() {
            if (tomSelectInstance) return;

            tomSelectInstance = new TomSelect('#processo_fracassado_id', {
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                placeholder: 'Digite para buscar por nº do processo, procedimento ou objeto...',
                loadThrottle: 300, // Aguarda 300ms após o usuário parar de digitar para fazer o fetch
                load: function(query, callback) {
                    const prefId = prefeituraSelect.value;
                    if (!prefId) return callback();

                    // Envia prefeitura_id e o termo digitado em 'q'
                    const url = `${fracassadosUrl}?prefeitura_id=${encodeURIComponent(prefId)}&q=${encodeURIComponent(query)}`;
                    
                    fetch(url)
                        .then(response => response.json())
                        .then(json => {
                            callback(json);
                        })
                        .catch(() => {
                            callback();
                        });
                },
                onChange: function(value) {
                    if (!value) return;
                    const item = this.options[value];
                    if (item) {
                        if (item.objeto) {
                            const editor = window.tinymce && tinymce.get('objeto');
                            if (editor) { 
                                editor.setContent(item.objeto); 
                            } else if (objetoEl) { 
                                objetoEl.value = item.objeto; 
                            }
                        }
                        if (item.tipo_procedimento && tipoProcEl) {
                            tipoProcEl.value = item.tipo_procedimento;
                            tipoProcEl.dispatchEvent(new Event('change'));
                        }
                    }
                }
            });
        }

        function toggleBlocoFracassada() {
            if (modalidadeSelect.value === '2') {
                blocoFracassada.classList.remove('hidden');
            } else {
                blocoFracassada.classList.add('hidden');
                if (chkOriundo.checked) {
                    chkOriundo.checked = false;
                    chkOriundo.dispatchEvent(new Event('change'));
                }
            }
        }

        modalidadeSelect.addEventListener('change', toggleBlocoFracassada);
        toggleBlocoFracassada();

        chkOriundo.addEventListener('change', function () {
            if (this.checked) {
                selecaoFracassado.classList.remove('hidden');
                initTomSelect();
                if (tomSelectInstance) {
                    tomSelectInstance.clearOptions();
                    tomSelectInstance.load('');
                }
            } else {
                selecaoFracassado.classList.add('hidden');
                if (tomSelectInstance) {
                    tomSelectInstance.clear();
                }
            }
        });

        prefeituraSelect.addEventListener('change', function () {
            if (chkOriundo.checked && tomSelectInstance) {
                tomSelectInstance.clear();
                tomSelectInstance.clearOptions();
                tomSelectInstance.load('');
            }
        });
    });
</script>
@endsection