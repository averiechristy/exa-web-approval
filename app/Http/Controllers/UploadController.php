<?php

namespace App\Http\Controllers;

use App\Jobs\LogActivityJob;
use App\Mail\DocumentApprovalMail;
use App\Models\ApprovalPosition;
use App\Models\Division;
use App\Models\DocumentApproval;
use App\Models\Documents;
use App\Models\DocumentShare;
use App\Models\Folder;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAccess;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use setasign\Fpdi\Tcpdf\Fpdi;
use Storage;

class UploadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $isSuperAdmin = $user->system_role_id == 1;
        $activeOrganizationId = session('active_organization_id');


        // ORGANIZATION
        $organizations = $isSuperAdmin
            ? Organization::all()
            : Organization::whereHas('useraccess', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->get();

        // DIVISION
        $divisions = Division::all();
    

        // FOLDER

        $rootFoldersQuery = Folder::with('children')
            ->whereNull('parent_id');

        if (!$isSuperAdmin) {
            $rootFoldersQuery->where('organization_id', $activeOrganizationId);
        }

        $rootFolders = $rootFoldersQuery->get();

        $folderOptions = $this->buildFolderOptions($rootFolders);

        $documentTypes = [];

        if ($activeOrganizationId) {
            $documentTypes = Workflow::where('organization_id', $activeOrganizationId)
                ->pluck('document_type', 'id');
        }

        return view('upload.index', compact(
            'organizations',
            'divisions',
            'folderOptions',
            'isSuperAdmin',
            'documentTypes'
        ));
    }

    private function buildFolderOptions($folders, $prefix = '')
    {
        $result = [];

        foreach ($folders as $folder) {

            $name = $prefix 
                ? $prefix . ' / ' . $folder->folder_name
                : $folder->folder_name;

            $result[] = [
                'id' => $folder->id,
                'name' => $name
            ];

            if ($folder->children && $folder->children->count()) {
                $children = $this->buildFolderOptions($folder->children, $name);
                $result = array_merge($result, $children);
            }
        }

        return $result;
    }

    public function getByOrganization($orgId)
    {
        $folders = Folder::with('children')
            ->where('organization_id', $orgId)
            ->whereNull('parent_id')
            ->get();

        return response()->json(
            $this->buildFolderOptions($folders)
        );
    }

    public function getDocumentTypesByOrganization($orgId)
    {
        $documentTypes = Workflow::where('organization_id', $orgId)
            ->get(['id', 'document_type']);

        return response()->json($documentTypes);
    }

    public function getCC(Request $request)
    {
        $organizationId = $request->get('organization_id');
        
        $users = User::select(
                'users.id',
                'users.name',
                'users.email',
                'divisions.division_name as division_name'
            )
            ->join('user_accesses', 'users.id', '=', 'user_accesses.user_id')
            ->join('divisions', 'user_accesses.division_id', '=', 'divisions.id')
            ->when($organizationId, function ($q) use ($organizationId) {
                $q->where('user_accesses.organization_id', $organizationId);
            })
            ->distinct()
            ->get();

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
    }

public function getWorkflowApprovers($workflowId, Request $request)
{
    $orgId = $request->organization_id;
    $requesterDivisionId = $request->division_id;   // division requester
    $currentUser = auth()->user();
    $activeRoleId = session('active_role_id');

    $activeRoleLevel = Role::where('id', $activeRoleId)->value('role_level');
    $highestRoleLevel = Role::max('role_level');

    // Ambil workflow steps
    $workflowSteps = WorkflowStep::with('division')
        ->where('workflow_id', $workflowId)
        ->orderBy('tier')
        ->get();

    $result = [];
    $isRequesterHighestRole = ($activeRoleLevel == $highestRoleLevel);

    // ================== GROUP 1: Same Division - Higher Role ==================
    if (!$isRequesterHighestRole) {
        $sameDivisionUsers = UserAccess::with(['user', 'role'])
            ->where('organization_id', $orgId)
            ->where('division_id', $requesterDivisionId)
            // ->where('user_id', '!=', $currentUser->id)
            ->whereHas('role', function ($q) use ($activeRoleLevel) {
                // Safeguard activeRoleLevel as well
                $level = $activeRoleLevel ?? 0;
                $q->where('role_level', '>', $level);   // Hanya yang lebih tinggi
            })
            ->get()
            ->map(function($access) {
                return [
                    'id'         => $access->user?->id,
                    'name'       => $access->user?->name ?? 'Unknown User',
                    'role_name'  => $access->role?->role_name ?? '',
                    'role_level' => $access->role?->role_level,
                    'source'     => 'same_division'
                ];
            });

        if ($sameDivisionUsers->isNotEmpty()) {
            $division = Division::find($requesterDivisionId);
            $result[] = [
                'tier'                 => 0,
                'title'                => "Tier 0 • Direct Superior",
                'division_name'        => $division->division_name ?? 'Unknown',
                'division_id'          => $division?->id ?? '',
                'sla_days'             => 0,
                'users'                => $sameDivisionUsers,
                'is_same_division'     => true,
                'is_requester_highest' => false
            ];
        }
    } else {
        // Optional: Kirim info ke frontend
        $result[] = [
            'tier'                 => 0,
            'title'                => "Tier 0",
            'division_name'        => 'Highest Role',
            'division_id'          => $requesterDivisionId,
            'sla_days'             => 0,
            'users'                => [],
            'is_same_division'     => true,
            'is_requester_highest' => true   // Flag penting
        ];
    }

    // ================== GROUP 2: Workflow Tiers ==================
    foreach ($workflowSteps as $step) {
        $users = UserAccess::with(['user', 'role'])
            ->where('organization_id', $orgId)
            ->where('division_id', $step->division_id)
            ->where('user_id', '!=', $currentUser->id)
            ->whereHas('role', function($q) use ($step) {
                // FIX: Fallback to 0 if min_role_level is null to prevent Illegal Operator Exception
                $minLevel = $step->min_role_level ?? 0;
                $q->where('role_level', '>=', $minLevel);
            })
            ->get()
            ->map(function($access) {
                return [
                    'id'         => $access->user?->id,
                    'name'       => $access->user?->name ?? 'Unknown User',
                    'role_name'  => $access->role?->role_name ?? '',
                    'role_level' => $access->role?->role_level,
                    'source'     => 'workflow'
                ];
            });

        $result[] = [
            'tier'             => $step->tier,
            'title'            => "Tier {$step->tier}",
            'division_name'    => $step->division?->division_name ?? 'Unknown',
            'division_id'      => $step->division?->id ?? '',
            'sla_days'         => $step->sla_days,
            'users'            => $users,
            'is_same_division' => false
        ];
    }

    return response()->json([
        'success' => true,
        'workflow_steps' => $result,
        'requester_is_highest_role' => $isRequesterHighestRole
    ]);
}
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $payloadJson = $request->input('payload');
            $payload = json_decode($payloadJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['message' => 'Payload JSON invalid'], 422);
            }

            $uploadedFiles = $request->file('files');
            if (empty($uploadedFiles)) {
                return response()->json(['message' => 'No files uploaded'], 422);
            }
            DB::beginTransaction();

            $folderName = 'documents/' . date('Y/m/d');
            $createdDocuments = [];

            foreach ($uploadedFiles as $index => $file) {
                
                $meta = $payload['files'][$index] ?? [];

                $filename = time() . '_' . $index . '_' . 
                            Str::slug(pathinfo($meta['name'] ?? 'document', PATHINFO_FILENAME)) . 
                            '.' . $file->getClientOriginalExtension();

                $path = $file->storeAs($folderName, $filename, 'public');
                
                // Create Document
                $document = Documents::create([
                    'organization_id'       => $payload['document']['organization_id'],
                    'folder_id'             => $payload['document']['folder_id'],
                    'document_name'         => $meta['name'] ?? 'Untitled',
                    'path'                  => $path,
                    'status'                => 'Need Approval',
                    'requester_id'          => auth()->id(),
                    'requester_division_id' => $payload['document']['requester_division_id'] ?? null,
                    'workflow_id'           => $payload['document']['workflow_id'],
                    'current_tier'          => 0,
                    'placement_type'        => $payload['placement_type'] ?? 'custom',
                    'email_subject'         => $payload['email_subject'] ?? null,
                    'email_message'         => $payload['email_message'] ?? null,
                ]);

                $createdDocuments[] = $document;

                // Mapping temp_id => real approval id
                $approvalIdsMap = [];

                // ================= CREATE ALL APPROVALS =================
                foreach ($payload['document_approvals'] as $app) {
                    $slaDays = (int) ($app['sla_days'] ?? 1);
                    $dueAt = $slaDays == 0 
                        ? now()->endOfDay() 
                        : now()->addDays($slaDays)->endOfDay();

                    $docApproval = DocumentApproval::create([
                        'document_id'       => $document->id,
                        'division_id'       => $app['division_id'],
                        'approver_id'       => $app['approver_id'],
                        'approver_order'    => $app['approver_order'],
                        'show_on_doc'       => $app['show_on_doc'],
                        'status'            => $app['status'] ?? 'Pending',
                        'tier'              => $app['tier'],
                        'remarks'           => '',
                        'sla_days'          => $slaDays,
                        'workflow_step_id'  => $app['workflow_step_id'] ?? null,
                        'started_at'        => now(),
                        'due_at'            => $dueAt,
                        'completed_at'      => $app['status'] === 'Approved' ? now() : null,
                        'is_overdue'        => false,
                        'is_requester' => $app['is_requester'] ?? false,
                    ]);

                    $approvalIdsMap[$app['temp_id']] = $docApproval->id;

                    if ($app['approver_order'] == 2) {
                        $approverUser = User::find($app['approver_id']);
                        if ($approverUser && $approverUser->email) {
                            try {
                                Mail::to($approverUser->email)
                                    ->send(new DocumentApprovalMail($document, $docApproval));
                            } catch (\Exception $mailEx) {
                                \Log::warning("Gagal mengirim email approval ke {$approverUser->email}: " . $mailEx->getMessage());
                            }
                        }
}
                }

                // ================= AUTO APPLY REQUESTER SIGNATURE KE PDF =================
            $requesterApprovals = collect($payload['document_approvals'])
                    ->where('is_requester', true)
                    ->all();

                   

                \Log::info('Requester in payload:', [
                    'has_requester' => !empty($requesterApprovals),
                    'requester_data' => $requesterApprovals,
                    'all_approvals_count' => count($payload['document_approvals'] ?? [])
                ]);

                // ================= AUTO APPLY REQUESTER SIGNATURE =================
                if (!empty($requesterApprovals)) {
                    \Log::info('✅ Memanggil applyRequesterSignature untuk document ID: ' . $document->id);
                    
                    $this->applyRequesterSignature($document, $payload, $index, $approvalIdsMap);
                    
                    \Log::info('✅ applyRequesterSignature selesai dipanggil');
                } else {
                    \Log::warning('❌ Tidak ada requester di payload document_approvals');
                }

                // ================= CREATE APPROVAL POSITIONS =================
                $filePositions = $payload['file_positions'][$index]['signatures'] ?? [];
                foreach ($filePositions as $pos) {
                    if (isset($approvalIdsMap[$pos['approver_temp_id']])) {
                        ApprovalPosition::create([
                            'document_approval_id' => $approvalIdsMap[$pos['approver_temp_id']],
                            'page_number'          => $pos['page_number'],
                            'pos_x_percent'        => $pos['pos_x_percent'],
                            'pos_y_percent'        => $pos['pos_y_percent'],
                            'mode'                 => $pos['mode']
                        ]);
                    }
                }

                // ================= CREATE CC / SHARES =================
                foreach ($payload['document_shares'] as $share) {
                    DocumentShare::create([
                        'document_id' => $document->id,
                        'share_to'    => $share['share_to'],
                        'share_by'    => auth()->id(),
                    ]);
                }
                $document->load([
                    'organization',
                    'folder',
                    'workflow',
                    'documentApprovals.approver',
                    'documentshare.user',
                ]);

                LogActivityJob::dispatchSync(
                    logName: 'document',
                    causedBy: auth()->user(),
                    performedOn: $document,
                    event: 'document.created',
                    description: 'Upload Document',
                    properties: [
                        'attributes' => [
                            'document_name' => $document->document_name,
                            'organization' => $document->organization?->organization_name,
                            'folder' => $document->folder?->folder_name,
                            'workflow' => $document->workflow?->document_type,
                            'status' => $document->status,

                            'approvers' => $document->documentApprovals->map(function ($approval) {
                                return [
                                    'approver' => $approval->approver?->name,
                                    'tier' => $approval->tier,
                                    'status' => $approval->status,
                                ];
                            })->toArray(),

                            'shared_to' => $document->documentshare->map(function ($share) {
                                return [
                                    'user' => $share->user?->name,
                                ];
                            })->toArray(),
                        ],
                    ],
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($createdDocuments) . ' document(s) successfully created',
                'document_ids' => collect($createdDocuments)->pluck('id')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Document Store Error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
            
            return response()->json([
                'message' => 'An error occurred while saving document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto apply requester signature to PDF if "Show on document" is checked
     */
 private function applyRequesterSignature($document, $payload, $fileIndex, $approvalIdsMap)
{
    $requesterApproval = collect($payload['document_approvals'])
        ->firstWhere('is_requester', true);

    if (!$requesterApproval || !($requesterApproval['show_on_doc'] ?? false)) {
        return;
    }

    $placementType = $payload['placement_type'] ?? 'custom';
    $isFixedMode = ($placementType === 'fixed');

    $originalPath = storage_path('app/public/' . $document->path);
    $newFilename = time() . ($isFixedMode ? '_summary_' : '_req_') . basename($document->path);
    $newPath = 'documents/approved/' . $newFilename;
    $newFullPath = storage_path('app/public/' . $newPath);

    Storage::disk('public')->makeDirectory('documents/approved', 0755, true);

    try {
        $pdf = new Fpdi();
        $pdf->setFontSubsetting(true);
        $pageCount = $pdf->setSourceFile($originalPath);

        $approver = User::find($requesterApproval['approver_id']);
        $approvalTime = now()->format('d M Y H:i');

        $approvalStartY = null;

        // ==================== 1. PROSES HALAMAN ASLI DULU ====================
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            // Selalu tambahkan halaman asli ke PDF baru
            $pdf->AddPage();
            $tplId = $pdf->importPage($pageNo);
            $pdf->useTemplate($tplId, 0, 0, null, null, true);

            // Jika mode fixed, lewati penulisan teks coretan di halaman asli
            if ($isFixedMode === true) {
                continue; 
            }

            // --- Logika Custom Mode (Teks menempel di halaman asli sesuai koordinat) ---
            $size = $pdf->getTemplateSize($tplId);
            $pageWidth = $size['width'];
            $pageHeight = $size['height'];
            $paddingY = 2;
            $textToInsert = "Requested by {$approver->name}";

            $positions = collect($payload['file_positions'][$fileIndex]['signatures'] ?? [])
                ->where('approver_temp_id', $requesterApproval['temp_id']);

            foreach ($positions as $pos) {
                if ((int)$pos['page_number'] !== $pageNo) continue;

                $x = $pos['pos_x_percent'] * $pageWidth;
                $y = ($pos['pos_y_percent'] * $pageHeight) + $paddingY;

                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->SetTextColor(0, 128, 0);
                $pdf->SetXY($x, $y);
                $pdf->SetAutoPageBreak(false);
                
                $textWidth = $pdf->GetStringWidth($textToInsert);
                if (($x + $textWidth) > $pageWidth) {
                    $x = $pageWidth - $textWidth - 5;
                }

                $pdf->Text($x, $y, $textToInsert);
            }
        }

        // ===================== 2. FIXED MODE: SUMMARY PAGE (Di Halaman Paling Akhir) =====================
        if ($isFixedMode === true) {
            // Menambahkan halaman baru SETELAH loop halaman asli selesai
            $pdf->AddPage();

            $pdf->SetFont('helvetica', 'B', 18);
            $pdf->Cell(0, 20, 'APPROVAL SUMMARY', 0, 1, 'C');
            $pdf->Ln(10);

            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Document Information', 0, 1);
            $pdf->SetFont('helvetica', '', 11);
            
            $fileName = $document->document_name;
            $pdf->Cell(0, 8, 'File Name : ' . $fileName, 0, 1);
            $pdf->Cell(0, 8, 'Total Pages : ' . $pageCount . ' page(s)', 0, 1);
            $pdf->Ln(12);

            // Requested By
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Requested by : ' . $approver->name, 0, 1);
            $pdf->SetFont('helvetica', '', 11);
            $pdf->Cell(0, 8, 'Date : ' . $approvalTime, 0, 1);
            $pdf->Ln(15);

            // Approved By - Tempat tanda tangan berikutnya
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Approved by :', 0, 1);
            $pdf->SetFont('helvetica', '', 11);

            $approvalStartY = $pdf->GetY();
        }

        // Simpan file baru
        $pdf->Output($newFullPath, 'F');
        
        // Update data dokumen di database
        $document->update([
            'approval_summary_created' => $isFixedMode,
            'approval_start_y'         => $approvalStartY,
            'path'                     => $newPath
        ]);

    } catch (\Exception $e) {
        \Log::error('Requester Signature Error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
    }
}
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
