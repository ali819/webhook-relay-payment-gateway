<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FirstAdminRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_and_login_point_to_register_when_no_admin_exists(): void
    {
        $this->get('/')->assertRedirect(route('register'));
        $this->get(route('login'))->assertRedirect(route('register'));
        $this->get(route('register'))->assertOk()->assertSee('Buat akun admin');
    }

    public function test_first_admin_is_created_and_logged_in(): void
    {
        $this->post(route('register.post'), [
            'name'                  => 'Admin',
            'email'                 => 'admin@relay.test',
            'password'              => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('panel.domains.index'));

        $this->assertAuthenticated();
        $this->assertSame(1, User::count());
        $this->assertNotSame('rahasia123', User::first()->password); // ter-hash
    }

    public function test_register_closes_once_an_admin_exists(): void
    {
        User::factory()->create();

        $this->get(route('register'))->assertRedirect(route('login'));

        $this->post(route('register.post'), [
            'name'                  => 'Admin Kedua',
            'email'                 => 'kedua@relay.test',
            'password'              => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('login'));

        $this->assertSame(1, User::count());
        $this->assertGuest();
    }

    public function test_login_form_is_shown_when_admin_exists(): void
    {
        User::factory()->create();

        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk()->assertSee('Login UI');
    }

    public function test_weak_password_is_rejected(): void
    {
        $this->post(route('register.post'), [
            'name'                  => 'Admin',
            'email'                 => 'admin@relay.test',
            'password'              => 'abc',
            'password_confirmation' => 'abc',
        ])->assertSessionHasErrors('password');

        $this->assertSame(0, User::count());
    }
}
