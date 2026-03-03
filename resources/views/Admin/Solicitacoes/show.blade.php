@extends('layouts.app')
@section('page-title', 'Solicitação: ' . $solicitacao->assunto)
@section('page-subtitle', 'Histórico de troca de mensagens oficiais')

@section('content')
<div class="py-8 max-w-5xl mx-auto">
    <!-- Header de Contexto -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('admin.solicitacoes.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-2 mb-2">
                <i class="fas fa-arrow-left"></i> Voltar para lista
            </a>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-extrabold text-gray-900">{{ $solicitacao->assunto }}</h2>
                <span class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-full
                    @if($solicitacao->status === 'aberta') bg-blue-100 text-blue-700
                    @elseif($solicitacao->status === 'recebida') bg-green-100 text-green-700
                    @elseif($solicitacao->status === 'aguardando_resposta') bg-yellow-100 text-yellow-700
                    @elseif($solicitacao->status === 'finalizada') bg-gray-100 text-gray-700
                    @endif shadow-sm border border-black/5 uppercase">
                    {{ str_replace('_', ' ', $solicitacao->status) }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">
                Prefeitura: <span class="font-bold text-gray-800">{{ $solicitacao->prefeitura->nome }}</span>
            </p>
        </div>
        
        @if(!$solicitacao->estaFinalizada())
        <form action="{{ route('admin.solicitacoes.finalizar', $solicitacao->id) }}" method="POST">
            @csrf @method('PATCH')
            <button type="submit" class="bg-gray-800 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-black transition-all flex items-center gap-2">
                <i class="fas fa-check-circle"></i> Finalizar Solicitação
            </button>
        </form>
        @endif
    </div>

    <!-- Timeline estilo Chat -->
    <div class="bg-white border border-gray-100 shadow-2xl rounded-3xl overflow-hidden flex flex-col min-h-[600px]">
        
        <!-- Área de Mensagens -->
        <div class="flex-1 p-6 md:p-10 space-y-8 overflow-y-auto bg-gray-50/50">
            @foreach($solicitacao->mensagens as $msg)
                <div class="flex {{ $msg->user_id == Auth::id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%] md:max-w-[70%]">
                        <div class="flex items-center gap-2 mb-1.5 {{ $msg->user_id == Auth::id() ? 'justify-end' : 'justify-start' }}">
                            <span class="text-xs font-bold text-gray-500">{{ $msg->usuario->name }}</span>
                            <span class="text-[10px] text-gray-400 font-medium">{{ $msg->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        
                        <div class="relative p-5 rounded-2xl shadow-sm border {{ $msg->user_id == Auth::id() 
                            ? 'bg-indigo-600 text-white border-indigo-700 rounded-tr-none' 
                            : 'bg-white text-gray-800 border-gray-200 rounded-tl-none' }}">
                            
                            <p class="text-sm leading-relaxed whitespace-pre-line">{{ $msg->mensagem }}</p>

                            @if($msg->anexo_path)
                            <div class="mt-4 pt-4 border-t {{ $msg->user_id == Auth::id() ? 'border-indigo-500' : 'border-gray-100' }}">
                                <a href="{{ asset($msg->anexo_path) }}" target="_blank" class="inline-flex items-center gap-2 text-xs font-bold {{ $msg->user_id == Auth::id() ? 'text-indigo-100' : 'text-indigo-600' }} border border-current px-3 py-1.5 rounded-lg hover:bg-black/5 transition-all">
                                    <i class="fas fa-paperclip"></i> Ver Anexo
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Área de Resposta -->
        @if(!$solicitacao->estaFinalizada())
        <div class="p-6 bg-white border-t border-gray-100">
            <form action="{{ route('admin.solicitacoes.responder', $solicitacao->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <textarea name="mensagem" rows="3" placeholder="Digite sua resposta oficial aqui..." class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-5 py-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all resize-none" required></textarea>
                </div>
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-2">
                        <label class="cursor-pointer bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                            <i class="fas fa-paperclip"></i> Anexar Arquivo
                            <input type="file" name="anexo" class="hidden" onchange="document.getElementById('file-name').textContent = this.files[0].name" />
                        </label>
                        <span id="file-name" class="text-xs text-gray-500 italic max-w-[150px] truncate"></span>
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white font-bold py-3 px-10 rounded-2xl hover:bg-indigo-700 transition-all shadow-lg hover:shadow-indigo-200 flex items-center gap-2">
                        <i class="fas fa-reply"></i> Enviar Resposta
                    </button>
                </div>
            </form>
        </div>
        @else
        <div class="p-10 bg-gray-50 text-center border-t border-gray-100">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-200 text-gray-500 rounded-full mb-4">
                <i class="fas fa-lock text-xl"></i>
            </div>
            <h4 class="text-gray-900 font-bold">Solicitação Encerrada</h4>
            <p class="text-sm text-gray-500 mt-1">Este histórico está bloqueado para novas mensagens.</p>
        </div>
        @endif
    </div>
</div>
@endsection
