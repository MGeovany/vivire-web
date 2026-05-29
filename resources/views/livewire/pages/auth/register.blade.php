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

    /**
     * Handle an incoming registration request.
     */
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
    {{-- Tabs --}}
    <div class="flex mb-7 border-b border-border">
        <a href="{{ route('login') }}" wire:navigate class="text-[13.5px] text-subtle pb-2 mr-[22px] border-b-[1.5px] border-transparent -mb-px transition-colors hover:text-fg">Entrar</a>
        <a href="{{ route('register') }}" wire:navigate class="text-[13.5px] text-fg pb-2 mr-[22px] border-b-[1.5px] border-fg -mb-px">Crear cuenta</a>
    </div>

    <form wire:submit="register" class="flex flex-col gap-3">
        <div>
            <input wire:model="name" type="text" name="name" required autofocus autocomplete="name"
                   placeholder="Tu nombre"
                   class="w-full px-[14px] py-[11px] text-sm bg-white border border-border rounded-lg outline-none transition focus:border-fg focus:ring-[3px] focus:ring-fg/[0.06] placeholder:text-muted">
            @error('name') <p class="text-[12.5px] text-error mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <input wire:model="email" type="email" name="email" required autocomplete="username"
                   placeholder="Email"
                   class="w-full px-[14px] py-[11px] text-sm bg-white border border-border rounded-lg outline-none transition focus:border-fg focus:ring-[3px] focus:ring-fg/[0.06] placeholder:text-muted">
            @error('email') <p class="text-[12.5px] text-error mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <input wire:model="password" type="password" name="password" required autocomplete="new-password"
                   placeholder="Contraseña"
                   class="w-full px-[14px] py-[11px] text-sm bg-white border border-border rounded-lg outline-none transition focus:border-fg focus:ring-[3px] focus:ring-fg/[0.06] placeholder:text-muted">
            @error('password') <p class="text-[12.5px] text-error mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <input wire:model="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   placeholder="Confirma tu contraseña"
                   class="w-full px-[14px] py-[11px] text-sm bg-white border border-border rounded-lg outline-none transition focus:border-fg focus:ring-[3px] focus:ring-fg/[0.06] placeholder:text-muted">
            @error('password_confirmation') <p class="text-[12.5px] text-error mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="mt-1 px-[14px] py-[11px] text-sm font-medium text-bg bg-fg rounded-lg transition hover:opacity-[0.82] tracking-[0.01em]">
            Crear cuenta
        </button>
    </form>
</div>
