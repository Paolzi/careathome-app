<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#78956A] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#D1EB8F]/50 focus:bg-[#FF9ABC] active:bg-[#78956A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
