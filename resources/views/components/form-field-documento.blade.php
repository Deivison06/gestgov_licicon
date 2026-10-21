{{-- resources/views/components/form-field.blade.php --}}
@props(['name', 'label', 'type' => 'text', 'value' => ''])

<div class="flex items-start space-x-2">
    <div class="flex-1">
        <label for="{{ $name }}" class="block mb-1 text-sm font-medium text-gray-700">
            {{ $label }}
        </label>

        @if($type === 'textarea')
            <textarea
                id="{{ $name }}"
                name="{{ $name }}"
                x-model="{{ $name }}"
                :disabled="confirmed.{{ $name }}"
                class="block w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-[var(--mint)] focus:border-[var(--mint)] sm:text-sm"
                rows="4"
            >{{ $value }}</textarea>
        @else
            <input
                type="{{ $type }}"
                id="{{ $name }}"
                name="{{ $name }}"
                x-model="{{ $name }}"
                :disabled="confirmed.{{ $name }}"
                class="block w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-[var(--mint)] focus:border-[var(--mint)] sm:text-sm"
                value="{{ $value }}"
            >
        @endif
    </div>
    <div class="flex pt-6 space-x-1">
        <button type="button" @click="saveField('{{ $name }}')"
                x-show="!confirmed.{{ $name }}" :disabled="!{{ $name }}"
                class="px-3 py-2 text-white transition rounded-lg"
                :class="!{{ $name }} ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-500 hover:bg-green-600'">
            ✔
        </button>
        <button type="button" @click="toggleConfirm('{{ $name }}')"
                x-show="confirmed.{{ $name }}"
                class="px-3 py-2 text-white bg-red-500 rounded-lg hover:bg-red-600">
            ✖
        </button>
    </div>
</div>
