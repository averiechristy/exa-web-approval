<?php

namespace Database\Seeders;

use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Database\Seeder;

class WorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ==========================================
        // 1. WORKFLOW UNTUK: Exa Mitra Solusi (ID: 1)
        // ==========================================
        
        // Dokumen Pengajuan Anggaran (Budget Proposal)
        $budgetWorkflow = Workflow::create([
            'organization_id' => 1,
            'document_type'   => 'Budget Proposal',
        ]);

        // Langkah Persetujuan Berjenjang (Tiered Steps)
        // Aturan: Harus disetujui bertahap dari Tier 1 -> Tier 2 -> Tier 3
        WorkflowStep::create([
            'workflow_id'    => $budgetWorkflow->id,
            'tier'           => 1,
            'division_id'    => 4, // Division: Finance
            'sla_days'       => 2, // Harus diproses dalam 2 hari
            'min_role_level' => 3, // Minimal Role: Manager (Level 3)
        ]);

        WorkflowStep::create([
            'workflow_id'    => $budgetWorkflow->id,
            'tier'           => 2,
            'division_id'    => 1, // Division: Director
            'sla_days'       => 3,
            'min_role_level' => 4, // Minimal Role: Director (Level 4)
        ]);


        // ==========================================
        // 2. WORKFLOW UNTUK: Premier Deli (ID: 2)
        // ==========================================
        
        // Dokumen Pengadaan Barang (Purchase Requisition)
        $prWorkflow = Workflow::create([
            'organization_id' => 2,
            'document_type'   => 'Purchase Requisition',
        ]);

        WorkflowStep::create([
            'workflow_id'    => $prWorkflow->id,
            'tier'           => 1,
            'division_id'    => 5, // Division: IT (karena request barang IT)
            'sla_days'       => 1,
            'min_role_level' => 2, // Minimal Role: Supervisor (Level 2)
        ]);

        WorkflowStep::create([
            'workflow_id'    => $prWorkflow->id,
            'tier'           => 2,
            'division_id'    => 4, // Division: Finance
            'sla_days'       => 2,
            'min_role_level' => 3, // Minimal Role: Manager (Level 3)
        ]);
    }
}