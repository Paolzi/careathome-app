<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white border border-[#78956A] rounded-md font-semibold text-xs text-[#46571A] uppercase tracking-widest shadow-sm hover:bg-[#F9F4EA] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
