@props(['action'])

<form
    wire:submit.prevent="{{ $action }}"
    x-on:keydown.enter.capture.prevent="$wire.{{ $action }}()"
    {{ $attributes }}
>
    {{ $slot }}
</form>
