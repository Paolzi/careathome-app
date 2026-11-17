@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-[#46571A]']) }}>
    {{ $value ?? $slot }}
</label>
