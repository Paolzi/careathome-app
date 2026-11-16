@props(['dark' => true])

<div {{ $attributes->merge(['class' => 'flex items-center gap-2.5 select-none shrink-0']) }}>
    <img src="{{ asset('images/careathome-logo.png') }}"
         alt="Logo CareAtHome"
         class="h-11 w-11 object-contain shrink-0 rounded-xl bg-[#FFFDF9] p-0.5 border border-[#78956A]/50 shadow-xs" />
    <div class="flex flex-col text-left leading-none">
        <span class="text-[20px] font-extrabold tracking-tight whitespace-nowrap {{ $dark ? 'text-[#46571A]' : 'text-[#F9F4EA]' }}">
            Care<span class="{{ $dark ? 'text-[#78956A]' : 'text-[#D1EB8F]' }}">AtHome</span>
        </span>
        <span class="text-[9px] font-bold tracking-wider uppercase whitespace-nowrap mt-0.5 {{ $dark ? 'text-[#78956A]' : 'text-[#D1EB8F]' }}">
            HomeCare Medical
        </span>
    </div>
</div>
