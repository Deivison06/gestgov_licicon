@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-[var(--card-2)] border-[var(--line)] text-[var(--ink)] placeholder-[var(--ink-3)] focus:border-[var(--mint)] focus:ring-[var(--mint)]/25 rounded-xl shadow-sm text-sm transition']) }}>
