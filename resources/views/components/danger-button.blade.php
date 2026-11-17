<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#FF9ABC] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#FF9ABC] active:bg-[#FF9ABC] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
