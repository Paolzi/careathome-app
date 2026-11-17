@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-[#78956A] focus:border-[#78956A] focus:ring-[#78956A] rounded-md shadow-sm']) }}>
