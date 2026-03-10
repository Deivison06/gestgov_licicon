@extends('layouts.app')
@section('page-title', 'Nova Solicitação')
@section('page-subtitle', 'Preencha os dados abaixo para iniciar uma nova conversa formal')

@section('content')
<div class="py-8 max-w-4xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('admin.solicitacoes.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Voltar para lista
        </a>
    </div>

    <div class="bg-white border border-gray-100 shadow-xl rounded-2xl overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-indigo-50/50 to-white">
            <h3 class="text-xl font-bold text-gray-800">Criar Solicitação</h3>
            <p class="text-sm text-gray-500 mt-1">Envie uma mensagem formal para o setor responsável.</p>
        </div>

        <form action="{{ route('admin.solicitacoes.store') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(auth()->user()->hasRole('prefeitura'))
                    <input type="hidden" name="prefeitura_id" value="{{ auth()->user()->prefeitura_id }}">
                @else
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Prefeitura Destino</label>
                        <select name="prefeitura_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all cursor-pointer" required>
                            <option value="">Selecione a Prefeitura</option>
                            @foreach($prefeituras as $prefeitura)
                                <option value="{{ $prefeitura->id }}">{{ $prefeitura->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Tipo da Solicitação</label>
                    <select name="tipo" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all cursor-pointer" required>
                        <option value="correcao">Correção de Dados</option>
                        <option value="reclamacao">Reclamação / Questionamento</option>
                        <option value="outros">Outros Assuntos</option>
                    </select>
                </div>
                
                <div class="{{ auth()->user()->hasRole('prefeitura') ? '' : 'md:col-span-2' }}">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Assunto Breve</label>
                    <input type="text" name="assunto" placeholder="Ex: Dúvida sobre novos procedimentos" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Mensagem Detalhada</label>
                <textarea name="mensagem" rows="5" placeholder="Descreva aqui sua solicitação de forma clara e profissional..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" required></textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Anexar Documento (Opcional)</label>
                <div class="flex items-center justify-center w-full">
                    <label for="anexo_create" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-200 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition-all">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-500" id="anexo_create_label">Clique para selecionar ou arraste um arquivo</p>
                            <p class="text-xs text-gray-400">PDF, DOC, JPG ou PNG (Max 10MB)</p>
                        </div>
                        <input type="file" name="anexo" id="anexo_create" class="sr-only"
                            onchange="document.getElementById('anexo_create_label').textContent = this.files[0] ? this.files[0].name : 'Clique para selecionar ou arraste um arquivo'" />
                    </label>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white font-bold py-3 px-8 rounded-xl hover:bg-indigo-700 transition-all shadow-lg hover:shadow-indigo-200 flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Enviar Solicitação
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
