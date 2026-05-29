@props(['active' => 'login'])

<div {{ $attributes->merge(['class' => 'flex gap-6 mb-8']) }}>
  <a href="{{ route('login') }}" wire:navigate
     @class([
       'text-[14px] pb-1 transition-colors',
       'text-fg' => $active === 'login',
       'text-muted hover:text-subtle' => $active !== 'login',
     ])>
    Entrar
  </a>
  <a href="{{ route('register') }}" wire:navigate
     @class([
       'text-[14px] pb-1 transition-colors',
       'text-fg' => $active === 'register',
       'text-muted hover:text-subtle' => $active !== 'register',
     ])>
    Crear cuenta
  </a>
</div>
