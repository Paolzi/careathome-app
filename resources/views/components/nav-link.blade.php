@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-[#78956A] text-sm font-medium leading-5 text-[#46571A] focus:outline-none focus:border-[#78956A] transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-[#78956A] hover:text-[#46571A] hover:border-[#78956A] focus:outline-none focus:text-[#46571A] focus:border-[#78956A] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
