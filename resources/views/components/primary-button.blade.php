<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-[#2DC197] hover:bg-[#4bd2aa] border border-transparent rounded-[11px] font-bold text-xs text-[#06302d] uppercase tracking-wider shadow-[0_4px_14px_rgba(45,193,151,0.25)] focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:ring-offset-2 focus:ring-offset-[#0a1514] transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
