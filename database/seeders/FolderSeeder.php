<?php

namespace Database\Seeders;

use App\Models\Folder;
use Illuminate\Database\Seeder;

class FolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ==========================================
        // 1. FOLDER UNTUK: Exa Mitra Solusi (ID: 1)
        // ==========================================

        // Folder Utama (Root)
        $financeRootExa = Folder::create([
            'organization_id' => 1,
            'parent_id'       => null,
            'folder_name'     => 'Finance & Accounting',
        ]);

        $hrRootExa = Folder::create([
            'organization_id' => 1,
            'parent_id'       => null,
            'folder_name'     => 'Human Resource Department',
        ]);

        // Sub-Folder di dalam "Finance & Accounting" (parent_id mengarah ke $financeRootExa)
        Folder::create([
            'organization_id' => 1,
            'parent_id'       => $financeRootExa->id,
            'folder_name'     => 'Budget Proposals',
        ]);

        Folder::create([
            'organization_id' => 1,
            'parent_id'       => $financeRootExa->id,
            'folder_name'     => 'Tax Reports',
        ]);

        // Sub-Folder di dalam "Human Resource Department"
        Folder::create([
            'organization_id' => 1,
            'parent_id'       => $hrRootExa->id,
            'folder_name'     => 'Employee Contracts',
        ]);


        // ==========================================
        // 2. FOLDER UNTUK: Premier Deli (ID: 2)
        // ==========================================

        // Folder Utama (Root)
        $itRootDeli = Folder::create([
            'organization_id' => 2,
            'parent_id'       => null,
            'folder_name'     => 'IT Operations',
        ]);

        // Sub-Folder di dalam "IT Operations"
        $procurementItDeli = Folder::create([
            'organization_id' => 2,
            'parent_id'       => $itRootDeli->id,
            'folder_name'     => 'Hardware Procurement',
        ]);

        // Sub-Sub-Folder (Skenario bersarang lebih dalam: IT Operations / Hardware Procurement / Invoices)
        Folder::create([
            'organization_id' => 2,
            'parent_id'       => $procurementItDeli->id,
            'folder_name'     => 'Invoices 2026',
        ]);
    }
}