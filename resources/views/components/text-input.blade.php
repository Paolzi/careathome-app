@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-white border-[#46571A]/45 text-[#46571A] placeholder:text-[#78956A] focus:border-[#46571A] focus:ring-[#46571A] rounded-md shadow-xs']) }}>
