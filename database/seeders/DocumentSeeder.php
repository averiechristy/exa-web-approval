<?php

namespace Database\Seeders;

use App\Models\ApprovalPosition;
use App\Models\DocumentApproval;
use App\Models\Documents;
use App\Models\DocumentShare;
use App\Models\Folder;
use App\Models\User;
use App\Models\Workflow;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil data master pendukung
        $workflows = Workflow::with('steps')->get();
        $folders = Folder::all();
        
        // Ambil user spesifik
        $budi = User::where('username', 'budi_exa')->first();       // Director Exa
        $siti = User::where('username', 'siti_exa')->first();       // Manager Finance Exa
        $andi = User::where('username', 'andi_exa')->first();       // Staff Finance Exa
        $rian = User::where('username', 'rian_exa')->first();       // Staff Finance Exa
        $michael = User::where('username', 'michael_deli')->first(); // Director Deli
        $david = User::where('username', 'david_deli')->first();     // Spv IT Deli
        $diana = User::where('username', 'diana_hr')->first();       // Manager HR di 2 Org
        
        $documentTemplates = [
            'Budget Proposal' => [
                'Pengajuan Anggaran Lisensi Software Q3',
                'Anggaran Pelatihan Karyawan Baru',
                'Biaya Renovasi Kantor Cabang Utama',
                'Pengajuan Budget Marketing Campaign Ramadhan',
                'Anggaran Pembelian Bahan Baku Kuliner Q4',
            ],
            'Purchase Requisition' => [
                'Pengadaan Laptop Developer IT',
                'Pembelian Server Lokal Cadangan',
                'Pengadaan Meja Kursi Ergonomis HR',
                'Pembelian Printer Barcode Gudang',
                'Pengadaan AC Ruangan Rapat Utama',
            ]
        ];

        // Loop 7 hari terakhir (dari 9 Juli hingga 15 Juli 2026)
        for ($i = 6; $i >= 0; $i--) {
            $currentDate = Carbon::now()->subDays($i);

            // Penentuan Status
            if ($i >= 5) {
                $docStatus = 'Approved';
            } elseif ($i >= 2) {
                $docStatus = 'In Progress';
            } else {
                $docStatus = 'Waiting Approval';
            }

            // ==========================================
            // 1. DOKUMEN: Exa Mitra Solusi (Budget Proposal)
            // ==========================================
            $workflowExa = $workflows->where('organization_id', 1)->first();
            $folderExa = $folders->where('organization_id', 1)->where('folder_name', 'Budget Proposals')->first() 
                         ?? $folders->where('organization_id', 1)->first();
            
            $requesterExa = ($i % 2 === 0) ? $andi : $rian;
            $docNameExa = $documentTemplates['Budget Proposal'][$i % count($documentTemplates['Budget Proposal'])] . " (" . $docStatus . " - " . $currentDate->format('d-M') . ")";

            $document1 = Documents::create([
                'organization_id'          => 1,
                'folder_id'                => $folderExa->id,
                'document_name'            => $docNameExa,
                'path'                     => 'documents/exa/' . md5($docNameExa) . '.pdf',
                'status'                   => $docStatus,
                'requester_id'             => $requesterExa->id,
                'requester_division_id'    => 4, 
                'workflow_id'              => $workflowExa->id,
                'current_tier'             => ($docStatus === 'Approved') ? 2 : (($docStatus === 'In Progress') ? 2 : 1),
                'email_subject'            => 'Permohonan Persetujuan: ' . $docNameExa,
                'email_message'            => 'Mohon tinjau dokumen terlampir.',
                'approval_summary_created' => true,
                'approval_start_y'         => 500,
                'created_at'               => $currentDate,
                'updated_at'               => $currentDate,
            ]);

            // Alur Approval Dokumen 1
            $stepsExa = $workflowExa->steps->sortBy('tier');
            foreach ($stepsExa as $step) {
                $approverId = ($step->division_id == 4) ? $siti->id : $budi->id;

                $appStatus = 'Waiting';
                $completedAt = null;

                if ($docStatus === 'Approved') {
                    $appStatus = 'Approved';
                    $completedAt = $currentDate->copy()->addHours($step->tier * 2);
                } elseif ($docStatus === 'In Progress') {
                    if ($step->tier === 1) {
                        $appStatus = 'Approved';
                        $completedAt = $currentDate->copy()->addHours(2);
                    } else {
                        $appStatus = 'Pending';
                    }
                } else {
                    if ($step->tier === 1) {
                        $appStatus = 'Pending';
                    } else {
                        $appStatus = 'Waiting';
                    }
                }

                $approval = DocumentApproval::create([
                    'document_id'      => $document1->id,
                    'division_id'      => $step->division_id,
                    'approver_id'      => $approverId,
                    'approver_order'   => $step->tier,
                    'show_on_doc'      => true,
                    'status'           => $appStatus,
                    'remarks'          => $appStatus === 'Approved' ? 'Approved via Seeder' : null,
                    'sla_days'         => $step->sla_days,
                    'started_at'       => ($step->tier === 1 || $appStatus === 'Pending') ? $currentDate : null,
                    'due_at'           => ($step->tier === 1 || $appStatus === 'Pending') ? $currentDate->copy()->addDays($step->sla_days) : null,
                    'completed_at'     => $completedAt,
                    'is_overdue'       => false,
                    'tier'             => $step->tier,
                    'workflow_step_id' => $step->id,
                    'is_requester'     => false,
                    'created_at'       => $currentDate,
                    'updated_at'       => $currentDate,
                ]);

                ApprovalPosition::create([
                    'document_approval_id' => $approval->id,
                    'page_number'          => 1,
                    'pos_x_percent'        => 15.5 + ($step->tier * 15),
                    'pos_y_percent'        => 80.0,
                    'mode'                 => 'standard', // Diubah ke salah satu opsi yang valid: standard, custom, atau fixed
                ]);
            }


            // ==========================================
            // 2. DOKUMEN: Premier Deli (Purchase Requisition)
            // ==========================================
            $workflowDeli = $workflows->where('organization_id', 2)->first();
            $folderDeli = $folders->where('organization_id', 2)->where('folder_name', 'Hardware Procurement')->first()
                          ?? $folders->where('organization_id', 2)->first();

            $docNameDeli = $documentTemplates['Purchase Requisition'][$i % count($documentTemplates['Purchase Requisition'])] . " (" . $docStatus . " - " . $currentDate->format('d-M') . ")";

            $document2 = Documents::create([
                'organization_id'          => 2,
                'folder_id'                => $folderDeli->id,
                'document_name'            => $docNameDeli,
                'path'                     => 'documents/deli/' . md5($docNameDeli) . '.pdf',
                'status'                   => $docStatus,
                'requester_id'             => $david->id,
                'requester_division_id'    => 5, 
                'workflow_id'              => $workflowDeli->id,
                'current_tier'             => ($docStatus === 'Approved') ? 2 : (($docStatus === 'In Progress') ? 2 : 1),
                'email_subject'            => 'Butuh Persetujuan Pengadaan: ' . $docNameDeli,
                'email_message'            => 'Mohon approval untuk pembelian aset IT.',
                'approval_summary_created' => true,
                'approval_start_y'         => 550,
                'created_at'               => $currentDate,
                'updated_at'               => $currentDate,
            ]);

            // Alur Approval Dokumen 2
            $stepsDeli = $workflowDeli->steps->sortBy('tier');
            foreach ($stepsDeli as $step) {
                $approverId = ($step->division_id == 5) ? $david->id : $diana->id;

                $appStatus = 'Waiting';
                $completedAt = null;

                if ($docStatus === 'Approved') {
                    $appStatus = 'Approved';
                    $completedAt = $currentDate->copy()->addHours($step->tier * 2);
                } elseif ($docStatus === 'In Progress') {
                    if ($step->tier === 1) {
                        $appStatus = 'Approved';
                        $completedAt = $currentDate->copy()->addHours(2);
                    } else {
                        $appStatus = 'Pending';
                    }
                } else {
                    if ($step->tier === 1) {
                        $appStatus = 'Pending';
                    } else {
                        $appStatus = 'Waiting';
                    }
                }

                $approval = DocumentApproval::create([
                    'document_id'      => $document2->id,
                    'division_id'      => $step->division_id,
                    'approver_id'      => $approverId,
                    'approver_order'   => $step->tier,
                    'show_on_doc'      => true,
                    'status'           => $appStatus,
                    'remarks'          => $appStatus === 'Approved' ? 'Approved via Seeder' : null,
                    'sla_days'         => $step->sla_days,
                    'started_at'       => ($step->tier === 1 || $appStatus === 'Pending') ? $currentDate : null,
                    'due_at'           => ($step->tier === 1 || $appStatus === 'Pending') ? $currentDate->copy()->addDays($step->sla_days) : null,
                    'completed_at'     => $completedAt,
                    'is_overdue'       => false,
                    'tier'             => $step->tier,
                    'workflow_step_id' => $step->id,
                    'is_requester'     => false,
                    'created_at'       => $currentDate,
                    'updated_at'       => $currentDate,
                ]);

                ApprovalPosition::create([
                    'document_approval_id' => $approval->id,
                    'page_number'          => 1,
                    'pos_x_percent'        => 20.0 + ($step->tier * 10),
                    'pos_y_percent'        => 75.0,
                    'mode'                 => 'standard', // Diubah ke salah satu opsi yang valid: standard, custom, atau fixed
                ]);
            }

            // Document Share log
            DocumentShare::create([
                'document_id' => $document2->id,
                'share_to'    => $michael->id,
                'share_by'    => $david->id,
                'created_at'  => $currentDate,
                'updated_at'  => $currentDate,
            ]);
        }
    }
}