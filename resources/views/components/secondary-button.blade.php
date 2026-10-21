<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-[var(--card)] border border-[var(--line)] rounded-[11px] font-semibold text-xs text-[var(--ink)] uppercase tracking-wider shadow-sm hover:bg-[var(--hover-bg)] hover:border-[var(--teal-text)] focus:outline-none focus:ring-2 focus:ring-[var(--teal-text)] focus:ring-offset-2 focus:ring-offset-[var(--bg)] disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
