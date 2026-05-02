<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('system_roles')->insert([
            [
                'system_role_name' => 'Superadmin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'system_role_name' => 'User',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}