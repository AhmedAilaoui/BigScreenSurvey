<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        AdminUser::create([
            'username' => 'Ahmed Ezzine',
            'name' => 'Ailaoui',
            'email' => 'ahmedailaoui@bigscreen.com',
            'password' => 'password123',
        ]);

        AdminUser::create([
            'username' => 'Hichem',
            'name' => 'Lassoued',
            'email' => 'hichemlassoued@bigscreen.com',
            'password' => 'password1234',
        ]);
    }
}