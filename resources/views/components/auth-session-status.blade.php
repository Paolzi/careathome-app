@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-[#78956A]']) }}>
        {{ $status }}
    </div>
@endif
