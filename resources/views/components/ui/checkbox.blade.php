@props([
    'label' => null,
])

@php
    $inputId = $attributes->get('id');
@endphp

<label
    @if ($inputId) for="{{ $inputId }}" @endif
    {{ $attributes->only('class')->merge(['class' => 'inline-flex items-center gap-3 cursor-pointer select-none group py-3']) }}
>
    <span class="relative flex h-[18px] w-[18px] shrink-0 items-center justify-center">
        <input
            type="checkbox"
            {{ $attributes->except('class')->merge(['class' => 'peer absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0']) }}
        >
        <span
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 z-0 rounded-[5px] border border-border bg-bg transition-all duration-200
                   group-hover:border-subtle
                   peer-focus-visible:ring-2 peer-focus-visible:ring-fg/10 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-bg
                   peer-checked:border-accent peer-checked:bg-accent
                   peer-disabled:opacity-40 peer-disabled:cursor-not-allowed"
        ></span>
        <svg
            aria-hidden="true"
            viewBox="0 0 12 12"
            fill="none"
            class="pointer-events-none absolute inset-0 z-0 m-auto h-2.5 w-2.5 text-bg opacity-0 transition-opacity duration-150 peer-checked:opacity-100 checkbox-check"
        >
            <path d="M2 6.2 4.8 9 10 3" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </span>

    @if ($label)
        <span class="text-[13px] text-muted transition-colors duration-150 group-hover:text-subtle">{{ $label }}</span>
    @elseif ($slot->isNotEmpty())
        <span class="text-[13px] text-muted transition-colors duration-150 group-hover:text-subtle">{{ $slot }}</span>
    @endif
</label>
