<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_can_be_created()
    {
        $admin = AdminUser::create([
            'username' => 'admin_test',
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => 'password123'
        ]);

        $this->assertInstanceOf(AdminUser::class, $admin);
        $this->assertEquals('admin_test', $admin->username);
        $this->assertEquals('Test Admin', $admin->name);
        $this->assertEquals('admin@test.com', $admin->email);
        $this->assertTrue(Hash::check('password123', $admin->password));
    }

    public function test_admin_user_password_is_hashed()
    {
        $admin = new AdminUser();
        $admin->password = 'plain_password';

        $this->assertTrue(Hash::check('plain_password', $admin->password));
        $this->assertNotEquals('plain_password', $admin->password);
    }

    public function test_admin_user_fillable_attributes()
    {
        $fillable = ['username', 'name', 'email', 'password'];
        $admin = new AdminUser();

        $this->assertEquals($fillable, $admin->getFillable());
    }

    public function test_admin_user_hidden_attributes()
    {
        $hidden = ['password', 'remember_token'];
        $admin = new AdminUser();

        $this->assertEquals($hidden, $admin->getHidden());
    }
}
