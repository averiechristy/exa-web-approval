<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserAccess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'system_role_id' => 1,
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'username' => 'superadmin',
            'password' => Hash::make('12345678'),
            'is_active' => true,
        ]);
    }
}