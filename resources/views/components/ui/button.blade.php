@props(['variant' => 'primary'])

@php
  $classes = match ($variant) {
    'ghost' => 'vivire-btn-ghost',
    default => 'vivire-btn w-full mt-1',
  };
@endphp

<button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>
  {{ $slot }}
</button>
