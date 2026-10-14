@extends('layouts.app')
@section('page-title', 'Nova Solicitação')
@section('page-subtitle', 'Preencha os dados abaixo para iniciar uma nova conversa formal')

@section('content')
<div class="py-8">
    <div class="mb-8">
        <a href="{{ route('admin.solicitacoes.index') }}" class="text-sm text-[#2DC197] hover:text-[#26aa84] flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Voltar para lista
        </a>
    </div>

    <div class="bg-[#10201e] border border-[#24403c] shadow-xl rounded-2xl overflow-hidden">
        <div class="px-8 py-6 border-b border-[#24403c] bg-[#0d1b19]">
            <h3 class="text-xl font-bold text-[#e6f0ee]">Criar Solicitação</h3>
            <p class="text-sm text-[#7ea29c] mt-1">Envie uma mensagem formal para o setor responsável.</p>
        </div>

        <form action="{{ route('admin.solicitacoes.store') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(auth()->user()->hasRole('prefeitura'))
                    <input type="hidden" name="prefeitura_id" value="{{ auth()->user()->prefeitura_id }}">
                @else
                    <div>
                        <label class="block text-sm font-bold text-[#a2b9b5] mb-2">Prefeitura Destino</label>
                        <select name="prefeitura_id" class="w-full bg-[#0d1b19] border border-[#24403c] text-[#e6f0ee] rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#2DC197] transition-all cursor-pointer" required>
                            <option value="">Selecione a Prefeitura</option>
                            @foreach($prefeituras as $prefeitura)
                                <option value="{{ $prefeitura->id }}">{{ $prefeitura->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-bold text-[#a2b9b5] mb-2">Tipo da Solicitação</label>
                    <select name="tipo" class="w-full bg-[#0d1b19] border border-[#24403c] text-[#e6f0ee] rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#2DC197] transition-all cursor-pointer" required>
                        <option value="correcao">Correção de Dados</option>
                        <option value="reclamacao">Reclamação / Questionamento</option>
                        <option value="outros">Outros Assuntos</option>
                    </select>
                </div>
                
                <div class="{{ auth()->user()->hasRole('prefeitura') ? '' : 'md:col-span-2' }}">
                    <label class="block text-sm font-bold text-[#a2b9b5] mb-2">Assunto Breve</label>
                    <input type="text" name="assunto" placeholder="Ex: Dúvida sobre novos procedimentos" class="w-full bg-[#0d1b19] border border-[#24403c] text-[#e6f0ee] rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#2DC197] transition-all" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-[#a2b9b5] mb-2">Mensagem Detalhada</label>
                <textarea name="mensagem" rows="5" placeholder="Descreva aqui sua solicitação de forma clara e profissional..." class="w-full bg-[#0d1b19] border border-[#24403c] text-[#e6f0ee] rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#2DC197] transition-all" required></textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-[#a2b9b5] mb-2">Anexar Documento (Opcional)</label>
                <div class="flex items-center justify-center w-full">
                    <label for="anexo_create" class="flex flex-col items-center justify-center w-full h-32 border-2 border-[#24403c] border-dashed rounded-xl cursor-pointer bg-[#0d1b19] hover:bg-[#142825] transition-all">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <i class="fas fa-cloud-upload-alt text-3xl text-[#7ea29c] mb-2"></i>
                            <p class="text-sm text-[#7ea29c]" id="anexo_create_label">Clique para selecionar ou arraste um arquivo</p>
                            <p class="text-xs text-[#527771]">PDF, DOC, JPG ou PNG (Max 10MB)</p>
                        </div>
                        <input type="file" name="anexo" id="anexo_create" class="sr-only"
                            onchange="document.getElementById('anexo_create_label').textContent = this.files[0] ? this.files[0].name : 'Clique para selecionar ou arraste um arquivo'" />
                    </label>
                </div>
            </div>

            <div class="pt-6 border-t border-[#24403c] flex justify-end">
                <button type="submit" class="bg-[#2DC197] text-[#041d1a] font-bold py-3 px-8 rounded-xl hover:bg-[#26aa84] transition-all shadow-lg flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Enviar Solicitação
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
