{{-- ============================================================
  Modal "Iniciar Novo Aditivo" — único componente reaproveitado por
  contrato.blade.php (Contrato do Sistema, tema claro) e
  show_manual.blade.php (Contrato Manual, tema escuro). Antes desta
  atualização (TCU) era HTML duplicado em cada página; agora os campos
  novos (natureza da alteração, tipo de objeto, contrato contínuo) só
  precisam existir em um lugar.

  Props:
    - modalId       (string) id do elemento modal (permite múltiplas instâncias na mesma página)
    - actionRoute    (string) URL do form (admin.incidentes.store ou admin.incidentes-manual.store)
    - dark           (bool)   tema escuro (contratos manuais) vs. claro (contrato do sistema)
    - forcarExterno  (bool)   usuário de prefeitura só pode abrir aditivo externo
============================================================ --}}
@props([
    'modalId' => 'modalNovoAditivo',
    'actionRoute' => '', // vazio quando o form.action é setado dinamicamente via JS (ver abrirModalAditivo())
    'dark' => false,
    'forcarExterno' => false,
])

@php
    $cls = $dark ? [
        'overlay' => 'fixed inset-0 transition-opacity bg-[var(--bg)]/80 backdrop-blur-sm',
        'painel' => 'inline-block overflow-hidden text-left align-bottom transition-all transform bg-[var(--card)] border border-[var(--line)] rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full',
        'corpo' => 'px-5 pt-6 pb-5 bg-[var(--card)] sm:p-6 sm:pb-5',
        'icone_bg' => 'flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-[var(--card-3)] border border-[var(--line)] rounded-xl sm:mx-0 sm:h-10 sm:w-10',
        'icone' => 'text-[var(--mint)] fas fa-file-contract',
        'titulo' => 'text-lg font-bold leading-6 text-[var(--ink)]',
        'label' => 'block text-sm font-semibold text-[var(--ink-2)]',
        'select' => 'block w-full mt-1.5 px-4 py-2.5 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-xl focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] sm:text-sm',
        'radio' => 'text-[var(--mint)] focus:ring-[var(--mint)] bg-[var(--bg)] border-[var(--line)]',
        'radio_label' => 'inline-flex items-center gap-1.5 text-sm text-[var(--ink)] cursor-pointer',
        'rodape' => 'px-5 py-4 bg-[var(--card-2)] border-t border-[var(--line)] sm:px-6 sm:flex sm:flex-row-reverse',
        'btn_submit' => 'inline-flex justify-center w-full px-5 py-2.5 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] transition-all shadow-sm sm:ml-3 sm:w-auto hover:scale-105',
        'btn_cancelar' => 'inline-flex justify-center w-full px-4 py-2.5 mt-3 text-sm font-semibold text-[var(--ink)] bg-[var(--card)] border border-[var(--line)] rounded-xl hover:bg-[var(--hover-bg)] hover:text-[var(--mint)] transition-colors sm:mt-0 sm:ml-3 sm:w-auto',
        'info' => 'text-xs text-[var(--ink-2)]',
    ] : [
        'overlay' => 'fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75',
        'painel' => 'inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full',
        'corpo' => 'px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4',
        'icone_bg' => 'flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-teal-100 rounded-full sm:mx-0 sm:h-10 sm:w-10',
        'icone' => 'text-teal-600 fas fa-file-contract',
        'titulo' => 'text-lg font-medium leading-6 text-gray-900',
        'label' => 'block text-sm font-medium text-gray-700',
        'select' => 'block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[var(--mint)] focus:border-[var(--mint)] sm:text-sm',
        'radio' => 'text-[var(--teal-text)] focus:ring-[var(--mint)]',
        'radio_label' => 'inline-flex items-center gap-1.5 text-sm text-gray-700',
        'rodape' => 'px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse',
        'btn_submit' => 'inline-flex justify-center w-full px-4 py-2 text-base font-bold text-[var(--on-mint)] bg-[var(--mint)] border border-transparent rounded-md shadow-sm hover:bg-[var(--mint-h)] focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors',
        'btn_cancelar' => 'inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm',
        'info' => 'text-xs text-gray-500',
    ];
@endphp

<div id="{{ $modalId }}" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="{{ $modalId }}-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="{{ $cls['overlay'] }}" aria-hidden="true" onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="{{ $cls['painel'] }}" x-data="{ tipo: '', categoria: '' }">
            <form id="{{ $modalId }}-form" method="POST" action="{{ $actionRoute }}">
                @csrf
                <div class="{{ $cls['corpo'] }}">
                    <div class="sm:flex sm:items-start">
                        <div class="{{ $cls['icone_bg'] }}">
                            <i class="{{ $cls['icone'] }}"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="{{ $cls['titulo'] }}" id="{{ $modalId }}-title">Iniciar Novo Aditivo</h3>

                            @if($dark)
                                <p class="mt-1 text-xs text-[var(--warn-2)]">
                                    <i class="fas fa-circle-info"></i> Contrato Manual/Externo: os documentos serão gerados para assinatura física.
                                </p>
                            @endif

                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="{{ $cls['label'] }}">Tipo de Aditivo <span class="text-red-500">*</span></label>
                                    <select name="tipo" x-model="tipo" required class="{{ $cls['select'] }}">
                                        <option value="">Selecione...</option>
                                        <option value="prazo">Prorrogação de Prazo</option>
                                        <option value="valor">Acréscimo/Supressão de Valor</option>
                                        <option value="prazo_valor">Prazo e Valor</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="{{ $cls['label'] }}">Categoria do Contrato <span class="text-red-500">*</span></label>
                                    <select name="categoria" x-model="categoria" required class="{{ $cls['select'] }}">
                                        <option value="">Selecione...</option>
                                        <option value="compras_servicos">Compras e Serviços</option>
                                        <option value="obras">Obras</option>
                                    </select>
                                </div>

                                {{-- Campos exigidos pela atualização TCU --}}
                                <div x-show="tipo === 'valor' || tipo === 'prazo_valor'" x-cloak>
                                    <label class="{{ $cls['label'] }}">Natureza da Alteração de Valor <span class="text-red-500">*</span></label>
                                    <select name="natureza_alteracao" class="{{ $cls['select'] }}">
                                        <option value="">Selecione...</option>
                                        <option value="acrescimo">Apenas Acréscimo</option>
                                        <option value="supressao">Apenas Supressão</option>
                                        <option value="acrescimo_e_supressao">Acréscimo e Supressão</option>
                                    </select>
                                </div>
                                <div x-show="categoria === 'compras_servicos'" x-cloak>
                                    <label class="{{ $cls['label'] }}">Tipo de Objeto do Contrato <span class="text-red-500">*</span></label>
                                    <select name="tipo_objeto_contrato" class="{{ $cls['select'] }}">
                                        <option value="">Selecione...</option>
                                        <option value="compra">Compra (Fornecimento)</option>
                                        <option value="servico">Serviço</option>
                                    </select>
                                </div>
                                <div x-show="tipo === 'prazo' || tipo === 'prazo_valor'" x-cloak>
                                    <label class="{{ $cls['label'] }}">O Contrato é Contínuo? <span class="text-red-500">*</span></label>
                                    <select name="contrato_continuo" class="{{ $cls['select'] }}">
                                        <option value="">Selecione...</option>
                                        <option value="1">Sim (fornecimento/serviço contínuo — art. 107)</option>
                                        <option value="0">Não (demais contratos — art. 111)</option>
                                    </select>
                                </div>

                                @if($forcarExterno)
                                    <input type="hidden" name="origem" value="externo">
                                    <p class="{{ $cls['info'] }}">
                                        <i class="fas fa-circle-info"></i> Este aditivo será registrado como <strong>Externo</strong>.
                                        Para corrigir ou cancelar um aditivo depois de criado, entre em contato com a equipe Licicon.
                                    </p>
                                @else
                                    <div>
                                        <label class="{{ $cls['label'] }}">Origem do Aditivo <span class="text-red-500">*</span></label>
                                        <div class="flex gap-4 mt-1">
                                            <label class="{{ $cls['radio_label'] }}">
                                                <input type="radio" name="origem" value="interno" checked class="{{ $cls['radio'] }}">
                                                Interno (gera documentos)
                                            </label>
                                            <label class="{{ $cls['radio_label'] }}">
                                                <input type="radio" name="origem" value="externo" class="{{ $cls['radio'] }}">
                                                Externo (feito fora do sistema)
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ $cls['rodape'] }}">
                    <button type="submit" class="{{ $cls['btn_submit'] }}">Iniciar Aditivo</button>
                    <button type="button" onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')" class="{{ $cls['btn_cancelar'] }}">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
