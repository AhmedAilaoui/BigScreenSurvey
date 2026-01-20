<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class AdminUserApiTest extends TestCase
{
    use RefreshDatabase;

        public function test_admin_can_login()
    {
        $admin = AdminUser::create([
            'username' => 'admin_test',
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => 'password123'
        ]);

        $loginData = [
            'username' => 'admin_test',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/admin/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'admin' => ['id', 'username', 'name', 'email'],
                    'token'
                ]
            ]);
    }

    public function test_admin_login_fails_with_wrong_credentials()
    {
        $admin = AdminUser::create([
            'username' => 'admin_test',
            'password' => 'password123'
        ]);

        $loginData = [
            'username' => 'admin_test',
            'password' => 'wrong_password'
        ];

        $response = $this->postJson('/api/admin/login', $loginData);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Identifiants incorrects.'
            ]);
    }

    public function test_admin_can_access_profile_when_authenticated()
    {
        $admin = AdminUser::create([
            'username' => 'admin_test',
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => 'password123'
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/profile');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $admin->id,
                    'username' => 'admin_test',
                    'name' => 'Test Admin',
                    'email' => 'admin@test.com'
                ]
            ]);
    }

    public function test_admin_cannot_access_profile_when_not_authenticated()
    {
        $response = $this->getJson('/api/admin/profile');

        $response->assertStatus(401);
    }

    public function test_admin_can_logout()
    {
        $admin = AdminUser::create([
            'username' => 'admin_test',
            'password' => 'password123'
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Déconnexion réussie.'
            ]);
    }
}
