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
            <h2 class="text-2xl font-bold text-gray-800">Ocorrência {{ $ocorrencia->numero_ocorrencia }}</h2>
            <span class="inline-flex items-center px-2.5 py-0.5 mt-1 rounded-full text-xs font-semibold {{ $ocorrencia->status_badge_class }}">
                {{ $ocorrencia->status_texto }}
            </span>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.ocorrencias.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>

            @unless($bloqueado)
                <a href="{{ route('admin.ocorrencias.edit', $ocorrencia->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 transition-colors bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 shadow-sm">
                    <i class="fas fa-edit text-amber-500"></i> Editar Dados
                </a>
            @endunless

            <div class="hidden md:block w-px h-8 bg-gray-300 mx-1"></div>

            <a href="{{ route('admin.ocorrencias.pdf.registro', $ocorrencia->id) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white transition-colors bg-[#009496] rounded-lg hover:bg-[#062F43] shadow-sm">
                <i class="fas fa-file-contract"></i> Registro de Ocorrência
            </a>

            <a href="{{ route('admin.ocorrencias.pdf.notificacoes', $ocorrencia->id) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white transition-colors bg-red-600 rounded-lg hover:bg-red-800 shadow-sm">
                <i class="fas fa-exclamation-triangle"></i> Notificações
            </a>

            @if($ocorrencia->correcao_descricao)
                <a href="{{ route('admin.ocorrencias.pdf.atesto', $ocorrencia->id) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white transition-colors bg-[#062F43] rounded-lg hover:bg-[#009496] shadow-sm">
                    <i class="fas fa-stamp"></i> Atesto de Correção
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- COLUNA ESQUERDA --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Dados do Registro</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Situação</dt>
                            <dd class="text-sm text-gray-900 col-span-2">
                                @if($ocorrencia->situacao)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $ocorrencia->situacao_badge_class }}">
                                        {{ $ocorrencia->situacao_texto }}
                                    </span>
                                @else
                                    <span class="text-gray-400">Não informada</span>
                                @endif
                            </dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Data</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $ocorrencia->data_ocorrencia?->format('d/m/Y') }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Local</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $ocorrencia->local ?? '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Fiscal</dt>
                            <dd class="text-sm text-gray-900 col-span-2 font-medium">{{ $ocorrencia->user->name ?? 'Sistema' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Criado em</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $ocorrencia->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Contrato Vinculado</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Empresa Contratada</dt>
                            <dd class="text-sm font-bold text-gray-900 mt-1">{{ $info['razao_social'] }}</dd>
                            <dd class="text-xs text-gray-500">{{ $info['cnpj'] }}</dd>
                        </div>
                        <div class="pt-3 border-t border-gray-50">
                            <dt class="text-xs font-medium text-gray-400 uppercase">Nº Contrato / Processo</dt>
                            <dd class="text-sm text-gray-700 mt-1">{{ $info['numero_contrato'] }} ({{ $info['numero_processo'] }})</dd>
                        </div>
                        <div class="pt-3 border-t border-gray-50">
                            <dt class="text-xs font-medium text-gray-400 uppercase">Secretaria</dt>
                            <dd class="text-sm text-gray-700 mt-1">{{ $info['secretaria'] }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if($ocorrencia->status->value === 'registrada')
                <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden p-6">
                    <p class="text-sm text-gray-500 mb-3">
                        Só é possível concluir depois de preencher o Atesto de Correção (descrição e data).
                    </p>
                    <form method="POST" action="{{ route('admin.ocorrencias.concluir', $ocorrencia->id) }}"
                          onsubmit="return confirm('Concluir esta ocorrência? Depois disso ela não poderá mais ser editada.');">
                        @csrf
                        <button type="submit" {{ $ocorrencia->correcao_descricao && $ocorrencia->correcao_data ? '' : 'disabled' }}
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-sm">
                            <i class="fas fa-check-circle"></i> Concluir Ocorrência
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- COLUNA PRINCIPAL --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Descrição da Ocorrência</h3>
                </div>
                <div class="p-8 space-y-8">
                    <div class="relative pl-8">
                        <div class="absolute left-0 top-1 text-[#0596A2]"><i class="fas fa-file-lines text-lg"></i></div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Descrição do Fato</h4>
                        <div class="mt-2 text-sm text-gray-700 leading-relaxed bg-gray-50/50 p-4 rounded-xl border border-gray-50">
                            {!! nl2br(e($ocorrencia->descricao_fato)) !!}
                        </div>
                    </div>

                    @if($ocorrencia->obrigacao_descumprida)
                        <div class="relative pl-8">
                            <div class="absolute left-0 top-1 text-[#0596A2]"><i class="fas fa-gavel text-lg"></i></div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Obrigação Descumprida</h4>
                            <div class="mt-2 text-sm text-gray-700 leading-relaxed bg-gray-50/50 p-4 rounded-xl border border-gray-50">
                                {!! nl2br(e($ocorrencia->obrigacao_descumprida)) !!}
                            </div>
                        </div>
                    @endif

                    @if($ocorrencia->prazo_resposta)
                        <div class="relative pl-8">
                            <div class="absolute left-0 top-1 text-[#0596A2]"><i class="fas fa-clock text-lg"></i></div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Prazo para Resposta / Solução</h4>
                            <div class="mt-2 text-sm text-gray-700 leading-relaxed bg-gray-50/50 p-4 rounded-xl border border-gray-50">
                                {{ $ocorrencia->prazo_resposta }}
                            </div>
                        </div>
                    @endif

                    @if(!empty($ocorrencia->tipo_comprovacao))
                        <div class="relative pl-8">
                            <div class="absolute left-0 top-1 text-[#0596A2]"><i class="fas fa-paperclip text-lg"></i></div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Meio de Comprovação</h4>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach(\App\Models\Ocorrencia::TIPOS_COMPROVACAO as $chave => $rotulo)
                                    @if(data_get($ocorrencia->tipo_comprovacao, $chave))
                                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                            {{ $chave === 'outros' && $ocorrencia->tipo_comprovacao_outro ? $ocorrencia->tipo_comprovacao_outro : $rotulo }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ANEXOS DO FATO --}}
                    <div class="relative pl-8">
                        <div class="absolute left-0 top-1 text-[#0596A2]"><i class="fas fa-camera text-lg"></i></div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Fotografias / Documentos</h4>
                        @php $anexosFato = $ocorrencia->anexos->where('categoria', 'fato'); @endphp
                        @if($anexosFato->isNotEmpty())
                            <ul class="space-y-1">
                                @foreach($anexosFato as $anexo)
                                    <li class="flex items-center justify-between text-sm bg-gray-50/50 p-2.5 rounded-lg border border-gray-50">
                                        <a href="{{ asset($anexo->caminho) }}" target="_blank" class="text-blue-600 hover:underline truncate">
                                            <i class="fas fa-paperclip mr-1"></i>{{ $anexo->nome_original ?? basename($anexo->caminho) }}
                                        </a>
                                        @unless($bloqueado)
                                            <form method="POST" action="{{ route('admin.ocorrencias.anexos.delete', [$ocorrencia->id, $anexo->id]) }}" onsubmit="return confirm('Remover este anexo?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 ml-3"><i class="fas fa-trash-alt"></i></button>
                                            </form>
                                        @endunless
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-400">Nenhum anexo.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ============================================================== --}}
            {{-- RESPOSTA DA EMPRESA --}}
            {{-- ============================================================== --}}
            @if($ocorrencia->status->value !== 'rascunho')
                <div class="mt-6 bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800"><i class="fas fa-reply text-[#0596A2] mr-2"></i>Resposta da Empresa</h3>
                        <span class="text-xs font-medium text-gray-500">{{ $ocorrencia->anexosResposta()->count() }} anexo(s)</span>
                    </div>
                    <div class="p-6 space-y-4">
                        @unless($bloqueado)
                            <form method="POST" action="{{ route('admin.ocorrencias.anexos.upload', $ocorrencia->id) }}" enctype="multipart/form-data"
                                  class="flex flex-col gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg sm:flex-row sm:items-center">
                                @csrf
                                <input type="hidden" name="categoria" value="resposta">
                                <input type="file" name="anexos[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp" required
                                       class="flex-1 text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#009496] file:text-white hover:file:bg-[#007779] cursor-pointer">
                                <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007779] transition-colors shadow-sm">
                                    <i class="fas fa-upload"></i> Anexar Resposta
                                </button>
                            </form>
                        @endunless

                        @forelse($ocorrencia->anexosResposta()->get() as $anexo)
                            <div class="flex items-center justify-between text-sm bg-gray-50/50 p-2.5 rounded-lg border border-gray-50">
                                <a href="{{ asset($anexo->caminho) }}" target="_blank" class="text-blue-600 hover:underline truncate">
                                    <i class="fas fa-file-pdf mr-1"></i>{{ $anexo->nome_original ?? basename($anexo->caminho) }}
                                </a>
                                @unless($bloqueado)
                                    <form method="POST" action="{{ route('admin.ocorrencias.anexos.delete', [$ocorrencia->id, $anexo->id]) }}" onsubmit="return confirm('Remover este anexo?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 ml-3"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                @endunless
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">Nenhuma resposta anexada ainda.</p>
                        @endforelse
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- ATESTO DE CORREÇÃO --}}
                {{-- ============================================================== --}}
                <div class="mt-6 bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-800"><i class="fas fa-stamp text-[#0596A2] mr-2"></i>Atesto de Correção</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        @if($bloqueado || $ocorrencia->correcao_descricao)
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Solução Adotada</dt>
                                    <dd class="text-sm text-gray-700 mt-1">{!! nl2br(e($ocorrencia->correcao_descricao)) !!}</dd>
                                </div>
                                <div class="pt-3 border-t border-gray-50">
                                    <dt class="text-xs font-medium text-gray-400 uppercase">Data da Correção</dt>
                                    <dd class="text-sm text-gray-700 mt-1">{{ $ocorrencia->correcao_data?->format('d/m/Y') }}</dd>
                                </div>
                                @if($ocorrencia->correcao_elementos_comprobatorios)
                                    <div class="pt-3 border-t border-gray-50">
                                        <dt class="text-xs font-medium text-gray-400 uppercase">Elementos Comprobatórios</dt>
                                        <dd class="text-sm text-gray-700 mt-1">{!! nl2br(e($ocorrencia->correcao_elementos_comprobatorios)) !!}</dd>
                                    </div>
                                @endif
                            </dl>
                        @endif

                        @foreach($ocorrencia->anexosCorrecao()->get() as $anexo)
                            <div class="flex items-center justify-between text-sm bg-gray-50/50 p-2.5 rounded-lg border border-gray-50">
                                <a href="{{ asset($anexo->caminho) }}" target="_blank" class="text-blue-600 hover:underline truncate">
                                    <i class="fas fa-file-pdf mr-1"></i>{{ $anexo->nome_original ?? basename($anexo->caminho) }}
                                </a>
                                @unless($bloqueado)
                                    <form method="POST" action="{{ route('admin.ocorrencias.anexos.delete', [$ocorrencia->id, $anexo->id]) }}" onsubmit="return confirm('Remover este anexo?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 ml-3"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                @endunless
                            </div>
                        @endforeach

                        @unless($bloqueado)
                            <form method="POST" action="{{ route('admin.ocorrencias.atesto.salvar', $ocorrencia->id) }}" enctype="multipart/form-data" class="pt-4 border-t border-gray-100 space-y-4">
                                @csrf
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-gray-700">Descrição da Solução Adotada</label>
                                    <textarea name="correcao_descricao" rows="3" required
                                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496]">{{ old('correcao_descricao', $ocorrencia->correcao_descricao) }}</textarea>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-gray-700">Data da Correção</label>
                                        <input type="date" name="correcao_data" required
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496]"
                                               value="{{ old('correcao_data', optional($ocorrencia->correcao_data)->format('Y-m-d')) }}">
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-gray-700">Anexar Comprovação (PDF/Imagem)</label>
                                        <input type="file" name="anexos_correcao[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp"
                                               class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#009496] file:text-white hover:file:bg-[#007779] cursor-pointer">
                                    </div>
                                </div>
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-gray-700">Elementos que Comprovam a Correção</label>
                                    <textarea name="correcao_elementos_comprobatorios" rows="2"
                                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#009496] focus:border-[#009496]">{{ old('correcao_elementos_comprobatorios', $ocorrencia->correcao_elementos_comprobatorios) }}</textarea>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-[#062F43] rounded-lg hover:bg-[#0596A2] transition-colors shadow-sm">
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
    <div class="mt-6 bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden"
         x-data="assinantesFiscalizacao({
            unidades: {{ $unidadesAssinantes->toJson() }},
            fiscais: {{ ($fiscais ?? collect())->toJson() }},
            iniciais: {{ json_encode($ocorrencia->assinantes ?? []) }},
            saveUrl: '{{ route('admin.ocorrencias.assinantes', $ocorrencia->id) }}'
         })">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-file-signature text-[#0596A2] mr-2"></i>Assinantes do Registro
            </h3>
            <span class="text-xs font-medium text-gray-500" x-show="assinantes.length > 0">
                <span x-text="assinantes.length"></span> assinante(s)
            </span>
        </div>

        <div class="p-6 space-y-5">
            <p class="text-sm text-gray-500">
                Selecione os servidores responsáveis que assinarão fisicamente o registro. Os assinantes aparecem no rodapé do documento impresso, sem assinatura eletrônica.
            </p>

            <div class="flex flex-col gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg sm:flex-row sm:items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block mb-1 text-xs font-medium text-gray-600">Secretaria ou Fiscal</label>
                    <select x-model="selecionado" @change="aoSelecionar()"
                            class="block w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#009496] focus:border-[#009496]">
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
                    <label class="block mb-1 text-xs font-medium text-gray-600">Nome do Servidor <span class="text-red-500">*</span></label>
                    <input type="text" x-model="formNome" placeholder="Nome completo"
                           class="block w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:ring-[#009496]">
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="block mb-1 text-xs font-medium text-gray-600">Cargo / Função</label>
                    <input type="text" x-model="formCargo" placeholder="Ex: Fiscal de Contrato"
                           class="block w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:ring-[#009496]">
                </div>
                <div>
                    <button type="button" @click="adicionar()" :disabled="!formNome.trim()"
                            class="flex items-center justify-center w-full px-4 py-2 text-sm font-semibold text-white transition-colors bg-[#009496] rounded-md shadow disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[#007779]">
                        + Adicionar
                    </button>
                </div>
            </div>

            <div class="border border-emerald-200 rounded-lg overflow-hidden bg-white">
                <div class="px-4 py-2.5 bg-emerald-50 border-b border-emerald-200">
                    <span class="text-xs font-semibold text-emerald-800 uppercase tracking-wide">Assinantes Selecionados</span>
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
