<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The app boots and sends guests to the login screen.
     */
    public function test_the_application_renders_the_landing_page_for_guests(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('vivire');
    }
}
