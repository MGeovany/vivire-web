@props(['disabled' => false])

@php($isPassword = $attributes->get('type') === 'password')

@if ($isPassword)
    <div class="password-field" x-data="{ shown: false }">
        <input
            @disabled($disabled)
            {{ $attributes->except('type')->merge(['class' => 'vivire-input']) }}
            x-bind:type="shown ? 'text' : 'password'"
        >
        <button
            type="button"
            class="password-toggle"
            tabindex="-1"
            @click.prevent="shown = !shown"
            x-bind:aria-label="shown ? 'Ocultar contraseña' : 'Mostrar contraseña'"
        >
            <svg x-show="!shown" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/>
                <circle cx="12" cy="12" r="2.5"/>
            </svg>
            <svg x-show="shown" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 3l18 18"/>
                <path d="M10.6 10.6a2.5 2.5 0 0 0 3.5 3.5"/>
                <path d="M6.7 6.7C4.6 8.3 3 10.5 2 12c0 0 3.5 6 10 6 1.8 0 3.4-.4 4.8-1"/>
                <path d="M14.1 9.9C14.6 10.4 15 11.2 15 12c0 1.7-1.3 3-3 3-.8 0-1.6-.4-2.1-.9"/>
            </svg>
        </button>
    </div>
@else
    <input @disabled($disabled) {{ $attributes->merge(['class' => 'vivire-input']) }}>
@endif
