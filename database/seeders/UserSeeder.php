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
        // 1. Super Admin (Menggunakan Eloquent agar lebih konsisten)
        User::create([
            'system_role_id' => 1,
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'username' => 'superadmin',
            'password' => Hash::make('12345678'),
            'is_active' => true,
        ]);

        // ==========================================
        // ORGANISASI 1: Exa Mitra Solusi (ID: 1)
        // ==========================================

        // Director - Exa Mitra Solusi
        $directorExa = User::create([
            'system_role_id' => 2, // Asumsi system_role_id 2 adalah user biasa
            'name' => 'Budi Santoso',
            'email' => 'budi.exa@gmail.com',
            'username' => 'budi_exa',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        UserAccess::create([
            'user_id' => $directorExa->id,
            'organization_id' => 1,
            'division_id' => 1, // Director
            'role_id' => 4, // Director
            'manager_id' => null,
        ]);

        // Manager Finance - Exa Mitra Solusi
        $managerFinanceExa = User::create([
            'system_role_id' => 2,
            'name' => 'Siti Aminah',
            'email' => 'siti.exa@gmail.com',
            'username' => 'siti_exa',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        UserAccess::create([
            'user_id' => $managerFinanceExa->id,
            'organization_id' => 1,
            'division_id' => 4, // Finance
            'role_id' => 3, // Manager
            'manager_id' => $directorExa->id,
        ]);

        // Staff Finance 1 - Exa Mitra Solusi
        $staffFinance1Exa = User::create([
            'system_role_id' => 2,
            'name' => 'Andi Wijaya',
            'email' => 'andi.exa@gmail.com',
            'username' => 'andi_exa',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        UserAccess::create([
            'user_id' => $staffFinance1Exa->id,
            'organization_id' => 1,
            'division_id' => 4, // Finance
            'role_id' => 1, // Staff
            'manager_id' => $managerFinanceExa->id,
        ]);

        // Staff Finance 2 - Exa Mitra Solusi (Menunjukkan >1 user di divisi/role yang sama)
        $staffFinance2Exa = User::create([
            'system_role_id' => 2,
            'name' => 'Rian Hidayat',
            'email' => 'rian.exa@gmail.com',
            'username' => 'rian_exa',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        UserAccess::create([
            'user_id' => $staffFinance2Exa->id,
            'organization_id' => 1,
            'division_id' => 4, // Finance
            'role_id' => 1, // Staff
            'manager_id' => $managerFinanceExa->id,
        ]);


        // ==========================================
        // ORGANISASI 2: Premier Deli (ID: 2)
        // ==========================================

        // Director - Premier Deli
        $directorDeli = User::create([
            'system_role_id' => 2,
            'name' => 'Michael Corleone',
            'email' => 'michael.deli@gmail.com',
            'username' => 'michael_deli',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        UserAccess::create([
            'user_id' => $directorDeli->id,
            'organization_id' => 2,
            'division_id' => 1, // Director
            'role_id' => 4, // Director
            'manager_id' => null,
        ]);

        // Supervisor IT - Premier Deli
        $spvItDeli = User::create([
            'system_role_id' => 2,
            'name' => 'David Beckham',
            'email' => 'david.deli@gmail.com',
            'username' => 'david_deli',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        UserAccess::create([
            'user_id' => $spvItDeli->id,
            'organization_id' => 2,
            'division_id' => 5, // IT
            'role_id' => 2, // Supervisor
            'manager_id' => $directorDeli->id,
        ]);


        // ==========================================
        // SKENARIO KHUSUS: 1 User di 2 Organisasi
        // ==========================================
        // User bernama "Diana Putri" bekerja sebagai Manager HR di Exa Mitra Solusi (Org 1)
        // Sekaligus menjadi Manager HR di Premier Deli (Org 2)
        
        $dianaDoubleAgent = User::create([
            'system_role_id' => 2,
            'name' => 'Diana Putri',
            'email' => 'diana.hr@gmail.com',
            'username' => 'diana_hr',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // Akses 1: Exa Mitra Solusi (Org 1) -> HR (Div 2) -> Manager (Role 3)
        UserAccess::create([
            'user_id' => $dianaDoubleAgent->id,
            'organization_id' => 1,
            'division_id' => 2,
            'role_id' => 3,
            'manager_id' => $directorExa->id,
        ]);

        // Akses 2: Premier Deli (Org 2) -> HR (Div 2) -> Manager (Role 3)
        UserAccess::create([
            'user_id' => $dianaDoubleAgent->id,
            'organization_id' => 2,
            'division_id' => 2,
            'role_id' => 3,
            'manager_id' => $directorDeli->id,
        ]);
    }
}