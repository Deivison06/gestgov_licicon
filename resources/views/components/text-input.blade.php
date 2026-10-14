@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-[#0d1b19] border-[#24403c] text-[#e6f0ee] placeholder-[#8aa29d] focus:border-[#2DC197] focus:ring-[#2DC197]/25 rounded-xl shadow-sm text-sm transition']) }}>
