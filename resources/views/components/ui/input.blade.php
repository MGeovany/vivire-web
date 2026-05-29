@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'vivire-input']) }}>
