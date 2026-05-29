<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'vivire' }}</title>

  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📓</text></svg>">

  {{-- Same fonts as before: DM Serif Display (display), DM Sans (UI), Lora (writing) --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,400&family=Lora:ital,wght@0,400;0,500;1,400;1,500&display=swap">

  <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">

  @livewireStyles
</head>
<body class="font-sans bg-bg text-fg leading-relaxed min-h-screen overflow-x-hidden antialiased">

  {{ $slot }}

  <div class="toast fixed bottom-8 left-1/2 -translate-x-1/2 translate-y-3 bg-fg text-bg text-[13px] px-5 py-[9px] rounded-3xl opacity-0 transition-[opacity,transform] duration-200 pointer-events-none z-[1000] whitespace-nowrap tracking-[0.01em]" id="app-toast"></div>

  @livewireScripts
  <script src="{{ asset('js/spell-check.js') }}"></script>
  <script src="{{ asset('js/editor.js') }}"></script>
</body>
</html>
