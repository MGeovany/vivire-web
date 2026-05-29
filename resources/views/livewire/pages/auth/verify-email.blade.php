<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('journal', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <p class="text-[13px] text-subtle leading-relaxed mb-6">
        Gracias por registrarte. Confirma tu email con el enlace que te enviamos. Si no lo recibiste, podemos enviarte otro.
    </p>

    @if (session('status') == 'verification-link-sent')
        <p class="text-[12.5px] text-success mb-4 px-3 py-2 bg-success/10 rounded-lg">
            Te enviamos un nuevo enlace de verificación.
        </p>
    @endif

    <div class="flex flex-col gap-3">
        <x-ui.button wire:click="sendVerification">Reenviar email</x-ui.button>

        <button wire:click="logout" type="button" class="text-[12.5px] text-muted hover:text-subtle transition-colors text-center py-1">
            Cerrar sesión
        </button>
    </div>
</div>
