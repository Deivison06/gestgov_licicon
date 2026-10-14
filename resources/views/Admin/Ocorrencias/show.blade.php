@extends('layouts.app')
@section('page-title', 'Detalhes da Ocorrência')
@section('page-subtitle', 'Registro de Ocorrência Nº ' . ($ocorrencia->numero_ocorrencia ?? $ocorrencia->id))

@section('content')

@php
    $info = $ocorrencia->contrato_info;
    $bloqueado = $ocorrencia->status->value === 'concluida';
@endphp

<div class="py-8">
    {{-- BARRA DE AÇÕES --}}
    <div class="flex items-center justify-between mb-8 flex-wrap gap-3">
        <div>
            <h2 class="text-2xl font-bold text-[#e6f0ee]">Ocorrência {{ $ocorrencia->numero_ocorrencia }}</h2>
            <span class="inline-flex items-center px-2.5 py-0.5 mt-1 rounded-full text-xs font-semibold {{ $ocorrencia->status_badge_class }}">
                {{ $ocorrencia->status_texto }}
            </span>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.ocorrencias.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-[#e6f0ee] transition-colors bg-[#10201e] border border-[#24403c] rounded-xl hover:bg-[#142825] hover:text-[#2DC197] shadow-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>

            @unless($bloqueado)
                <a href="{{ route('admin.ocorrencias.edit', $ocorrencia->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-[#e6f0ee] transition-colors bg-[#142825] border border-[#24403c] rounded-xl hover:bg-[#1a3531] shadow-sm">
                    <i class="fas fa-edit text-amber-400"></i> Editar Dados
                </a>
            @endunless

            <div class="hidden md:block w-px h-8 bg-[#24403c] mx-1"></div>

            <a href="{{ route('admin.ocorrencias.pdf.registro', $ocorrencia->id) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-[#2DC197] transition-colors bg-[#10201e] border border-[#24403c] rounded-xl hover:bg-[#142825] shadow-sm">
                <i class="fas fa-file-contract"></i> Registro de Ocorrência
            </a>

            <a href="{{ route('admin.ocorrencias.pdf.notificacoes', $ocorrencia->id) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-[#ff7b72] transition-colors bg-[#2a1315] border border-[#5a2328] rounded-xl hover:bg-[#3d1a1e] shadow-sm">
                <i class="fas fa-exclamation-triangle"></i> Notificações
            </a>

            @if($ocorrencia->correcao_descricao)
                <a href="{{ route('admin.ocorrencias.pdf.atesto', $ocorrencia->id) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-[#2DC197] transition-colors bg-[#142825] border border-[#24403c] rounded-xl hover:bg-[#1a3531] shadow-sm">
                    <i class="fas fa-stamp"></i> Atesto de Correção
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- COLUNA ESQUERDA --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="card !rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-[#24403c] bg-[#0d1b19]">
                    <h3 class="text-lg font-bold text-[#e6f0ee]">Dados do Registro</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-semibold text-[#a2b9b5] col-span-1">Situação</dt>
                            <dd class="text-sm text-[#e6f0ee] col-span-2">
                                @if($ocorrencia->situacao)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $ocorrencia->situacao_badge_class }}">
                                        {{ $ocorrencia->situacao_texto }}
                                    </span>
                                @else
                                    <span class="text-[#a2b9b5]/60">Não informada</span>
                                @endif
                            </dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-[#24403c]/60 pt-3">
                            <dt class="text-sm font-semibold text-[#a2b9b5] col-span-1">Data</dt>
                            <dd class="text-sm text-[#e6f0ee] col-span-2">{{ $ocorrencia->data_ocorrencia?->format('d/m/Y') }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-[#24403c]/60 pt-3">
                            <dt class="text-sm font-semibold text-[#a2b9b5] col-span-1">Local</dt>
                            <dd class="text-sm text-[#e6f0ee] col-span-2">{{ $ocorrencia->local ?? '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-[#24403c]/60 pt-3">
                            <dt class="text-sm font-semibold text-[#a2b9b5] col-span-1">Fiscal</dt>
                            <dd class="text-sm text-[#e6f0ee] col-span-2 font-medium">{{ $ocorrencia->user->name ?? 'Sistema' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-[#24403c]/60 pt-3">
                            <dt class="text-sm font-semibold text-[#a2b9b5] col-span-1">Criado em</dt>
                            <dd class="text-sm text-[#e6f0ee] col-span-2">{{ $ocorrencia->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="card !rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-[#24403c] bg-[#0d1b19]">
                    <h3 class="text-lg font-bold text-[#e6f0ee]">Contrato Vinculado</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs font-bold text-[#a2b9b5] uppercase tracking-wider">Empresa Contratada</dt>
                            <dd class="text-sm font-bold text-[#e6f0ee] mt-1">{{ $info['razao_social'] }}</dd>
                            <dd class="text-xs text-[#a2b9b5]">{{ $info['cnpj'] }}</dd>
                        </div>
                        <div class="pt-3 border-t border-[#24403c]/60">
                            <dt class="text-xs font-bold text-[#a2b9b5] uppercase">Nº Contrato / Processo</dt>
                            <dd class="text-sm text-[#e6f0ee] mt-1">{{ $info['numero_contrato'] }} ({{ $info['numero_processo'] }})</dd>
                        </div>
                        <div class="pt-3 border-t border-[#24403c]/60">
                            <dt class="text-xs font-bold text-[#a2b9b5] uppercase">Secretaria</dt>
                            <dd class="text-sm text-[#e6f0ee] mt-1">{{ $info['secretaria'] }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if($ocorrencia->status->value === 'registrada')
                <div class="card !rounded-2xl overflow-hidden p-6">
                    <p class="text-sm text-[#a2b9b5] mb-3">
                        Só é possível concluir depois de preencher o Atesto de Correção (descrição e data).
                    </p>
                    <form method="POST" action="{{ route('admin.ocorrencias.concluir', $ocorrencia->id) }}"
                          onsubmit="return confirm('Concluir esta ocorrência? Depois disso ela não poderá mais ser editada.');">
                        @csrf
                        <button type="submit" {{ $ocorrencia->correcao_descricao && $ocorrencia->correcao_data ? '' : 'disabled' }}
                                class="w-full flex items-center justify-center gap-2 px-5 py-3 text-sm font-bold text-[#041d1a] bg-[#2DC197] rounded-xl hover:bg-[#26aa84] disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm">
                            <i class="fas fa-check-circle"></i> Concluir Ocorrência
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- COLUNA PRINCIPAL --}}
        <div class="lg:col-span-2">
            <div class="card !rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-[#24403c] bg-[#0d1b19]">
                    <h3 class="text-lg font-bold text-[#e6f0ee]">Descrição da Ocorrência</h3>
                </div>
                <div class="p-8 space-y-8">
                    <div class="relative pl-8">
                        <div class="absolute left-0 top-1 text-[#2DC197]"><i class="fas fa-file-lines text-lg"></i></div>
                        <h4 class="text-xs font-bold text-[#a2b9b5] uppercase tracking-widest">Descrição do Fato</h4>
                        <div class="mt-2 text-sm text-[#e6f0ee] leading-relaxed bg-[#0a1514] p-4 rounded-xl border border-[#24403c]">
                            {!! nl2br(e($ocorrencia->descricao_fato)) !!}
                        </div>
                    </div>

                    @if($ocorrencia->obrigacao_descumprida)
                        <div class="relative pl-8">
                            <div class="absolute left-0 top-1 text-[#2DC197]"><i class="fas fa-gavel text-lg"></i></div>
                            <h4 class="text-xs font-bold text-[#a2b9b5] uppercase tracking-widest">Obrigação Descumprida</h4>
                            <div class="mt-2 text-sm text-[#e6f0ee] leading-relaxed bg-[#0a1514] p-4 rounded-xl border border-[#24403c]">
                                {!! nl2br(e($ocorrencia->obrigacao_descumprida)) !!}
                            </div>
                        </div>
                    @endif

                    @if($ocorrencia->prazo_resposta)
                        <div class="relative pl-8">
                            <div class="absolute left-0 top-1 text-[#2DC197]"><i class="fas fa-clock text-lg"></i></div>
                            <h4 class="text-xs font-bold text-[#a2b9b5] uppercase tracking-widest">Prazo para Resposta / Solução</h4>
                            <div class="mt-2 text-sm text-[#e6f0ee] leading-relaxed bg-[#0a1514] p-4 rounded-xl border border-[#24403c]">
                                {{ $ocorrencia->prazo_resposta }}
                            </div>
                        </div>
                    @endif

                    @if(!empty($ocorrencia->tipo_comprovacao))
                        <div class="relative pl-8">
                            <div class="absolute left-0 top-1 text-[#2DC197]"><i class="fas fa-paperclip text-lg"></i></div>
                            <h4 class="text-xs font-bold text-[#a2b9b5] uppercase tracking-widest">Meio de Comprovação</h4>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach(\App\Models\Ocorrencia::TIPOS_COMPROVACAO as $chave => $rotulo)
                                    @if(data_get($ocorrencia->tipo_comprovacao, $chave))
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-[#142825] border border-[#24403c] text-[#2DC197]">
                                            {{ $chave === 'outros' && $ocorrencia->tipo_comprovacao_outro ? $ocorrencia->tipo_comprovacao_outro : $rotulo }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ANEXOS DO FATO --}}
                    <div class="relative pl-8">
                        <div class="absolute left-0 top-1 text-[#2DC197]"><i class="fas fa-camera text-lg"></i></div>
                        <h4 class="text-xs font-bold text-[#a2b9b5] uppercase tracking-widest mb-2">Fotografias / Documentos</h4>
                        @php $anexosFato = $ocorrencia->anexos->where('categoria', 'fato'); @endphp
                        @if($anexosFato->isNotEmpty())
                            <ul class="space-y-1.5">
                                @foreach($anexosFato as $anexo)
                                    <li class="flex items-center justify-between text-sm bg-[#0a1514] p-3 rounded-xl border border-[#24403c]">
                                        <a href="{{ asset($anexo->caminho) }}" target="_blank" class="text-[#2DC197] hover:underline truncate">
                                            <i class="fas fa-paperclip mr-1.5"></i>{{ $anexo->nome_original ?? basename($anexo->caminho) }}
                                        </a>
                                        @unless($bloqueado)
                                            <form method="POST" action="{{ route('admin.ocorrencias.anexos.delete', [$ocorrencia->id, $anexo->id]) }}" onsubmit="return confirm('Remover este anexo?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-300 ml-3"><i class="fas fa-trash-alt"></i></button>
                                            </form>
                                        @endunless
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-[#a2b9b5]">Nenhum anexo.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ============================================================== --}}
            {{-- RESPOSTA DA EMPRESA --}}
            {{-- ============================================================== --}}
            @if($ocorrencia->status->value !== 'rascunho')
                <div class="mt-6 card !rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#24403c] bg-[#0d1b19] flex items-center justify-between">
                        <h3 class="text-lg font-bold text-[#e6f0ee]"><i class="fas fa-reply text-[#2DC197] mr-2"></i>Resposta da Empresa</h3>
                        <span class="text-xs font-semibold text-[#a2b9b5]">{{ $ocorrencia->anexosResposta()->count() }} anexo(s)</span>
                    </div>
                    <div class="p-6 space-y-4">
                        @unless($bloqueado)
                            <form method="POST" action="{{ route('admin.ocorrencias.anexos.upload', $ocorrencia->id) }}" enctype="multipart/form-data"
                                  class="flex flex-col gap-3 p-4 bg-[#0a1514] border border-[#24403c] rounded-xl sm:flex-row sm:items-center">
                                @csrf
                                <input type="hidden" name="categoria" value="resposta">
                                <input type="file" name="anexos[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp" required
                                       class="flex-1 text-sm text-[#a2b9b5] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-[#2DC197] file:text-[#041d1a] hover:file:bg-[#26aa84] cursor-pointer">
                                <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-[#041d1a] bg-[#2DC197] rounded-xl hover:bg-[#26aa84] transition-all shadow-sm">
                                    <i class="fas fa-upload"></i> Anexar Resposta
                                </button>
                            </form>
                        @endunless

                        @forelse($ocorrencia->anexosResposta()->get() as $anexo)
                            <div class="flex items-center justify-between text-sm bg-[#0a1514] p-3 rounded-xl border border-[#24403c]">
                                <a href="{{ asset($anexo->caminho) }}" target="_blank" class="text-[#2DC197] hover:underline truncate">
                                    <i class="fas fa-file-pdf mr-1.5"></i>{{ $anexo->nome_original ?? basename($anexo->caminho) }}
                                </a>
                                @unless($bloqueado)
                                    <form method="POST" action="{{ route('admin.ocorrencias.anexos.delete', [$ocorrencia->id, $anexo->id]) }}" onsubmit="return confirm('Remover este anexo?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 ml-3"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                @endunless
                            </div>
                        @empty
                            <p class="text-sm text-[#a2b9b5]">Nenhuma resposta anexada ainda.</p>
                        @endforelse
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- ATESTO DE CORREÇÃO --}}
                {{-- ============================================================== --}}
                <div class="mt-6 card !rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#24403c] bg-[#0d1b19]">
                        <h3 class="text-lg font-bold text-[#e6f0ee]"><i class="fas fa-stamp text-[#2DC197] mr-2"></i>Atesto de Correção</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        @if($bloqueado || $ocorrencia->correcao_descricao)
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-xs font-bold text-[#a2b9b5] uppercase tracking-wider">Solução Adotada</dt>
                                    <dd class="text-sm text-[#e6f0ee] mt-1">{!! nl2br(e($ocorrencia->correcao_descricao)) !!}</dd>
                                </div>
                                <div class="pt-3 border-t border-[#24403c]/60">
                                    <dt class="text-xs font-bold text-[#a2b9b5] uppercase">Data da Correção</dt>
                                    <dd class="text-sm text-[#e6f0ee] mt-1">{{ $ocorrencia->correcao_data?->format('d/m/Y') }}</dd>
                                </div>
                                @if($ocorrencia->correcao_elementos_comprobatorios)
                                    <div class="pt-3 border-t border-[#24403c]/60">
                                        <dt class="text-xs font-bold text-[#a2b9b5] uppercase">Elementos Comprobatórios</dt>
                                        <dd class="text-sm text-[#e6f0ee] mt-1">{!! nl2br(e($ocorrencia->correcao_elementos_comprobatorios)) !!}</dd>
                                    </div>
                                @endif
                            </dl>
                        @endif

                        @foreach($ocorrencia->anexosCorrecao()->get() as $anexo)
                            <div class="flex items-center justify-between text-sm bg-[#0a1514] p-3 rounded-xl border border-[#24403c]">
                                <a href="{{ asset($anexo->caminho) }}" target="_blank" class="text-[#2DC197] hover:underline truncate">
                                    <i class="fas fa-file-pdf mr-1.5"></i>{{ $anexo->nome_original ?? basename($anexo->caminho) }}
                                </a>
                                @unless($bloqueado)
                                    <form method="POST" action="{{ route('admin.ocorrencias.anexos.delete', [$ocorrencia->id, $anexo->id]) }}" onsubmit="return confirm('Remover este anexo?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 ml-3"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                @endunless
                            </div>
                        @endforeach

                        @unless($bloqueado)
                            <form method="POST" action="{{ route('admin.ocorrencias.atesto.salvar', $ocorrencia->id) }}" enctype="multipart/form-data" class="pt-4 border-t border-[#24403c] space-y-4">
                                @csrf
                                <div>
                                    <label class="block mb-1 text-sm font-semibold text-[#a2b9b5]">Descrição da Solução Adotada</label>
                                    <textarea name="correcao_descricao" rows="3" required
                                              class="w-full px-4 py-2.5 border border-[#24403c] rounded-xl bg-[#0a1514] text-[#e6f0ee] focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] outline-none">{{ old('correcao_descricao', $ocorrencia->correcao_descricao) }}</textarea>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block mb-1 text-sm font-semibold text-[#a2b9b5]">Data da Correção</label>
                                        <input type="date" name="correcao_data" required
                                               class="w-full px-4 py-2.5 border border-[#24403c] rounded-xl bg-[#0a1514] text-[#e6f0ee] focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] outline-none"
                                               value="{{ old('correcao_data', optional($ocorrencia->correcao_data)->format('Y-m-d')) }}">
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-sm font-semibold text-[#a2b9b5]">Anexar Comprovação (PDF/Imagem)</label>
                                        <input type="file" name="anexos_correcao[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp"
                                               class="block w-full text-sm text-[#a2b9b5] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-[#2DC197] file:text-[#041d1a] hover:file:bg-[#26aa84] cursor-pointer">
                                    </div>
                                </div>
                                <div>
                                    <label class="block mb-1 text-sm font-semibold text-[#a2b9b5]">Elementos que Comprovam a Correção</label>
                                    <textarea name="correcao_elementos_comprobatorios" rows="2"
                                              class="w-full px-4 py-2.5 border border-[#24403c] rounded-xl bg-[#0a1514] text-[#e6f0ee] focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] outline-none">{{ old('correcao_elementos_comprobatorios', $ocorrencia->correcao_elementos_comprobatorios) }}</textarea>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-[#041d1a] bg-[#2DC197] rounded-xl hover:bg-[#26aa84] transition-all shadow-sm hover:scale-105">
                                        <i class="fas fa-save"></i> Salvar Atesto de Correção
                                    </button>
                                </div>
                            </form>
                        @endunless
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- ASSINANTES DO REGISTRO (assinatura física — sem assinatura eletrônica) --}}
    {{-- ============================================================== --}}
    @php
        $unidadesAssinantes = $ocorrencia->prefeitura?->unidades->map(fn($u) => [
            'id' => $u->id,
            'nome' => $u->nome,
            'servidor_responsavel' => $u->servidor_responsavel,
        ])->values() ?? collect();
    @endphp
    <div class="mt-6 card !rounded-2xl overflow-hidden"
         x-data="assinantesFiscalizacao({
            unidades: {{ $unidadesAssinantes->toJson() }},
            fiscais: {{ ($fiscais ?? collect())->toJson() }},
            iniciais: {{ json_encode($ocorrencia->assinantes ?? []) }},
            saveUrl: '{{ route('admin.ocorrencias.assinantes', $ocorrencia->id) }}'
         })">
        <div class="px-6 py-4 border-b border-[#24403c] bg-[#0d1b19] flex items-center justify-between">
            <h3 class="text-lg font-bold text-[#e6f0ee]">
                <i class="fas fa-file-signature text-[#2DC197] mr-2"></i>Quem deve assinar o registro?
            </h3>
            <span class="text-xs font-semibold text-[#a2b9b5]" x-show="assinantes.length > 0">
                <span class="text-[#2DC197]" x-text="assinantes.length"></span> assinante(s)
            </span>
        </div>

        <div class="p-6 space-y-5">
            <p class="text-sm text-[#a2b9b5]">
                Informe abaixo os servidores que deverão assinar o registro impresso. Os nomes aparecerão no rodapé do documento e a assinatura será feita fisicamente.
            </p>

            <div class="flex flex-col gap-3 p-4 bg-[#0a1514] border border-[#24403c] rounded-xl sm:flex-row sm:items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block mb-1 text-xs font-semibold text-[#a2b9b5]">Selecione a secretaria ou o fiscal responsável</label>
                    <select x-model="selecionado" @change="aoSelecionar()"
                            class="block w-full px-3 py-2 text-sm bg-[#10201e] border border-[#24403c] text-[#e6f0ee] rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197]">
                        <option value="">Selecione (ou preencha manualmente)</option>
                        <optgroup label="Secretarias" x-show="unidades.length">
                            <template x-for="u in unidades" :key="'u'+u.id">
                                <option :value="'unidade:'+u.id" x-text="u.nome"></option>
                            </template>
                        </optgroup>
                        <optgroup label="Fiscais / Usuários" x-show="fiscais.length">
                            <template x-for="f in fiscais" :key="'f'+f.id">
                                <option :value="'fiscal:'+f.id" x-text="f.nome + (f.unidade ? ' — ' + f.unidade : '')"></option>
                            </template>
                        </optgroup>
                    </select>
                </div>
                <div class="flex-1 min-w-[160px]">
                    <label class="block mb-1 text-xs font-semibold text-[#a2b9b5]">Informe o nome completo do servidor <span class="text-red-400">*</span></label>
                    <input type="text" x-model="formNome" placeholder="Nome completo"
                           class="block w-full px-3 py-2 text-sm text-[#e6f0ee] bg-[#10201e] border border-[#24403c] rounded-xl shadow-sm focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197]">
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="block mb-1 text-xs font-semibold text-[#a2b9b5]">Informe o cargo ou a função do servidor</label>
                    <input type="text" x-model="formCargo" placeholder="Ex: Fiscal de Contrato"
                           class="block w-full px-3 py-2 text-sm text-[#e6f0ee] bg-[#10201e] border border-[#24403c] rounded-xl shadow-sm focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197]">
                </div>
                <div>
                    <button type="button" @click="adicionar()" :disabled="!formNome.trim()"
                            class="flex items-center justify-center w-full px-5 py-2 text-sm font-bold text-[#041d1a] transition-all bg-[#2DC197] rounded-xl shadow disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[#26aa84]">
                        + Adicionar
                    </button>
                </div>
            </div>

            <div class="border border-[#24403c] rounded-xl overflow-hidden bg-[#0a1514]">
                <div class="px-4 py-2.5 bg-[#0d1b19] border-b border-[#24403c]">
                    <span class="text-xs font-bold text-[#2DC197] uppercase tracking-wide">Assinantes Selecionados</span>
                </div>
                <template x-if="assinantes.length === 0">
                    <div class="p-6 text-sm text-center text-[#a2b9b5]">Nenhum assinante adicionado.</div>
                </template>
                <ul class="divide-y divide-[#24403c]/60">
                    <template x-for="(a, idx) in assinantes" :key="idx">
                        <li class="flex items-center justify-between px-4 py-3 hover:bg-[#142825] transition-colors">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[#e6f0ee] truncate" x-text="a.nome"></p>
                                <p class="text-xs text-[#a2b9b5] truncate"
                                   x-text="[a.cargo, a.unidade].filter(Boolean).join(' — ')"></p>
                            </div>
                            <button type="button" @click="remover(idx)"
                                    class="px-2.5 py-1.5 text-xs font-medium text-red-400 hover:text-red-300 hover:bg-[#2a1315] rounded-lg transition-colors">
                                Remover
                            </button>
                        </li>
                    </template>
                </ul>
            </div>

            <form method="POST" :action="saveUrl" @submit="salvando = true">
                @csrf
                <template x-for="(a, idx) in assinantes" :key="'h'+idx">
                    <span>
                        <input type="hidden" :name="`assinantes[${idx}][nome]`" :value="a.nome">
                        <input type="hidden" :name="`assinantes[${idx}][cargo]`" :value="a.cargo">
                        <input type="hidden" :name="`assinantes[${idx}][unidade]`" :value="a.unidade">
                    </span>
                </template>
                <div class="flex justify-end">
                    <button type="submit" :disabled="salvando"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-[#041d1a] bg-[#2DC197] rounded-xl hover:bg-[#26aa84] transition-all disabled:opacity-60 shadow-sm hover:scale-105">
                        <i class="fas fa-save"></i> Salvar Assinantes
                    </button>
                </div>
            </form>
        </div>
    </div>
                </div>
                <template x-if="assinantes.length === 0">
                    <div class="p-6 text-sm text-center text-gray-500">Nenhum assinante adicionado.</div>
                </template>
                <ul class="divide-y divide-emerald-100">
                    <template x-for="(a, idx) in assinantes" :key="idx">
                        <li class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate" x-text="a.nome"></p>
                                <p class="text-xs text-gray-500 truncate"
                                   x-text="[a.cargo, a.unidade].filter(Boolean).join(' — ')"></p>
                            </div>
                            <button type="button" @click="remover(idx)"
                                    class="px-2.5 py-1.5 text-xs font-medium text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md transition-colors">
                                Remover
                            </button>
                        </li>
                    </template>
                </ul>
            </div>

            <form method="POST" :action="saveUrl" @submit="salvando = true">
                @csrf
                <template x-for="(a, idx) in assinantes" :key="'h'+idx">
                    <span>
                        <input type="hidden" :name="`assinantes[${idx}][nome]`" :value="a.nome">
                        <input type="hidden" :name="`assinantes[${idx}][cargo]`" :value="a.cargo">
                        <input type="hidden" :name="`assinantes[${idx}][unidade]`" :value="a.unidade">
                    </span>
                </template>
                <div class="flex justify-end">
                    <button type="submit" :disabled="salvando"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-[#062F43] rounded-lg hover:bg-[#0596A2] transition-colors disabled:opacity-60 shadow-sm">
                        <i class="fas fa-save"></i> Salvar Assinantes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
<script>
    function assinantesFiscalizacao({ unidades, fiscais, iniciais, saveUrl }) {
        return {
            unidades: unidades || [],
            fiscais: fiscais || [],
            assinantes: (iniciais || []).map(a => ({
                nome: a.nome ?? '',
                cargo: a.cargo ?? '',
                unidade: a.unidade ?? '',
            })),
            saveUrl,
            salvando: false,
            selecionado: '',
            formNome: '',
            formCargo: '',
            formUnidade: '',

            aoSelecionar() {
                if (!this.selecionado) { this.formUnidade = ''; return; }
                const [tipo, id] = this.selecionado.split(':');

                if (tipo === 'unidade') {
                    const u = this.unidades.find(x => x.id == id);
                    if (u) {
                        this.formNome = u.servidor_responsavel || this.formNome;
                        this.formUnidade = u.nome || '';
                    }
                } else if (tipo === 'fiscal') {
                    const f = this.fiscais.find(x => x.id == id);
                    if (f) {
                        this.formNome = f.nome || this.formNome;
                        this.formUnidade = f.unidade || '';
                    }
                }
            },

            adicionar() {
                if (!this.formNome.trim()) return;
                this.assinantes.push({
                    nome: this.formNome.trim(),
                    cargo: this.formCargo.trim(),
                    unidade: this.formUnidade || '',
                });
                this.formNome = '';
                this.formCargo = '';
                this.formUnidade = '';
                this.selecionado = '';
            },

            remover(idx) {
                this.assinantes.splice(idx, 1);
            },
        };
    }
</script>
@endonce
@endsection
