<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SystemRoleSeeder::class,
            OrganizationSeeder::class,
            DivisionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            WorkflowSeeder::class, // Ditambahkan di sini
            FolderSeeder::class,
            DocumentSeeder::class
        ]);
    }
}