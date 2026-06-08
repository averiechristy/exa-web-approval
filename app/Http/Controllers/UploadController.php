<?php

namespace App\Http\Controllers;

use App\Mail\DocumentApprovalMail;
use App\Models\ApprovalPosition;
use App\Models\Division;
use App\Models\DocumentApproval;
use App\Models\Documents;
use App\Models\DocumentShare;
use App\Models\Folder;
use App\Models\Organization;
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
        $divisionId = $request->get('division_id'); 
        $documentTypeId = $request->get('document_type_id');
        
        $users = User::select('users.id', 'users.name', 'users.email', 'user_accesses.organization_id', 'user_accesses.division_id')
                    ->join('user_accesses', 'users.id', '=', 'user_accesses.user_id')
                    ->where('user_accesses.division_id', $divisionId) // 🔥 Filter berdasarkan UserAccess division
                    ->when($organizationId, function($q) use ($organizationId) {
                        return $q->where('user_accesses.organization_id', $organizationId);
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

        // Ambil workflow steps
        $workflowSteps = WorkflowStep::with('division')
            ->where('workflow_id', $workflowId)
            ->orderBy('tier')
            ->get();

        $result = [];

        // ================== GROUP 1: Same Division - Higher Role ==================
        $sameDivisionUsers = UserAccess::with(['user', 'role'])
            ->where('organization_id', $orgId)
            ->where('division_id', $requesterDivisionId)
            // ->where('user_id', '!=', $currentUser->id)
            ->whereHas('role', function($q) use ($currentUser) {
                // Role lebih tinggi dari user saat ini
                $q->where('role_level', '>', $currentUser->active_role_level ?? 1);
            })
            ->get()
            ->map(function($access) {
                return [
                    'id'         => $access->user->id,
                    'name'       => $access->user->name,
                    'role_name'  => $access->role->role_name ?? '',
                    'role_level' => $access->role->role_level,
                    'source'     => 'same_division'
                ];
            });

    //            $sameDivisionUsers = UserAccess::with(['user', 'role'])
    // ->where('organization_id', $orgId)
    // ->where('division_id', $requesterDivisionId)
    // ->where(function ($q) use ($currentUser) {
    //     // Kondisi 1: Wajibkan Current User untuk muncul (tidak peduli level rolenya)
    //     $q->where('user_id', $currentUser->id)
        
    //     // Kondisi 2: ATAU, Jika user BUKAN current user...
    //     ->orWhere(function ($q2) use ($currentUser) {
    //         $q2->where('user_id', '!=', $currentUser->id)
    //            // ...maka role-nya harus lebih tinggi dari current user
    //            ->whereHas('role', function ($q3) use ($currentUser) {
    //                $q3->where('role_level', '>', $currentUser->active_role_level ?? 1);
    //            });
    //     });
    // })
    // ->get()
    // ->map(function($access) {
    //     return [
    //         'id'         => $access->user->id,
    //         'name'       => $access->user->name,
    //         'role_name'  => $access->role->role_name ?? '',
    //         'role_level' => $access->role->role_level,
    //         'source'     => 'same_division'
    //     ];
    // });

        // Masukkan sebagai Tier 0 atau "Direct Superior"
        if ($sameDivisionUsers->isNotEmpty()) {
            $division = Division::find($requesterDivisionId);
            $result[] = [
                'tier'          => 0,
                'title'         => "Tier 0",
                'division_name' => $division->division_name ?? 'Unknown',
                'division_id'   => $division?->id ?? '',
                'sla_days'      => 0,
                'users'         => $sameDivisionUsers,
                'is_same_division' => true
            ];
        }

        // ================== GROUP 2: Workflow Tiers ==================
        foreach ($workflowSteps as $step) {
            $users = UserAccess::with(['user', 'role'])
                ->where('organization_id', $orgId)
                ->where('division_id', $step->division_id)
                ->where('user_id', '!=', $currentUser->id)
                ->whereHas('role', function($q) use ($step) {
                    $q->where('role_level', '>=', $step->min_role_level);
                })
                ->get()
                ->map(function($access) {
                    return [
                        'id'         => $access->user->id,
                        'name'       => $access->user->name,
                        'role_name'  => $access->role->role_name ?? '',
                        'role_level' => $access->role->role_level,
                        'source'     => 'workflow'
                    ];
                });

            $result[] = [
                'tier'          => $step->tier,
                'title'         => "Tier {$step->tier}",
                'division_name' => $step->division?->division_name ?? 'Unknown',
                'division_id'   => $step->division?->id ?? '',
                'sla_days'      => $step->sla_days,
                'users'         => $users,
                'is_same_division' => false
            ];
        }

        return response()->json([
            'success' => true,
            'workflow_steps' => $result
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
                'status'                => 'WAITING APPROVAL',
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
                    'status'            => $app['status'] ?? 'PENDING',
                    'tier'              => $app['tier'],
                    'remarks'           => '',
                    'sla_days'          => $slaDays,
                    'workflow_step_id'  => $app['workflow_step_id'] ?? null,
                    'started_at'        => now(),
                    'due_at'            => $dueAt,
                    'completed_at'      => $app['status'] === 'APPROVED' ? now() : null,
                    'is_overdue'        => false,
                ]);

                $approvalIdsMap[$app['temp_id']] = $docApproval->id;

                // if ($app['approver_order'] == 2) {
                //     $approverUser = User::find($app['approver_id']);
                //     if ($approverUser && $approverUser->email) {
                //         Mail::to($approverUser->email)
                //             ->send(new DocumentApprovalMail($document, $docApproval));
                //     }
                // }
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
        return; // Tidak perlu apply signature
    }

    $originalPath = storage_path('app/public/' . $document->path);
    $newFilename = time() . '_req_' . basename($document->path);
    $newPath = 'documents/approved/' . $newFilename;
    $newFullPath = storage_path('app/public/' . $newPath);

    Storage::disk('public')->makeDirectory('documents/approved');

    try {
        $pdf = new Fpdi();
        $pdf->setFontSubsetting(true);
        $pageCount = $pdf->setSourceFile($originalPath);

        $approver = User::find($requesterApproval['approver_id']);
        $approvalTime = now()->format('d M Y H:i');
        $textToInsert = "Approved by {$approver->name} at {$approvalTime}";

        $positions = collect($payload['file_positions'][$fileIndex]['signatures'] ?? [])
            ->where('approver_temp_id', $requesterApproval['temp_id']);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pdf->AddPage();
            $tplId = $pdf->importPage($pageNo);
            $pdf->useTemplate($tplId, 0, 0, null, null, true);

            $size = $pdf->getTemplateSize($tplId);
            $pageWidth = $size['width'];
            $pageHeight = $size['height'];

            // Apply signature requester di halaman yang sesuai
            foreach ($positions as $pos) {
                if ((int)$pos['page_number'] !== $pageNo) continue;

                $x = $pos['pos_x_percent'] * $pageWidth;
$y = $pos['pos_y_percent'] * $pageHeight;

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(0, 128, 0);

$pdf->SetXY($x, $y);
$pdf->Write(0, $textToInsert);
            }
        }

        $pdf->Output($newFullPath, 'F');

        // Update document path dengan PDF yang sudah ada signature requester
        $document->update([
            'path' => $newPath,
        ]);

    } catch (\Exception $e) {
        \Log::error('Requester Signature Error: ' . $e->getMessage());
        // Tidak throw, biarkan proses store tetap berhasil
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
