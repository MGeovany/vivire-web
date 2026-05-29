<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'vivire') }}</title>

        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📓</text></svg>">

        <!-- Fonts (same as legacy) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,400&family=Lora:ital,wght@0,400;0,500;1,400;1,500&display=swap">

        <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        @livewireStyles
    </head>
    <body class="font-sans bg-bg text-fg leading-relaxed min-h-screen overflow-x-hidden antialiased">
        <div class="min-h-screen">
            <livewire:layout.navigation />

            @if (isset($header))
                <header class="border-b border-[var(--color-border)] bg-[var(--color-bg)]">
                    <div class="mx-auto max-w-5xl px-4 sm:px-6 py-6">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="mx-auto max-w-5xl px-4 sm:px-6 py-8">
                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
