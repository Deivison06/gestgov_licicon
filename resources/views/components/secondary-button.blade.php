<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-[#10201e] border border-[#24403c] rounded-[11px] font-semibold text-xs text-[#e6f0ee] uppercase tracking-wider shadow-sm hover:bg-[#142825] hover:border-[#5eead4] hover:text-white focus:outline-none focus:ring-2 focus:ring-[#5eead4] focus:ring-offset-2 focus:ring-offset-[#0a1514] disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
