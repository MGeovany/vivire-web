<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
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
        try {
            $validated = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            ], [
                'email.unique' => __('validation.unique'),
            ]);
        } catch (ValidationException $e) {
            if ($e->validator->errors()->has('email')) {
                $this->dispatch(
                    'vivire-toast',
                    message: $e->validator->errors()->first('email'),
                    type: 'error',
                );
            }

            throw $e;
        }

        try {
            event(new Registered($user = User::create($validated)));
        } catch (QueryException $e) {
            if ($this->isDuplicateEmail($e)) {
                $message = __('validation.unique');

                $this->dispatch('vivire-toast', message: $message, type: 'error');
                $this->addError('email', $message);

                return;
            }

            if ($this->isDatabaseUnavailable($e)) {
                $this->dispatch(
                    'vivire-toast',
                    message: __('auth.database_unavailable'),
                    type: 'error',
                );

                return;
            }

            throw $e;
        }

        Auth::login($user);

        $this->redirect(route('journal', absolute: false), navigate: true);
    }

    private function isDuplicateEmail(QueryException $e): bool
    {
        if (($e->errorInfo[0] ?? '') === '23505') {
            return str_contains(strtolower($e->getMessage()), 'email');
        }

        $message = strtolower($e->getMessage());

        return str_contains($message, 'unique')
            && str_contains($message, 'email');
    }

    private function isDatabaseUnavailable(QueryException $e): bool
    {
        $message = strtolower($e->getMessage());

        return in_array($e->errorInfo[0] ?? '', ['08006', '08001', '57P01', '53300'], true)
            || str_contains($message, 'connection')
            || str_contains($message, 'password supplied')
            || str_contains($message, 'does not exist');
    }
}; ?>

<div>
    <x-ui.auth-tabs active="register" class="animate-fade-in" style="animation-delay: 100ms" />

    <x-ui.auth-form action="register" class="flex flex-col gap-1 mt-2 animate-fade-up" style="animation-delay: 140ms">
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
    </x-ui.auth-form>
</div>
