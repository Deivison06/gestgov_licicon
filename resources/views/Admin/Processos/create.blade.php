@extends('layouts.app')
@section('page-title', 'Gestão de Processos')
@section('page-subtitle', 'Preencha os dados do processo')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
<div class="py-6">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm rounded-xl">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-700">Informações do Processo</h3>
            </div>

            <div class="p-6">

                @if ($etp)
                <div class="flex items-center p-4 mb-6 border border-teal-200 rounded-lg bg-teal-50">
                    <i class="mr-3 fas fa-link text-[#009496]"></i>
                    <p class="text-sm font-medium text-teal-800">
                        Este processo será criado já vinculado ao
                        ETP-{{ str_pad($etp->id, 4, '0', STR_PAD_LEFT) }}/{{ $etp->created_at->format('y') }}.
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
                            <label for="prefeitura_id" class="block text-sm font-medium text-gray-700">Prefeitura</label>
                            <select name="prefeitura_id" id="prefeitura_id"
                                {{ $etp ? 'disabled' : '' }}
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496] {{ $etp ? 'bg-gray-100' : '' }}">
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
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- MODALIDADE --}}
                        <div>
                            <label for="modalidade" class="block text-sm font-medium text-gray-700">Modalidade</label>
                            <select name="modalidade" id="modalidade" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                <option value="">Selecione a modalidade</option>
                                @foreach (\App\Enums\ModalidadeEnum::cases() as $modalidade)
                                <option value="{{ $modalidade->value }}" {{ old('modalidade') == $modalidade->value ? 'selected' : '' }}>
                                    {{ $modalidade->getDisplayName() }}
                                </option>
                                @endforeach
                            </select>
                            @error('modalidade')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nº PROCESSO --}}
                        <div>
                            <label for="numero_processo" class="block text-sm font-medium text-gray-700">Nº DO PROCESSO ADMINISTRATIVO</label>
                            <input type="text" name="numero_processo" id="numero_processo" value="{{ old('numero_processo') }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                            @error('numero_processo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nº PROCEDIMENTO --}}
                        <div>
                            <label for="numero_procedimento" class="block text-sm font-medium text-gray-700">Nº do Procedimento</label>
                            <input type="text" name="numero_procedimento" id="numero_procedimento" value="{{ old('numero_procedimento') }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                            @error('numero_procedimento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- TIPO DE PROCEDIMENTO --}}
                        <div id="tipo_procedimento_wrapper">
                            <label for="tipo_procedimento" class="block mb-1 text-sm font-medium text-gray-700">Tipo de Procedimento</label>
                            <select name="tipo_procedimento" id="tipo_procedimento" class="block w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-[#009496] focus:border-[#009496] sm:text-sm">
                                <option value="">Selecione o tipo de procedimento</option>
                                @foreach (\App\Enums\TipoProcedimentoEnum::cases() as $enum)
                                <option value="{{ $enum->value }}" {{ old('tipo_procedimento') == $enum->value ? 'selected' : '' }}>
                                    {{ $enum->getDisplayName() }}
                                </option>
                                @endforeach
                            </select>
                            @error('tipo_procedimento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- TIPO DE CONTRATAÇÃO --}}
                        <div id="tipo_contratacao_wrapper">
                            <label for="tipo_contratacao" class="block mb-1 text-sm font-medium text-gray-700">Tipo de Contratação</label>
                            <select name="tipo_contratacao" id="tipo_contratacao" class="block w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-[#009496] focus:border-[#009496] sm:text-sm">
                                <option value="">Selecione o tipo de contratação</option>
                                @foreach (\App\Enums\TipoContratacaoEnum::cases() as $enum)
                                <option value="{{ $enum->value }}" {{ old('tipo_contratacao') == $enum->value ? 'selected' : '' }}>
                                    {{ $enum->getDisplayName() }}
                                </option>
                                @endforeach
                            </select>
                            @error('tipo_contratacao')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- RESPONSÁVEL POR NUMERAR O PROCESSO --}}
                        <div class="md:col-span-2">
                            <h4 class="mb-4 text-sm font-semibold text-gray-700">
                                Responsável por Numerar o Processo
                            </h4>

                            <div class="flex flex-col gap-4 md:flex-row">
                                {{-- Unidade --}}
                                <div class="w-full md:w-1/3">
                                    <select name="unidade_numeracao" id="unidade_numeracao" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                        <option value="">Selecione a unidade</option>
                                        {{-- As opções serão carregadas via JavaScript --}}
                                    </select>
                                    @error('unidade_numeracao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Responsável --}}
                                <div class="w-full md:w-1/3">
                                    <input type="text" name="responsavel_numeracao" id="responsavel_numeracao"
                                        value="{{ old('responsavel_numeracao') }}"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496] bg-gray-50">
                                    @error('responsavel_numeracao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Nº Portaria --}}
                                <div class="w-full md:w-1/3">
                                    <input type="text" name="portaria_numeracao" id="portaria_numeracao"
                                        value="{{ old('portaria_numeracao') }}"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496] bg-gray-50">
                                    @error('portaria_numeracao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- IDENTIFICAÇÃO DE CONTROLE / NOME RESUMIDO --}}
                        <div class="md:col-span-2">
                            <label for="nome_resumido" class="block text-sm font-medium text-gray-700">
                                Identificação de Controle / Nome Resumido
                                <span class="ml-1 text-xs text-gray-400 font-normal">— utilizado no quadro de planejamento</span>
                            </label>
                            <input type="text" name="nome_resumido" id="nome_resumido"
                                value="{{ old('nome_resumido') }}"
                                placeholder="Ex: Reforma da Escola X, Aquisição de Computadores..."
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                            @error('nome_resumido')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Flag de Dispensa de Licitação Fracassada (Aparece apenas na Dispensa) -->
                        <div id="bloco_dispensa_fracassada" class="col-span-1 md:col-span-2 hidden mt-2">
                            <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-md">
                                <label class="flex items-center space-x-2 text-sm font-medium text-yellow-800">
                                    <input type="checkbox" name="is_oriundo_fracassado" id="is_oriundo_fracassado" value="1"
                                        class="rounded border-yellow-400 text-yellow-600 focus:ring-yellow-500 w-5 h-5">
                                    <span>Processo oriundo de certame fracassado (Art. 75, III, alínea "a")</span>
                                </label>

                                <div id="selecao_processo_fracassado" class="mt-4 hidden">
                                    <label for="processo_fracassado_id" class="block text-sm font-medium text-gray-700">Selecione o Certame Fracassado</label>
                                    <select id="processo_fracassado_id" name="processo_fracassado_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <!-- Preenchido via AJAX -->
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">Ao selecionar, o tipo e objeto da contratação serão preenchidos automaticamente e os dados originais serão herdados.</p>
                                </div>
                            </div>
                        </div>

                        {{-- OBJETO --}}
                        <div class="md:col-span-2">
                            <label for="objeto" class="block text-sm font-medium text-gray-700">Objeto</label>
                            <textarea name="objeto" id="objeto" rows="4" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">{{ old('objeto', $etp->objeto_licitacao ?? '') }}</textarea>
                            @error('objeto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- BOTÕES --}}
                    <div class="flex justify-end mt-6 space-x-4">
                        <a href="{{ route('admin.processos.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Cancelar
                        </a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-[#062F43] rounded-md hover:bg-[#244853]">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Configuração do TinyMCE
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

        // Dados das unidades organizadas por prefeitura
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

    // Função para carregar unidades baseadas na prefeitura selecionada
    function carregarUnidades() {
        const prefeituraId = prefeituraSelect.value;

        // Limpar selects anteriores
        unidadeSelect.innerHTML = '<option value="">Selecione a unidade</option>';
        responsavelInput.value = '';
        portariaInput.value = '';

        if (prefeituraId && unidadesPorPrefeitura[prefeituraId]) {
            // Adicionar opções das unidades da prefeitura selecionada
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

    // Event listener para mudança na prefeitura
    prefeituraSelect.addEventListener('change', function() {
        carregarUnidades();
        carregarNumeroProcesso();
    });

    function carregarNumeroProcesso() {
        const prefeituraId = prefeituraSelect.value;
        const inputNumero = document.getElementById('numero_processo');

        if (prefeituraId) {
            // Se o campo estiver vazio ou for um valor que parece novo, buscamos o próximo
            // Isso evita sobrescrever um valor que o usuário digitou manualmente e deu erro de validação
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
                    .catch(error => {
                        console.error('Erro ao gerar número:', error);
                        inputNumero.placeholder = '';
                    });
            }
        }
    }

    // Event listener para mudança na unidade
    unidadeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption) {
            responsavelInput.value = selectedOption.getAttribute('data-responsavel') || '';
            portariaInput.value = selectedOption.getAttribute('data-portaria') || '';
        }
    });

    // Carregar unidades iniciais se já houver uma prefeitura selecionada
    if (prefeituraSelect.value) {
        carregarUnidades();
        
        // Se não houver erro de validação (valor antigo), carrega o número
        if (!"{{ old('numero_processo') }}") {
            carregarNumeroProcesso();
        }

        // Se houver um valor antigo (old) para unidade, restaurá-lo
        const oldUnidade = "{{ old('unidade_numeracao') }}";
        if (oldUnidade) {
            setTimeout(() => {
                unidadeSelect.value = oldUnidade;
                unidadeSelect.dispatchEvent(new Event('change'));
            }, 100);
        }
    }
        // Ocultar campos tipo quando modalidade for "Concorrência"
        const modalidadeSelect = document.getElementById('modalidade');
        const tipoProcedimentoSelect = document.getElementById('tipo_procedimento');

        const tipoProcedimentoDiv = document.getElementById('tipo_procedimento_wrapper');
        const tipoContratacaoDiv = document.getElementById('tipo_contratacao_wrapper');

        function atualizarVisibilidadeTipos() {
            const modalidade = modalidadeSelect.value;
            const tipoProcedimento = tipoProcedimentoSelect.value;

            // Reset padrão
            tipoProcedimentoDiv.style.display = '';
            tipoContratacaoDiv.style.display = '';

            // 1 = CONCORRÊNCIA
            if (modalidade === "1") {
                tipoProcedimentoDiv.style.display = 'none';
                tipoContratacaoDiv.style.display = 'none';
                return;
            }

            // 2 = DISPENSA
            if (modalidade === "2") {
                // 3 = OBRA
                if (tipoProcedimento === "3") {
                    tipoContratacaoDiv.style.display = 'none';
                }
            }
        }

        modalidadeSelect.addEventListener('change', atualizarVisibilidadeTipos);
        tipoProcedimentoSelect.addEventListener('change', atualizarVisibilidadeTipos);
        atualizarVisibilidadeTipos();

        // ===== DISPENSA FRACASSADA (JS puro — o projeto não carrega jQuery/select2) =====
        const modalidadeEl      = document.getElementById('modalidade');
        const blocoFracassada   = document.getElementById('bloco_dispensa_fracassada');
        const chkOriundo        = document.getElementById('is_oriundo_fracassado');
        const selecaoFracassado = document.getElementById('selecao_processo_fracassado');
        const selectFracassado  = document.getElementById('processo_fracassado_id');
        const objetoEl          = document.getElementById('objeto');
        const tipoProcEl        = document.getElementById('tipo_procedimento');
        const fracassadosUrl    = '{{ route('admin.api.processos.fracassados') }}';

        let fracassadosCache = [];

        function prefeituraIdAtual() {
            const el = document.getElementById('prefeitura_id') || document.querySelector('[name="prefeitura_id"]');
            return el ? el.value : '';
        }

        async function carregarFracassados() {
            const prefId = prefeituraIdAtual();
            if (!prefId) {
                selectFracassado.innerHTML = '<option value="">Selecione a prefeitura primeiro</option>';
                return;
            }
            selectFracassado.innerHTML = '<option value="">Carregando...</option>';
            try {
                const resp = await fetch(fracassadosUrl + '?prefeitura_id=' + encodeURIComponent(prefId), {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await resp.json();
                fracassadosCache = Array.isArray(data) ? data : [];
                if (fracassadosCache.length === 0) {
                    selectFracassado.innerHTML = '<option value="">Nenhum certame fracassado/cancelado encontrado</option>';
                    return;
                }
                selectFracassado.innerHTML = '<option value="">Selecione o certame...</option>' +
                    fracassadosCache.map(p =>
                        `<option value="${p.id}">${(p.text || '').replace(/"/g, '&quot;')}</option>`
                    ).join('');
            } catch (e) {
                selectFracassado.innerHTML = '<option value="">Erro ao carregar os certames</option>';
            }
        }

        // Mostra/oculta o bloco conforme a modalidade (2 = DISPENSA)
        function toggleBlocoFracassada() {
            if (modalidadeEl.value === '2') {
                blocoFracassada.classList.remove('hidden');
            } else {
                blocoFracassada.classList.add('hidden');
                if (chkOriundo.checked) {
                    chkOriundo.checked = false;
                    chkOriundo.dispatchEvent(new Event('change'));
                }
            }
        }
        modalidadeEl.addEventListener('change', toggleBlocoFracassada);
        toggleBlocoFracassada(); // estado inicial (respeita old() após erro de validação)

        // Checkbox: mostra o select e carrega a lista
        chkOriundo.addEventListener('change', function () {
            if (this.checked) {
                selecaoFracassado.classList.remove('hidden');
                selectFracassado.required = true;
                carregarFracassados();
            } else {
                selecaoFracassado.classList.add('hidden');
                selectFracassado.required = false;
                selectFracassado.value = '';
            }
        });

        // Recarrega a lista se a prefeitura mudar com o checkbox marcado
        const prefSelectEl = document.getElementById('prefeitura_id');
        if (prefSelectEl) {
            prefSelectEl.addEventListener('change', function () {
                if (chkOriundo.checked) carregarFracassados();
            });
        }

        // Autopreenchimento ao escolher o certame fracassado
        selectFracassado.addEventListener('change', function () {
            const proc = fracassadosCache.find(p => String(p.id) === String(this.value));
            if (!proc) return;
            if (proc.objeto) {
                const editor = window.tinymce && tinymce.get('objeto');
                if (editor) { editor.setContent(proc.objeto); }
                else if (objetoEl) { objetoEl.value = proc.objeto; }
            }
            if (proc.tipo_procedimento && tipoProcEl) {
                tipoProcEl.value = proc.tipo_procedimento;
                tipoProcEl.dispatchEvent(new Event('change'));
            }
        });
    });
</script>
@endsection
