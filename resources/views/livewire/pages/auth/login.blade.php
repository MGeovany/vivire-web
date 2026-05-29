<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('journal', absolute: false), navigate: true);
    }
}; ?>

<div>
    {{-- Tabs --}}
    <div class="flex mb-7 border-b border-border">
        <a href="{{ route('login') }}" wire:navigate class="text-[13.5px] text-fg pb-2 mr-[22px] border-b-[1.5px] border-fg -mb-px">Entrar</a>
        <a href="{{ route('register') }}" wire:navigate class="text-[13.5px] text-subtle pb-2 mr-[22px] border-b-[1.5px] border-transparent -mb-px transition-colors hover:text-fg">Crear cuenta</a>
    </div>

    @if (session('status'))
        <p class="text-[12.5px] text-success mb-3">{{ session('status') }}</p>
    @endif

    <form wire:submit="login" class="flex flex-col gap-3">
        <div>
            <input wire:model="form.email" type="email" name="email" required autofocus autocomplete="username"
                   placeholder="Email"
                   class="w-full px-[14px] py-[11px] text-sm bg-white border border-border rounded-lg outline-none transition focus:border-fg focus:ring-[3px] focus:ring-fg/[0.06] placeholder:text-muted">
            @error('form.email') <p class="text-[12.5px] text-error mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <input wire:model="form.password" type="password" name="password" required autocomplete="current-password"
                   placeholder="Contraseña"
                   class="w-full px-[14px] py-[11px] text-sm bg-white border border-border rounded-lg outline-none transition focus:border-fg focus:ring-[3px] focus:ring-fg/[0.06] placeholder:text-muted">
            @error('form.password') <p class="text-[12.5px] text-error mt-1">{{ $message }}</p> @enderror
        </div>

        <label class="inline-flex items-center gap-2 text-[12.5px] text-subtle select-none">
            <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-border text-fg focus:ring-0">
            Recordarme
        </label>

        <button type="submit" class="mt-1 px-[14px] py-[11px] text-sm font-medium text-bg bg-fg rounded-lg transition hover:opacity-[0.82] tracking-[0.01em]">
            Entrar
        </button>

        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" wire:navigate class="text-[12.5px] text-muted hover:text-subtle transition-colors text-center">¿Olvidaste tu contraseña?</a>
        @endif
    </form>
</div>
