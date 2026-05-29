<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? config('app.name', 'vivire') }}</title>

  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📓</text></svg>">

  <x-ui.fonts />

  <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">

  @livewireStyles
</head>
<body class="font-sans bg-bg text-fg antialiased min-h-screen flex items-center justify-center px-6 py-12 max-sm:px-5">

  <div class="w-full max-w-[340px] animate-fade-up">
    <header class="mb-10 animate-fade-in" style="animation-delay: 60ms">
      <a href="/" wire:navigate class="inline-block">
        <span class="font-write text-[26px] text-fg tracking-[-0.03em] leading-none">vivire</span>
      </a>
    </header>

    {{ $slot }}
  </div>

  @livewireScripts
  @include('partials.toast')
</body>
</html>
