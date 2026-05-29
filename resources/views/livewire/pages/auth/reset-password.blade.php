<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div>
    <p class="text-[13px] text-subtle leading-relaxed mb-6">
        Elige una contraseña nueva para tu cuenta.
    </p>

    <x-ui.auth-form action="resetPassword" class="flex flex-col gap-3.5">
        <div>
            <x-ui.input wire:model="email" id="email" type="email" name="email" required autofocus autocomplete="username" placeholder="Email" />
            <x-ui.error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-ui.input wire:model="password" id="password" type="password" name="password" required autocomplete="new-password" placeholder="Nueva contraseña" />
            <x-ui.error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-ui.input wire:model="password_confirmation" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Confirma la contraseña" />
            <x-ui.error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-ui.button>Restablecer contraseña</x-ui.button>
    </x-ui.auth-form>
</div>
