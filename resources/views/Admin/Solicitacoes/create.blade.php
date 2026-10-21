@extends('layouts.app')
@section('page-title', 'Nova Solicitação')
@section('page-subtitle', 'Preencha os dados abaixo para iniciar uma nova conversa formal')

@section('content')
<div class="py-8">
    <div class="mb-8">
        <a href="{{ route('admin.solicitacoes.index') }}" class="text-sm text-[var(--mint)] hover:text-[var(--mint-h)] flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Voltar para lista
        </a>
    </div>

    <div class="bg-[var(--card)] border border-[var(--line)] shadow-xl rounded-2xl overflow-hidden">
        <div class="px-8 py-6 border-b border-[var(--line)] bg-[var(--card-2)]">
            <h3 class="text-xl font-bold text-[var(--ink)]">Criar Solicitação</h3>
            <p class="text-sm text-[var(--ink-4)] mt-1">Envie uma mensagem formal para o setor responsável.</p>
        </div>

        <form action="{{ route('admin.solicitacoes.store') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(auth()->user()->hasRole('prefeitura'))
                    <input type="hidden" name="prefeitura_id" value="{{ auth()->user()->prefeitura_id }}">
                @else
                    <div>
                        <label class="block text-sm font-bold text-[var(--ink-2)] mb-2">Prefeitura Destino</label>
                        <select name="prefeitura_id" class="w-full bg-[var(--card-2)] border border-[var(--line)] text-[var(--ink)] rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--mint)] transition-all cursor-pointer" required>
                            <option value="">Selecione a Prefeitura</option>
                            @foreach($prefeituras as $prefeitura)
                                <option value="{{ $prefeitura->id }}">{{ $prefeitura->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-bold text-[var(--ink-2)] mb-2">Tipo da Solicitação</label>
                    <select name="tipo" class="w-full bg-[var(--card-2)] border border-[var(--line)] text-[var(--ink)] rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--mint)] transition-all cursor-pointer" required>
                        <option value="correcao">Correção de Dados</option>
                        <option value="reclamacao">Reclamação / Questionamento</option>
                        <option value="outros">Outros Assuntos</option>
                    </select>
                </div>
                
                <div class="{{ auth()->user()->hasRole('prefeitura') ? '' : 'md:col-span-2' }}">
                    <label class="block text-sm font-bold text-[var(--ink-2)] mb-2">Assunto Breve</label>
                    <input type="text" name="assunto" placeholder="Ex: Dúvida sobre novos procedimentos" class="w-full bg-[var(--card-2)] border border-[var(--line)] text-[var(--ink)] rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--mint)] transition-all" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-[var(--ink-2)] mb-2">Mensagem Detalhada</label>
                <textarea name="mensagem" rows="5" placeholder="Descreva aqui sua solicitação de forma clara e profissional..." class="w-full bg-[var(--card-2)] border border-[var(--line)] text-[var(--ink)] rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--mint)] transition-all" required></textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-[var(--ink-2)] mb-2">Anexar Documento (Opcional)</label>
                <div class="flex items-center justify-center w-full">
                    <label for="anexo_create" class="flex flex-col items-center justify-center w-full h-32 border-2 border-[var(--line)] border-dashed rounded-xl cursor-pointer bg-[var(--card-2)] hover:bg-[var(--hover-bg)] transition-all">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <i class="fas fa-cloud-upload-alt text-3xl text-[var(--ink-4)] mb-2"></i>
                            <p class="text-sm text-[var(--ink-4)]" id="anexo_create_label">Clique para selecionar ou arraste um arquivo</p>
                            <p class="text-xs text-[var(--ink-4)]">PDF, DOC, JPG ou PNG (Max 10MB)</p>
                        </div>
                        <input type="file" name="anexo" id="anexo_create" class="sr-only"
                            onchange="document.getElementById('anexo_create_label').textContent = this.files[0] ? this.files[0].name : 'Clique para selecionar ou arraste um arquivo'" />
                    </label>
                </div>
            </div>

            <div class="pt-6 border-t border-[var(--line)] flex justify-end">
                <button type="submit" class="bg-[var(--mint)] text-[var(--on-mint)] font-bold py-3 px-8 rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-lg flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Enviar Solicitação
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
