@extends('layouts.app')

@section('page-title', 'Gestão de Contratos')
@section('page-subtitle', 'Editando Contrato: ' . $contrato->numero_processo)

@section('content')

@if ($errors->any())
    <div class="p-4 mb-6 rounded-xl bg-[var(--bad-bg)] border border-[var(--bad-border)]">
        <p class="text-sm font-semibold text-[var(--bad-strong)]">Atenção aos erros:</p>
        <ul class="mt-2 text-sm text-[var(--bad-strong)] list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="p-4 mb-6 rounded-xl bg-[var(--card-3)] border border-[var(--line)] text-[var(--mint)] font-semibold">{{ session('success') }}</div>
@endif

{{-- ================= SEÇÃO DE DADOS DO CONTRATO ================= --}}
<div class="mb-6 card !rounded-2xl overflow-hidden">
    <div class="px-6 py-4 border-b border-[var(--line)]">
        <h3 class="flex items-center gap-2 text-lg font-bold text-[var(--ink)]">
            <i class="fas fa-file-signature text-[var(--mint)]"></i> Dados do Contrato
        </h3>
    </div>

    <form action="{{ route('admin.contratos.update', $contrato->id) }}" method="POST" enctype="multipart/form-data" class="px-6 py-6">
        @csrf
        @method('PUT')

        {{-- Linha 1: Prefeitura --}}
        <div class="mb-6">
            @php
                $isPrefeituraUser = auth()->user()->hasRole('prefeitura') && auth()->user()->prefeitura_id;
            @endphp

            <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">Prefeitura</label>
            @if($isPrefeituraUser)
                <input type="hidden" name="prefeitura_id" value="{{ auth()->user()->prefeitura_id }}">
                <select disabled class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] rounded-xl text-[var(--ink-2)] cursor-not-allowed">
                    <option value="{{ $contrato->prefeitura_id }}" selected>
                        {{ $contrato->prefeitura->nome }}
                    </option>
                </select>
            @else
                <select name="prefeitura_id" id="prefeitura_id" class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                    <option value="">Selecione a prefeitura</option>
                    @foreach ($prefeituras as $prefeitura)
                    <option value="{{ $prefeitura->id }}" {{ $contrato->prefeitura_id == $prefeitura->id ? 'selected' : '' }}>
                        {{ $prefeitura->nome }}
                    </option>
                    @endforeach
                </select>
            @endif
        </div>

        {{-- Linha 2: Identificação Básica --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-4 mb-6">
            <div>
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">Tipo de Contrato <span class="text-[var(--mint)]">*</span></label>
                <select name="tipo_contrato" required class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                    <option value="">Selecione</option>
                    <option value="Compras" {{ $contrato->tipo_contrato == 'Compras' ? 'selected' : '' }}>Compras</option>
                    <option value="Serviço" {{ $contrato->tipo_contrato == 'Serviço' ? 'selected' : '' }}>Serviço</option>
                    <option value="Obra" {{ $contrato->tipo_contrato == 'Obra' ? 'selected' : '' }}>Obra</option>
                </select>
                @error('tipo_contrato')
                <p class="mt-1 text-xs text-[var(--bad-strong)]">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">Nº Processo <span class="text-[var(--mint)]">*</span></label>
                <input type="text" name="numero_processo" value="{{ $contrato->numero_processo }}" required
                        class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder:text-[var(--ink-2)]/40 transition-colors">
                @error('numero_processo')
                <p class="mt-1 text-xs text-[var(--bad-strong)]">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">Nº Procedimento</label>
                <input type="text" name="numero_procedimento" value="{{ $contrato->numero_procedimento }}"
                        class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder:text-[var(--ink-2)]/40 transition-colors">
                @error('numero_procedimento')
                <p class="mt-1 text-xs text-[var(--bad-strong)]">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">Nº Contrato</label>
                <input type="text" name="numero_contrato" value="{{ $contrato->numero_contrato }}"
                        class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder:text-[var(--ink-2)]/40 transition-colors">
                @error('numero_contrato')
                <p class="mt-1 text-xs text-[var(--bad-strong)]">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Linha 3: Modalidade e Contratante --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 mb-6">
            <div>
                <label for="modalidade" class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">Modalidade</label>
                <select name="modalidade" id="modalidade" class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                    <option value="">Selecione a modalidade</option>
                    @foreach (\App\Enums\ModalidadeEnum::cases() as $modalidade)
                        <option value="{{ $modalidade->value }}" {{ old('modalidade', $contrato->modalidade?->value ?? $contrato->modalidade) == $modalidade->value ? 'selected' : '' }}>
                            {{ $modalidade->getDisplayName() }}
                        </option>
                    @endforeach
                </select>
                @error('modalidade')
                <p class="mt-1 text-xs text-[var(--bad-strong)]">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">Secretaria / Órgão Contratante <span class="text-[var(--mint)]">*</span></label>
                <select name="unidade_id" id="unidade_id" required class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                    <option value="">@if($isPrefeituraUser) Selecione a secretaria @else Primeiro selecione a prefeitura @endif</option>
                    {{-- As opções serão carregadas via JavaScript --}}
                </select>
                @error('unidade_id')
                <p class="mt-1 text-xs text-[var(--bad-strong)]">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Linha 4: Vigência e Valores --}}
        <div class="p-5 bg-[var(--bg)] rounded-xl border border-[var(--line)] mb-6">
            <h4 class="text-sm font-bold text-[var(--ink)] mb-4">Vigência e Valores</h4>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div>
                    <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">Valor Total (R$) <span class="text-[var(--mint)]">*</span></label>
                    <input type="text" name="valor_total" value="R$ {{ number_format($contrato->valor_total, 2, ',', '.') }}" required
                            class="w-full px-4 py-2.5 bg-[var(--card)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors money-mask">
                    @error('valor_total')
                    <p class="mt-1 text-xs text-[var(--bad-strong)]">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">Data Início</label>
                    <input type="date" name="data_inicio" value="{{ $contrato->data_inicio ? $contrato->data_inicio->format('Y-m-d') : '' }}"
                            class="w-full px-4 py-2.5 bg-[var(--card)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                    @error('data_inicio')
                    <p class="mt-1 text-xs text-[var(--bad-strong)]">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">Data Finalização <span class="text-[var(--mint)]">*</span></label>
                    <input type="date" name="data_finalizacao" required value="{{ $contrato->data_finalizacao ? $contrato->data_finalizacao->format('Y-m-d') : '' }}"
                            class="w-full px-4 py-2.5 bg-[var(--card)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] border-l-4 border-l-[#2DC197] transition-colors">
                    @error('data_finalizacao')
                    <p class="mt-1 text-xs text-[var(--bad-strong)]">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Objeto e Arquivo --}}
        <div class="mb-6">
            <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">Objeto do Contrato <span class="text-[var(--mint)]">*</span></label>
            <textarea name="objeto" rows="3" required class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder:text-[var(--ink-2)]/40 transition-colors">{{ $contrato->objeto }}</textarea>
            @error('objeto')
            <p class="mt-1 text-xs text-[var(--bad-strong)]">{{ $message }}</p>
            @enderror
        </div>

        {{-- Upload do Arquivo PDF do Contrato --}}
        <div class="mb-6 p-5 bg-[var(--bg)] rounded-xl border border-[var(--line)]">
            <h4 class="text-sm font-bold text-[var(--ink)] mb-3 flex items-center gap-2">
                <i class="fas fa-file-pdf text-[var(--mint)]"></i> Arquivo do Contrato
            </h4>

            @if($contrato->arquivo_contrato)
                <div class="mb-4">
                    <p class="text-xs font-semibold text-[var(--ink-2)] mb-2">Arquivo atual:</p>
                    <a href="{{ url($contrato->arquivo_contrato) }}"
                       target="_blank"
                       class="block group">
                        <div class="flex items-center gap-3 p-3 bg-[var(--card)] rounded-xl border border-[var(--line)] hover:bg-[var(--hover-bg)] hover:border-[var(--mint)]/40 transition-colors cursor-pointer">
                            <div class="flex-shrink-0">
                                <div class="p-2 bg-[var(--bad-bg)] border border-[var(--bad-border)] rounded-lg">
                                    <i class="fas fa-file-pdf text-[var(--bad-strong)] text-xl"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-[var(--ink)] truncate group-hover:text-[var(--mint)]">
                                    {{ basename($contrato->arquivo_contrato) }}
                                </p>
                                <p class="text-xs text-[var(--ink-2)] mt-1">
                                    <i class="fas fa-external-link-alt mr-1 text-[var(--mint)]"></i> Clique para abrir o PDF
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-external-link-alt text-[var(--ink-2)] group-hover:text-[var(--mint)]"></i>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="flex items-center gap-3 mb-4">
                    <div class="flex-1 h-px bg-[var(--line)]"></div>
                    <span class="text-xs text-[var(--ink-2)]">ou</span>
                    <div class="flex-1 h-px bg-[var(--line)]"></div>
                </div>
            @endif

            <div>
                <label class="block mb-2 text-sm font-semibold text-[var(--ink-2)]">
                    {{ $contrato->arquivo_contrato ? 'Substituir arquivo' : 'Enviar arquivo' }} (PDF)
                </label>
                <input type="file" name="arquivo_contrato" id="arquivo_contrato"
                       class="block w-full px-3 py-2 text-sm text-[var(--ink-2)] bg-[var(--card)] border border-[var(--line)] rounded-xl file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-[var(--mint)] file:text-[var(--on-mint)] hover:file:bg-[var(--mint-h)] transition-colors cursor-pointer focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)]"
                       accept=".pdf">
                <p class="mt-2 text-xs text-[var(--ink-2)]">
                    Apenas arquivos PDF, tamanho máximo: 5MB
                </p>
                @if($contrato->arquivo_contrato)
                    <p class="mt-1 text-xs text-[var(--ink-2)]/80">
                        Deixe em branco para manter o arquivo atual.
                    </p>
                @endif
                @error('arquivo_contrato')
                <p class="mt-1 text-xs text-[var(--bad-strong)]">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-[var(--line)]">
            <a href="{{ route('admin.contratos.index') }}"
                class="px-4 py-2.5 text-sm font-semibold text-[var(--ink)] bg-[var(--card)] border border-[var(--line)] rounded-xl hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] transition-colors shadow-sm">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-sm hover:scale-105">
                <i class="fas fa-save"></i>
                Salvar Alterações
            </button>
        </div>
    </form>
</div>

{{-- ================= SEÇÃO DA EMPRESA (AJAX) ================= --}}
<div class="card !rounded-2xl overflow-hidden mb-10">
    <div class="px-6 py-4 border-b border-[var(--line)]">
        <h3 class="flex items-center gap-2 text-lg font-bold text-[var(--ink)]">
            <i class="fas fa-building text-[var(--mint)]"></i> Informações da Empresa
        </h3>
    </div>

    <form id="formEmpresa" action="{{ route('admin.contratos.empresa.update', $contrato->id) }}" method="POST" class="px-6 py-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-[var(--ink-2)] mb-2">Razão Social <span class="text-[var(--mint)]">*</span></label>
                <input type="text" name="razao_social" value="{{ $contrato->empresa->razao_social }}" required
                        class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder:text-[var(--ink-2)]/40 transition-colors">
            </div>
            <div>
                <label class="block text-sm font-semibold text-[var(--ink-2)] mb-2">CNPJ <span class="text-[var(--mint)]">*</span></label>
                <input type="text" name="cnpj" id="cnpj" value="{{ $contrato->empresa->cnpj_formatado }}" required
                        class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder:text-[var(--ink-2)]/40 transition-colors">
            </div>

            <div>
                <label class="block text-sm font-semibold text-[var(--ink-2)] mb-2">Representante</label>
                <input type="text" name="representante" value="{{ $contrato->empresa->representante }}"
                        class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder:text-[var(--ink-2)]/40 transition-colors">
            </div>

            <div>
                <label class="block text-sm font-semibold text-[var(--ink-2)] mb-2">Endereço <span class="text-[var(--mint)]">*</span></label>
                <input type="text" name="endereco" value="{{ $contrato->empresa->endereco }}" required
                        class="w-full px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder:text-[var(--ink-2)]/40 transition-colors">
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-[var(--line)] flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-sm hover:scale-105">
                <i class="fas fa-save"></i>
                Atualizar Apenas Empresa
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const prefeituraSelect = document.getElementById('prefeitura_id');
        const unidadeSelect = document.getElementById('unidade_id');

        // Dados das unidades vindo do PHP
        const unidadesPorPrefeitura = {!! $secretarias->groupBy('prefeitura_id')->map(function($unidades) {
            return $unidades->map(function($unidade) {
                return [
                    'id' => $unidade->id,
                    'nome' => $unidade->nome,
                ];
            });
        })->toJson() !!};

        // Função para carregar unidades baseadas na prefeitura selecionada
        function carregarUnidades() {
            let prefeituraId;

            // Se for usuário da prefeitura, usar o ID da prefeitura do usuário
            @if($isPrefeituraUser)
                prefeituraId = "{{ auth()->user()->prefeitura_id }}";
            @else
                prefeituraId = prefeituraSelect ? prefeituraSelect.value : null;
            @endif

            // Limpar o select de unidades
            unidadeSelect.innerHTML = '<option value="">@if($isPrefeituraUser) Selecione a secretaria @else Primeiro selecione a prefeitura @endif</option>';

            if (prefeituraId && unidadesPorPrefeitura[prefeituraId]) {
                // Adicionar opções das unidades da prefeitura selecionada
                unidadesPorPrefeitura[prefeituraId].forEach(unidade => {
                    const option = document.createElement('option');
                    option.value = unidade.id;
                    option.textContent = unidade.nome;
                    unidadeSelect.appendChild(option);
                });

                // Selecionar a unidade atual do contrato
                const currentUnidadeId = "{{ $contrato->unidade_id }}";
                if (currentUnidadeId) {
                    unidadeSelect.value = currentUnidadeId;
                }
            }
        }

        // Se for usuário da prefeitura, carregar unidades automaticamente
        @if($isPrefeituraUser)
            carregarUnidades();
        @else
            // Adicionar evento de change na prefeitura apenas para não-usuários de prefeitura
            if (prefeituraSelect) {
                prefeituraSelect.addEventListener('change', carregarUnidades);
            }

            // Executar carregamento inicial
            carregarUnidades();
        @endif

        // Máscara de Moeda
        const moneyInput = document.querySelector('.money-mask');
        if (moneyInput) {
            moneyInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = (value / 100).toFixed(2) + '';
                value = value.replace(".", ",");
                value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
                e.target.value = 'R$ ' + value;
            });
        }

        // Máscara CNPJ
        const cnpjInput = document.getElementById('cnpj');
        if (cnpjInput) {
            cnpjInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length <= 14) {
                    value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                    value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                    value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                    value = value.replace(/(\d{4})(\d)/, '$1-$2');
                }
                e.target.value = value;
            });
        }

        // Validação do arquivo
        const fileInput = document.getElementById('arquivo_contrato');
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                if (e.target.files[0]) {
                    // Verificar se é PDF
                    if (!e.target.files[0].type.includes('pdf')) {
                        alert('Por favor, selecione apenas arquivos PDF.');
                        e.target.value = '';
                        return;
                    }

                    // Verificar tamanho (5MB máximo)
                    if (e.target.files[0].size > 5 * 1024 * 1024) {
                        alert('O arquivo é muito grande. Tamanho máximo permitido: 5MB.');
                        e.target.value = '';
                        return;
                    }
                }
            });
        }

        // Ajax da Empresa
        document.getElementById('formEmpresa').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Empresa atualizada com sucesso!');
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(err => alert('Erro na requisição'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
    });
</script>
@endsection
