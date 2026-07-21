<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'role_level' => 1,
                'role_name'  => 'staff',
            ],
            [
                'role_level' => 2,
                'role_name'  => 'supervisor',
            ],
            [
                'role_level' => 3,
                'role_name'  => 'manager',
            ],
            [
                'role_level' => 4,
                'role_name'  => 'director',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}