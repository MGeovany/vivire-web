<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <p class="text-[13px] text-subtle leading-relaxed mb-6">
        ¿Olvidaste tu contraseña? Escribe tu email y te enviaremos un enlace para crear una nueva.
    </p>

    @if (session('status'))
        <p class="text-[12.5px] text-success mb-4 px-3 py-2 bg-success/10 rounded-lg">{{ session('status') }}</p>
    @endif

    <x-ui.auth-form action="sendPasswordResetLink" class="flex flex-col gap-3.5">
        <div>
            <x-ui.input wire:model="email" id="email" type="email" name="email" required autofocus placeholder="Email" />
            <x-ui.error :messages="$errors->get('email')" />
        </div>

        <x-ui.button>Enviar enlace</x-ui.button>

        <a href="{{ route('login') }}" wire:navigate class="text-[12.5px] text-muted hover:text-subtle transition-colors text-center pt-1">
            Volver a entrar
        </a>
    </x-ui.auth-form>
</div>
