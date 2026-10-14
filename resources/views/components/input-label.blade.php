@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-bold text-[11px] uppercase tracking-[.06em] text-[#8aa29d] mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
