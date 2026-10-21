@extends('layouts.app')
@section('page-title', 'Solicitação: ' . $solicitacao->assunto)
@section('page-subtitle', 'Histórico de troca de mensagens oficiais')

@section('content')
<div class="py-6">
    <!-- Header de Contexto -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('admin.solicitacoes.index') }}" class="text-sm font-semibold text-[var(--ink-2)] hover:text-[var(--mint)] flex items-center gap-2 mb-2 transition-colors">
                <i class="fas fa-arrow-left"></i> Voltar para lista
            </a>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-[var(--ink)]">{{ $solicitacao->assunto }}</h2>
                <span class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-full
                    @if($solicitacao->status === 'aberta') bg-[var(--card-3)] text-[var(--mint)] border border-[var(--line)]
                    @elseif($solicitacao->status === 'recebida') bg-[var(--card-3)] text-[var(--mint)] border border-[var(--line)]
                    @elseif($solicitacao->status === 'aguardando_resposta') bg-[var(--warn-bg)] text-[var(--warn-2)] border border-[var(--warn-border)]
                    @elseif($solicitacao->status === 'finalizada') bg-[var(--card)] text-[var(--ink-2)] border border-[var(--line)]
                    @endif shadow-sm uppercase">
                    {{ str_replace('_', ' ', $solicitacao->status) }}
                </span>
            </div>
            <p class="text-sm text-[var(--ink-2)] mt-1">
                Prefeitura: <span class="font-bold text-[var(--ink)]">{{ $solicitacao->prefeitura->nome }}</span>
            </p>
        </div>
        
        @if(!$solicitacao->estaFinalizada())
        <form action="{{ route('admin.solicitacoes.finalizar', $solicitacao->id) }}" method="POST">
            @csrf @method('PATCH')
            <button type="submit" class="bg-[var(--bad-bg)] border border-[var(--bad-border)] text-[var(--bad-strong)] hover:bg-[var(--bad-bg)] px-5 py-2.5 rounded-xl font-bold text-sm transition-all flex items-center gap-2">
                <i class="fas fa-check-circle"></i> Finalizar Solicitação
            </button>
        </form>
        @endif
    </div>

    <!-- Timeline estilo Chat -->
    <div class="card !rounded-2xl overflow-hidden flex flex-col min-h-[600px] border border-[var(--line)]">
        
        <!-- Área de Mensagens -->
        <div class="flex-1 p-6 md:p-8 space-y-6 overflow-y-auto bg-[var(--bg)]">
            @foreach($solicitacao->mensagens as $msg)
                <div class="flex {{ $msg->user_id == Auth::id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%] md:max-w-[70%]">
                        <div class="flex items-center gap-2 mb-1.5 {{ $msg->user_id == Auth::id() ? 'justify-end' : 'justify-start' }}">
                            <span class="text-xs font-semibold text-[var(--ink-2)]">{{ $msg->usuario->name }}</span>
                            <span class="text-[10px] text-[var(--ink-2)]/60 font-medium">{{ $msg->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        
                        <div class="relative p-5 rounded-2xl shadow-sm border {{ $msg->user_id == Auth::id() 
                            ? 'bg-[var(--card-3)] text-[var(--ink)] border-[var(--line)] rounded-tr-none' 
                            : 'bg-[var(--card)] text-[var(--ink)] border-[var(--line)] rounded-tl-none' }}">
                            
                            <p class="text-sm leading-relaxed whitespace-pre-line">{{ $msg->mensagem }}</p>

                            @if($msg->anexo_path)
                            <div class="mt-4 pt-4 border-t border-[var(--line)]">
                                <a href="{{ asset($msg->anexo_path) }}" target="_blank" class="inline-flex items-center gap-2 text-xs font-bold text-[var(--mint)] bg-[var(--bg)] border border-[var(--line)] px-3 py-1.5 rounded-lg hover:bg-[var(--hover-bg)] transition-all">
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
        <div class="p-6 bg-[var(--card)] border-t border-[var(--line)]">
            <form action="{{ route('admin.solicitacoes.responder', $solicitacao->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <textarea name="mensagem" rows="3" placeholder="Digite sua resposta oficial aqui..." class="w-full bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl px-5 py-4 focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] placeholder:text-[var(--ink-2)]/40 transition-all resize-none" required></textarea>
                </div>
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-2">
                        <label for="anexo_resposta" class="cursor-pointer bg-[var(--bg)] hover:bg-[var(--hover-bg)] border border-[var(--line)] text-[var(--ink-2)] hover:text-[var(--mint)] px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                            <i class="fas fa-paperclip"></i> Anexar Arquivo
                            <input type="file" name="anexo" id="anexo_resposta" class="sr-only"
                                onchange="document.getElementById('file-name').textContent = this.files[0] ? this.files[0].name : ''" />
                        </label>
                        <span id="file-name" class="text-xs text-[var(--ink-2)] italic max-w-[200px] truncate"></span>
                    </div>
                    <button type="submit" class="bg-[var(--mint)] text-[var(--on-mint)] font-bold py-2.5 px-8 rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-sm hover:scale-105 flex items-center gap-2">
                        <i class="fas fa-reply"></i> Enviar Resposta
                    </button>
                </div>
            </form>
        </div>
        @else
        <div class="p-10 bg-[var(--bg)] text-center border-t border-[var(--line)]">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-[var(--card)] border border-[var(--line)] text-[var(--ink-2)] rounded-full mb-4">
                <i class="fas fa-lock text-xl"></i>
            </div>
            <h4 class="text-[var(--ink)] font-bold">Solicitação Encerrada</h4>
            <p class="text-sm text-[var(--ink-2)] mt-1">Este histórico está bloqueado para novas mensagens.</p>
        </div>
        @endif
    </div>
</div>
@endsection
