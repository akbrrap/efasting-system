<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BladeViewTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'username' => 'admin_blade',
            'password' => 'admin123',
            'nama_karyawan' => 'Admin Blade Test',
            'jenis_user' => 'ADMINISTRATOR',
        ]);
    }

    public function test_all_views_can_be_rendered(): void
    {
        $routes = [
            'dashboard',
            'opname.internal',
            'opname.external',
            'asset.index',
            'asset.create',
            'asset.adjustment',
            'asset.retirement',
            'audit.index',
            'reports.index',
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAs($this->admin)->get(route($routeName));
            $response->assertStatus(200);
            $response->assertSee('eFasting');
        }
    }
}
