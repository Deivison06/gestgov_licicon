<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-[var(--bad-bg)] hover:bg-[var(--bad-strong)] border border-[var(--bad)]/30 rounded-[11px] font-semibold text-xs text-[var(--bad)] hover:text-white uppercase tracking-wider shadow-sm focus:outline-none focus:ring-2 focus:ring-[var(--bad)] focus:ring-offset-2 focus:ring-offset-[var(--bg)] transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
