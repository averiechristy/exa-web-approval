<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = [
            ['organization_name' => 'Exa Mitra Solusi'],
            ['organization_name' => 'Premier Deli'], // Diperbaiki dari "Premier Deli\"
        ];

        foreach ($organizations as $org) {
            Organization::create($org);
        }
    }
}