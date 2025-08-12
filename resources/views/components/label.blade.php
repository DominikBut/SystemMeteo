@props(['value'])

<label {{ $attributes->merge(['class' => 'block  text-sm text-blue-500 font-bold']) }}>
    {{ $value ?? $slot }}
</label>
