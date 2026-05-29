<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  @php
    $name = config('seo.name');
    $description = config('seo.description');
    $url = rtrim(config('seo.url'), '/');
  @endphp

  <title>{{ $name }}</title>
  <meta name="description" content="{{ $description }}">
  <link rel="canonical" href="{{ $url }}/">

  <meta property="og:type" content="website">
  <meta property="og:title" content="{{ $name }}">
  <meta property="og:description" content="{{ $description }}">
  <meta property="og:url" content="{{ $url }}/">

  <meta name="twitter:card" content="summary">

  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📓</text></svg>">

  <!-- Fonts (same as app) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,400&family=Lora:ital,wght@0,400;0,500;1,400;1,500&display=swap">

  <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body class="font-sans bg-bg text-fg leading-relaxed min-h-screen overflow-x-hidden antialiased">
  <main class="mx-auto max-w-3xl px-5 py-16">
    <header class="space-y-4">
      <h1 class="font-serif italic text-5xl tracking-tight">{{ $name }}</h1>
      <p class="text-[15px] text-subtle leading-relaxed max-w-prose">{{ $description }}</p>
    </header>

    <div class="mt-10 flex items-center gap-3">
      <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full bg-fg text-bg px-5 py-2.5 text-sm tracking-wide hover:opacity-95">
        Entrar
      </a>
      <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full border border-border px-5 py-2.5 text-sm tracking-wide hover:bg-hover">
        Crear cuenta
      </a>
    </div>

    <section class="mt-14 grid gap-6 sm:grid-cols-2">
      <div class="rounded-2xl border border-border bg-white/50 backdrop-blur p-5">
        <div class="text-xs uppercase tracking-wider text-subtle">Focus</div>
        <div class="mt-2 text-sm leading-relaxed">Tres secciones por dia: feelings, thoughts, reflections.</div>
      </div>
      <div class="rounded-2xl border border-border bg-white/50 backdrop-blur p-5">
        <div class="text-xs uppercase tracking-wider text-subtle">Flow</div>
        <div class="mt-2 text-sm leading-relaxed">Auto-save, editor rapido y minimalista.</div>
      </div>
    </section>

    <footer class="mt-16 text-xs text-subtle">
      <a class="hover:text-fg" href="{{ $url }}/sitemap.xml">sitemap</a>
    </footer>
  </main>
</body>
</html>
