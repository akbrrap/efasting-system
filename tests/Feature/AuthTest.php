<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat user dummy
        User::create([
            'username' => 'admin_test',
            'password' => 'password123',
            'nama_karyawan' => 'Admin Tester',
            'jenis_user' => 'ADMINISTRATOR',
        ]);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Silakan Login');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin_test',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin_test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::where('username', 'admin_test')->first();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
