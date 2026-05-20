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
                <x-ia-popover :name="$name" :processoId="$iaProcessoId" />
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

     {{-- Botões de Confirmação CORRIGIDOS para datetime --}}
    <div class="flex pt-6 space-x-1">
        {{-- Botão Salvar para datetime --}}
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
