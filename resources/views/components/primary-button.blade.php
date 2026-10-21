<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-[var(--mint)] hover:bg-[var(--mint-h)] border border-transparent rounded-[11px] font-bold text-xs text-[var(--on-mint)] uppercase tracking-wider shadow-[0_4px_14px_rgba(45,193,151,0.25)] focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:ring-offset-2 focus:ring-offset-[var(--bg)] transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
