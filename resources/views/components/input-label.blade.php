@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-bold text-[11px] uppercase tracking-[.06em] text-[var(--ink-3)] mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
