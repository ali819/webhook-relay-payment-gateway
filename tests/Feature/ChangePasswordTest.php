<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['password' => 'lama12345']);
    }

    public function test_account_page_requires_login(): void
    {
        $this->get(route('panel.account.edit'))->assertRedirect(route('login'));
    }

    public function test_password_can_be_changed(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->putJson(route('panel.account.password'), [
                'current_password'      => 'lama12345',
                'password'              => 'baru123456',
                'password_confirmation' => 'baru123456',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue(Hash::check('baru123456', $user->fresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->putJson(route('panel.account.password'), [
                'current_password'      => 'salah12345',
                'password'              => 'baru123456',
                'password_confirmation' => 'baru123456',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('current_password');

        $this->assertTrue(Hash::check('lama12345', $user->fresh()->password));
    }

    public function test_new_password_must_be_confirmed_strong_and_different(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->putJson(route('panel.account.password'), [
                'current_password'      => 'lama12345',
                'password'              => 'baru123456',
                'password_confirmation' => 'beda123456',
            ])->assertJsonValidationErrors('password');

        $this->actingAs($user)
            ->putJson(route('panel.account.password'), [
                'current_password'      => 'lama12345',
                'password'              => 'abc',
                'password_confirmation' => 'abc',
            ])->assertJsonValidationErrors('password');

        $this->actingAs($user)
            ->putJson(route('panel.account.password'), [
                'current_password'      => 'lama12345',
                'password'              => 'lama12345',
                'password_confirmation' => 'lama12345',
            ])->assertJsonValidationErrors('password');

        $this->assertTrue(Hash::check('lama12345', $user->fresh()->password));
    }

    public function test_login_still_works_with_the_new_password(): void
    {
        $user = $this->admin();

        $this->actingAs($user)->putJson(route('panel.account.password'), [
            'current_password'      => 'lama12345',
            'password'              => 'baru123456',
            'password_confirmation' => 'baru123456',
        ])->assertOk();

        auth()->logout();

        $this->post(route('login.post'), [
            'email'    => $user->email,
            'password' => 'baru123456',
        ])->assertRedirect(route('panel.domains.index'));

        $this->assertAuthenticatedAs($user);
    }
}
