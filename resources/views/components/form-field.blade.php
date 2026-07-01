{{-- resources/views/components/form-field.blade.php --}}
@props([
    'name',
    'label',
    'type' => 'text', // 'text', 'textarea', 'password', 'select', 'radio', 'checkbox', 'file', 'date', 'time', 'datetime'
    'options' => [], // Para select, radio, checkbox
    'multiple' => false, // Para select múltiplo
    'accept' => '', // Para file inputs
    'placeholder' => '',
    'rows' => 3, // Para textarea
    'ia' => false, // Quando true, exibe botão "✨ IA" ao lado do label (gera conteúdo via OpenAI)
    'iaProcessoId' => null, // Opcional: id do processo para contexto da IA
])

@php
    // Classes centralizadas
    $fieldClasses = 'block w-full mt-1 border-gray-300 rounded-lg shadow-sm sm:text-sm ' .
                    'focus:ring-[#009496] focus:border-[#009496] ' .
                    'disabled:bg-gray-100 disabled:cursor-not-allowed ' .
                    'readonly:bg-gray-100';

    $selectClasses = $fieldClasses . ' cursor-pointer';
    $fileClasses = 'block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#009496] file:text-white hover:file:bg-[#007779]';

    $buttonClasses = 'flex items-center justify-center w-8 h-8 transition-colors duration-200 rounded-lg';
    $saveButtonClasses = $buttonClasses . ' bg-green-500 hover:bg-green-600 text-white';
    $cancelButtonClasses = $buttonClasses . ' bg-red-500 hover:bg-red-600 text-white';
    $disabledButtonClasses = $buttonClasses . ' bg-gray-400 cursor-not-allowed text-white';
@endphp

<div class="flex items-start mb-4 space-x-2">
    <div class="flex-1">
        <div class="flex items-center justify-between mb-1">
            <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
                {{ $label }}
            </label>
            @if ($ia)
                @include('partials.ia-helper-script')
                <div x-data="{ openIaModal: false, iaInstruction: '', iaReplace: true, iaLoading: false }" class="relative inline-block text-left">
                    <button type="button"
                        @click="openIaModal = !openIaModal; if(openIaModal) setTimeout(() => $refs.iaInput.focus(), 50)"
                        class="flex items-center gap-1 px-2 py-1 text-xs font-medium text-white transition-colors rounded-md bg-[#009496] hover:bg-[#007779] shadow-sm"
                        title="Gerar/Regenerar este texto com IA">
                        <span>✨</span><span>IA</span>
                    </button>
                    
                    <div x-show="openIaModal" 
                         @click.away="openIaModal = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         style="display: none; min-width: 320px;"
                         class="absolute right-0 z-50 p-4 mt-2 bg-white border border-gray-200 rounded-lg shadow-xl">
                         
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-800 text-md">Gerar com IA</h4>
                            <button @click="openIaModal = false" type="button" class="text-gray-400 transition-colors hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        
                        <p class="mb-1 text-sm text-gray-600">Descreva o que você precisa</p>
                        <textarea 
                            x-ref="iaInput"
                            x-model="iaInstruction" 
                            rows="3" 
                            class="w-full p-2 mb-2 text-sm border border-gray-300 rounded-md shadow-sm focus:border-[#009496] focus:ring focus:ring-[#009496] focus:ring-opacity-50"
                            placeholder="Ex: Compra de 200 cadeiras para 3 escolas municipais. Justifique a necessidade."
                            @keydown.enter.prevent="if(!iaLoading) { iaLoading = true; window.iaGerarCampoComInstrucao(document.getElementById('{{ $name }}'), iaInstruction, iaReplace, true).then(() => { openIaModal = false; iaLoading = false; }) }"
                        ></textarea>
                        
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-xs text-gray-400" x-text="iaInstruction.length + ' / 500'"></div>
                            <div class="text-xs text-gray-400">Pressione Enter para gerar</div>
                        </div>

                        <label class="flex items-center gap-2 mb-4 cursor-pointer">
                            <input type="checkbox" x-model="iaReplace" class="text-[#009496] focus:ring-[#009496] border-gray-300 rounded">
                            <span class="text-sm text-gray-600">Substituir conteúdo atual do campo</span>
                        </label>
                        
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="openIaModal = false" class="px-4 py-1.5 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="button" 
                                @click="iaLoading = true; window.iaGerarCampoComInstrucao(document.getElementById('{{ $name }}'), iaInstruction, iaReplace, true).then(() => { openIaModal = false; iaLoading = false; })"
                                :disabled="iaLoading"
                                class="px-4 py-1.5 text-sm font-medium text-white transition-colors bg-[#009496] rounded hover:bg-[#007779] disabled:opacity-50 flex items-center gap-1 shadow-sm">
                                <span x-show="iaLoading" class="animate-spin">
                                    <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </span>
                                <span x-show="!iaLoading">✨ Gerar</span>
                                <span x-show="iaLoading">Gerando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Campo DateTime CORRIGIDO --}}
        @if ($type === 'datetime')
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <div>
                    <label for="{{ $name }}_date" class="block mb-1 text-xs text-gray-500">Data</label>
                    <input
                        type="date"
                        id="{{ $name }}_date"
                        name="{{ $name }}_date"
                        x-model="{{ $name }}_date"
                        :disabled="confirmed.{{ $name }}"
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#009496] focus:border-[#009496] sm:text-sm"
                    >
                </div>
                <div>
                    <label for="{{ $name }}_time" class="block mb-1 text-xs text-gray-500">Hora</label>
                    <input
                        type="time"
                        id="{{ $name }}_time"
                        name="{{ $name }}_time"
                        x-model="{{ $name }}_time"
                        :disabled="confirmed.{{ $name }}"
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#009496] focus:border-[#009496] sm:text-sm"
                    >
                </div>
            </div>

            {{-- Campo hidden para valor combinado --}}
            <input type="hidden" name="{{ $name }}" x-model="{{ $name }}" />

            {{-- Display do valor salvo CORRIGIDO --}}
            <div x-show="confirmed.{{ $name }} && {{ $name }}" class="p-2 mt-2 text-sm text-green-700 border border-green-200 rounded bg-green-50">
                <span class="font-medium">Salvo:</span>
                <span x-text="formatDisplayDateTime({{ $name }})" class="ml-1"></span>
            </div>

        {{-- Outros tipos de campo... --}}
        @elseif ($type === 'text' || $type === 'password' || $type === 'email' || $type === 'number')
            <input
                type="{{ $type }}"
                id="{{ $name }}"
                name="{{ $name }}"
                x-model="{{ $name }}"
                :disabled="confirmed.{{ $name }}"
                placeholder="{{ $placeholder }}"
                {{ $attributes->merge(['class' => $fieldClasses]) }}
            >

        {{-- Campo Textarea --}}
        @elseif ($type === 'textarea')
            <textarea
                id="{{ $name }}"
                name="{{ $name }}"
                x-model="{{ $name }}"
                x-ref="{{ $name }}_editor"
                :disabled="confirmed.{{ $name }}"
                rows="{{ $rows }}"
                placeholder="{{ $placeholder }}"
                @if ($ia) data-ia-campo="{{ $name }}" data-ia-processo="{{ $iaProcessoId }}" @endif
                {{ $attributes->merge(['class' => $fieldClasses]) }}
            ></textarea>

        {{-- Campo Select --}}
        @elseif ($type === 'select')
            <select
                id="{{ $name }}"
                name="{{ $name }}"
                x-model="{{ $name }}"
                :disabled="confirmed.{{ $name }}"
                @if($multiple) multiple @endif
                {{ $attributes->merge(['class' => $selectClasses]) }}
            >
                @if(!$multiple)
                    <option value="">{{ $placeholder ?: 'Selecione uma opção' }}</option>
                @endif

                @foreach($options as $value => $text)
                    <option value="{{ $value }}">{{ $text }}</option>
                @endforeach
            </select>

        {{-- Campo Radio --}}
        @elseif ($type === 'radio')
            <div class="mt-2 space-y-2">
                @foreach($options as $value => $text)
                    <label class="inline-flex items-center mr-4">
                        <input
                            type="radio"
                            name="{{ $name }}"
                            x-model="{{ $name }}"
                            value="{{ $value }}"
                            :disabled="confirmed.{{ $name }}"
                            class="text-[#009496] focus:ring-[#009496] border-gray-300"
                        >
                        <span class="ml-2 text-sm text-gray-700">{{ $text }}</span>
                    </label>
                @endforeach
            </div>

        {{-- Campo Checkbox --}}
        @elseif ($type === 'checkbox')
            <div class="mt-2 space-y-2">
                @foreach($options as $value => $text)
                    <label class="inline-flex items-center mr-4">
                        <input
                            type="checkbox"
                            name="{{ $name }}[]"
                            x-model="{{ $name }}"
                            value="{{ $value }}"
                            :disabled="confirmed.{{ $name }}"
                            class="rounded text-[#009496] focus:ring-[#009496] border-gray-300"
                        >
                        <span class="ml-2 text-sm text-gray-700">{{ $text }}</span>
                    </label>
                @endforeach
            </div>

        {{-- Campo File -- NÃO USA x-model --}}
        @elseif ($type === 'file')
            <input
                type="file"
                id="{{ $name }}"
                name="{{ $name }}"
                :disabled="confirmed.{{ $name }}"
                accept="{{ $accept }}"
                {{ $attributes->merge(['class' => $fileClasses]) }}
            >

        {{-- Campo Date --}}
        @elseif ($type === 'date')
            <input
                type="date"
                id="{{ $name }}"
                name="{{ $name }}"
                x-model="{{ $name }}"
                :disabled="confirmed.{{ $name }}"
                {{ $attributes->merge(['class' => $fieldClasses]) }}
            >

        {{-- Campo Time --}}
        @elseif ($type === 'time')
            <input
                type="time"
                id="{{ $name }}"
                name="{{ $name }}"
                x-model="{{ $name }}"
                :disabled="confirmed.{{ $name }}"
                {{ $attributes->merge(['class' => $fieldClasses]) }}
            >

        {{-- Campo padrão (fallback) --}}
        @else
            <input
                type="text"
                id="{{ $name }}"
                name="{{ $name }}"
                x-model="{{ $name }}"
                :disabled="confirmed.{{ $name }}"
                placeholder="{{ $placeholder }}"
                {{ $attributes->merge(['class' => $fieldClasses]) }}
            >
        @endif
    </div>

    <div class="flex pt-6 space-x-1">
        {{-- Botão Salvar --}}
        @if ($type === 'datetime')
            <button
                type="button"
                @click="saveField('{{ $name }}')"
                x-show="!confirmed.{{ $name }}"
                :disabled="!{{ $name }}_date || !{{ $name }}_time"
                :class="(!{{ $name }}_date || !{{ $name }}_time) ? '{{ $disabledButtonClasses }}' : '{{ $saveButtonClasses }}'"
                title="Confirmar"
            >
                ✓
            </button>
        @else
            <button
                type="button"
                @click="saveField('{{ $name }}')"
                x-show="!confirmed.{{ $name }}"
                class="{{ $saveButtonClasses }}"
                title="Confirmar"
            >
                ✓
            </button>
        @endif

        {{-- Botão Cancelar/Editar --}}
        <button
            type="button"
            @click="toggleConfirm('{{ $name }}')"
            x-show="confirmed.{{ $name }}"
            class="{{ $cancelButtonClasses }}"
            title="Editar"
        >
            ✗
        </button>
    </div>
</div>
