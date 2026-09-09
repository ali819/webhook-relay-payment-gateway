<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Root selalu mengarahkan tamu ke halaman auth (register saat instalasi
     * masih kosong, login setelah ada admin) — lihat FirstAdminRegistrationTest.
     */
    public function test_the_application_redirects_guests_to_auth(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('register'));
    }
}
