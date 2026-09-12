<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Iniciar sesion')
            ->assertDontSee('admin@rmgs.test');
    }

    public function test_seeded_user_can_login_and_logout(): void
    {
        $this->seed();

        $this->post(route('login.store'), [
            'email' => 'admin@rmgs.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs(User::where('email', 'admin@rmgs.test')->first());

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
