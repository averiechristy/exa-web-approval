<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'system_role_id' => 1,
                'name' => 'Super Admin',
                'email' => 'superadmin@gmail.com',
                'username' => 'superadmin',
                'password' => Hash::make('12345678'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}