@props(['dark' => false])

<div {{ $attributes->merge(['class' => 'flex items-center gap-2.5 font-bold tracking-tight select-none']) }}>
    <div class="relative flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 via-emerald-500 to-teal-400 text-white shadow-md shadow-emerald-500/25 border border-emerald-400/30">
        <!-- Medical Cross with subtle pulse -->
        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
            <path d="M19 10.5h-5.5V5c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v5.5H5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5h5.5V19c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5v-5.5H19c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5z"/>
        </svg>
        <span class="absolute -top-0.5 -right-0.5 flex h-2.5 w-2.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-300 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
        </span>
    </div>
    <div class="flex flex-col text-left">
        <span class="text-xl font-extrabold tracking-tight {{ $dark ? 'text-slate-900' : 'text-white' }} flex items-center gap-1">
            Care<span class="text-emerald-500">AtHome</span>
        </span>
        <span class="text-[10px] font-semibold tracking-wider uppercase {{ $dark ? 'text-emerald-700 font-bold' : 'text-emerald-200/80' }} -mt-1">
            HomeCare Medical
        </span>
    </div>
</div>
