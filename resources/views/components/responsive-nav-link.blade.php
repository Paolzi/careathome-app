@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-[#78956A] text-start text-base font-medium text-[#46571A] bg-[#D1EB8F]/45 focus:outline-none focus:text-[#46571A] focus:bg-[#D1EB8F] focus:border-[#78956A] transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-[#46571A]/85 hover:text-[#46571A] hover:bg-[#F9F4EA] hover:border-[#78956A] focus:outline-none focus:text-[#46571A] focus:bg-[#F9F4EA] focus:border-[#78956A] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
