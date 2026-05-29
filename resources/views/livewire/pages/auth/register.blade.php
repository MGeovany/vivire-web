<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('journal', absolute: false), navigate: true);
    }
}; ?>

<div>
    <x-ui.auth-tabs active="register" class="animate-fade-in" style="animation-delay: 100ms" />

    <form wire:submit="register" class="flex flex-col gap-1 mt-2 animate-fade-up" style="animation-delay: 140ms">
        <div>
            <x-ui.input wire:model="name" type="text" name="name" required autofocus autocomplete="name" placeholder="Tu nombre" />
            <x-ui.error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-ui.input wire:model="email" type="email" name="email" required autocomplete="username" placeholder="Email" />
            <x-ui.error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-ui.input wire:model="password" type="password" name="password" required autocomplete="new-password" placeholder="Contraseña" />
            <x-ui.error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-ui.input wire:model="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Confirma tu contraseña" />
            <x-ui.error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-ui.button class="mt-2">Crear cuenta</x-ui.button>
    </form>
</div>
