<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @php
    $name = config('seo.name');
    $description = $description ?? config('seo.description');
    $url = rtrim(config('seo.url'), '/');
    $title = $title ?? $name;
  @endphp

  <title>{{ $title }}</title>
  <meta name="description" content="{{ $description }}">
  <link rel="canonical" href="{{ request()->url() }}">

  <meta property="og:type" content="website">
  <meta property="og:title" content="{{ $title }}">
  <meta property="og:description" content="{{ $description }}">
  <meta property="og:url" content="{{ request()->url() }}">

  <meta name="twitter:card" content="summary">

  @if (auth()->check())
    <meta name="robots" content="noindex, nofollow">
  @endif

  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📓</text></svg>">

  <x-ui.fonts />

  <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">

  @livewireStyles
</head>
<body class="font-sans bg-bg text-fg leading-relaxed min-h-screen overflow-x-hidden antialiased">

  {{ $slot }}

  @livewireScripts
  <link rel="stylesheet" href="{{ asset('css/sonner.css') }}">
  <script src="{{ asset('js/toast.js') }}"></script>
  @include('partials.toast')
  <script src="{{ asset('js/typing-sound.js') }}"></script>
  <script src="{{ asset('js/spell-check.js') }}"></script>
  <script src="{{ asset('js/editor.js') }}"></script>
</body>
</html>
