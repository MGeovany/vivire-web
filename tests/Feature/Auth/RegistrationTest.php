<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('journal', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        Volt::test('pages.auth.register')
            ->set('name', 'First User')
            ->set('email', 'dupe@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertRedirect(route('journal', absolute: false));

        auth()->logout();

        Volt::test('pages.auth.register')
            ->set('name', 'Second User')
            ->set('email', 'dupe@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasErrors(['email' => 'unique']);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }
}
