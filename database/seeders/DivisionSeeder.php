<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            ['division_name' => 'Director'],
            ['division_name' => 'Human Resource'],
            ['division_name' => 'Business Unit'],
            ['division_name' => 'Finance'],
            ['division_name' => 'IT'],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }
    }
}