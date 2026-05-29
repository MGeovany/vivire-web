<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('journal', absolute: false), navigate: true);
    }
}; ?>

<div>
    <p class="text-[13px] text-subtle leading-relaxed mb-6">
        Esta es un área segura. Confirma tu contraseña para continuar.
    </p>

    <form wire:submit="confirmPassword" class="flex flex-col gap-3.5">
        <div>
            <x-ui.input wire:model="password" id="password" type="password" name="password" required autocomplete="current-password" placeholder="Contraseña" />
            <x-ui.error :messages="$errors->get('password')" />
        </div>

        <x-ui.button>Confirmar</x-ui.button>
    </form>
</div>
