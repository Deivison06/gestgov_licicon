<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-[#3b1515] hover:bg-[#991b1b] border border-[#fca5a5]/30 rounded-[11px] font-semibold text-xs text-[#fca5a5] hover:text-white uppercase tracking-wider shadow-sm focus:outline-none focus:ring-2 focus:ring-[#fca5a5] focus:ring-offset-2 focus:ring-offset-[#0a1514] transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
