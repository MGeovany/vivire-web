<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        try {
            $this->form->authenticate();
        } catch (ValidationException $e) {
            if ($e->validator->errors()->has('login')) {
                $this->dispatch(
                    'vivire-toast',
                    message: $e->validator->errors()->first('login'),
                    type: 'error',
                );

                return;
            }

            throw $e;
        }

        Session::regenerate();

        $this->redirectIntended(default: route('journal', absolute: false), navigate: true);
    }
}; ?>

<div>
    <x-ui.auth-tabs active="login" class="animate-fade-in" style="animation-delay: 100ms" />

    @if (session('status'))
        <p class="text-[12.5px] text-success mb-4 px-3 py-2 bg-success/10 rounded-lg animate-fade-in">{{ session('status') }}</p>
    @endif

    <form wire:submit="login" class="flex flex-col gap-1 mt-2 animate-fade-up" style="animation-delay: 140ms">
        <div>
            <x-ui.input wire:model="form.email" type="email" name="email" required autofocus autocomplete="username" placeholder="Email" />
            <x-ui.error :messages="$errors->get('form.email')" />
        </div>

        <div>
            <x-ui.input wire:model="form.password" type="password" name="password" required autocomplete="current-password" placeholder="Contraseña" />
            <x-ui.error :messages="$errors->get('form.password')" />
        </div>

        <x-ui.checkbox wire:model="form.remember" id="remember" label="Recordarme" />

        <x-ui.button class="mt-2">Entrar</x-ui.button>

        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" wire:navigate class="text-[13px] text-muted hover:text-subtle transition-colors text-center pt-4">
                ¿Olvidaste tu contraseña?
            </a>
        @endif
    </form>
</div>
