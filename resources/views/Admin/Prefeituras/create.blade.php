@extends('layouts.app')
@section('page-title', 'Gestão de Prefeituras')
@section('page-subtitle', 'Preencha os dados da prefeitura')

@section('content')
<div class="py-6">
    <div class="card !rounded-2xl overflow-hidden">
        <div class="card-h">
            <h3 class="text-lg font-bold text-[var(--ink)] flex items-center gap-2">
                <i class="fas fa-building text-[var(--mint)]"></i> Informações da Prefeitura
            </h3>
        </div>
        <div class="p-8">
            <form action="{{ route('admin.prefeituras.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="nome" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Nome <span class="text-red-400">*</span></label>
                        <input type="text" name="nome" id="nome" value="{{ old('nome') }}" required
                            class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                        @error('nome')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="cnpj" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">CNPJ <span class="text-red-400">*</span></label>
                        <input type="text" name="cnpj" id="cnpj" value="{{ old('cnpj') }}" required
                            placeholder="00.000.000/0000-00"
                            class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder:text-[var(--ink-2)]/40 transition-colors">
                        @error('cnpj')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="endereco" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Endereço</label>
                        <input type="text" name="endereco" id="endereco" value="{{ old('endereco') }}"
                            class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                        @error('endereco')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="cidade" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Nome da Cidade <span class="text-red-400">*</span></label>
                        <input type="text" name="cidade" id="cidade" value="{{ old('cidade') }}" required
                            class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                        @error('cidade')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="telefone" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Telefone</label>
                        <input type="text" name="telefone" id="telefone" value="{{ old('telefone') }}"
                            placeholder="(00) 00000-0000"
                            class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder:text-[var(--ink-2)]/40 transition-colors">
                        @error('telefone')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                        @error('email')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="autoridade_competente" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Autoridade Competente</label>
                        <input type="text" name="autoridade_competente" id="autoridade_competente" value="{{ old('autoridade_competente') }}"
                            class="block w-full px-4 py-2.5 text-sm bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-colors">
                        @error('autoridade_competente')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="capa" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Capa</label>
                        <input type="file" name="capa" id="capa"
                            class="block w-full text-sm text-[var(--ink-2)]
                                   file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0
                                   file:text-sm file:font-bold
                                   file:bg-[var(--mint)] file:text-[var(--on-mint)] hover:file:bg-[var(--mint-h)]
                                   file:cursor-pointer transition-colors">
                        @error('capa')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="timbre" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Timbre</label>
                        <input type="file" name="timbre" id="timbre"
                            class="block w-full text-sm text-[var(--ink-2)]
                                   file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0
                                   file:text-sm file:font-bold
                                   file:bg-[var(--mint)] file:text-[var(--on-mint)] hover:file:bg-[var(--mint-h)]
                                   file:cursor-pointer transition-colors">
                        @error('timbre')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="capa_edital" class="block mb-1.5 text-sm font-semibold text-[var(--ink-2)]">Capa Edital</label>
                        <input type="file" name="capa_edital" id="capa_edital"
                            class="block w-full text-sm text-[var(--ink-2)]
                                   file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0
                                   file:text-sm file:font-bold
                                   file:bg-[var(--mint)] file:text-[var(--on-mint)] hover:file:bg-[var(--mint-h)]
                                   file:cursor-pointer transition-colors">
                        @error('capa_edital')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 mt-8 border-t border-[var(--line)]">
                    <a href="{{ route('admin.prefeituras.index') }}"
                        class="px-5 py-2.5 text-sm font-semibold text-[var(--ink)] bg-[var(--card)] border border-[var(--line)] rounded-xl hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] transition-colors shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-sm hover:scale-105">
                        <i class="fas fa-save"></i>
                        Salvar Prefeitura
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Máscara de CNPJ
        const cnpjInput = document.getElementById('cnpj');
        if (cnpjInput) {
            IMask(cnpjInput, {
                mask: '00.000.000/0000-00'
            });
        }

        // Máscara de Telefone (suporta fixo e celular)
        const telefoneInput = document.getElementById('telefone');
        if (telefoneInput) {
            IMask(telefoneInput, {
                mask: [
                    { mask: '(00) 0000-0000' },
                    { mask: '(00) 00000-0000' }
                ]
            });
        }
    });
</script>
@endpush

@endsection
