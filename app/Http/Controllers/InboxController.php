<?php

namespace App\Http\Controllers;

use App\Mail\DocumentApprovalMail;
use App\Models\ApprovalPosition;
use App\Models\DocumentApproval;
use App\Models\Documents;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use setasign\Fpdi\Tcpdf\Fpdi;
use Storage;

use ZipArchive;

class InboxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
// app/Http/Controllers/InboxController.php

// public function index()
// {
//     $user = auth()->user();
//     $organizationId = $user->current_organization_id ?? 1;

//     $folders = Folder::where('organization_id', $organizationId)
//                 ->with(['children' => function ($q) {
//                     $q->with('children');
//                 }])
//                 ->whereNull('parent_id')
//                 ->orderBy('folder_name')
//                 ->get();

//     // Ambil semua dokumen yang visible untuk user ini (Inbox utama)
//     $documents = Documents::with(['requester', 'folder', 'documentapprovals'])
//     ->where('organization_id', $organizationId)
//     ->where('status', 'IN_PROGRESS')                    // Hanya yang sedang berjalan
//     ->whereHas('documentapprovals', function ($q) use ($user) {
//         $q->where('approver_id', $user->id)
//           ->where('status', 'PENDING')
//           ->whereColumn('document_approvals.tier', 'documents.current_tier'); // Penting!
//     })
//     ->orderBy('updated_at', 'desc')
//     ->paginate(15);

//     return view('inbox.index', compact('folders', 'documents'));
// }



public function bulkExport(Request $request)
{
    $documentIds = $request->input('document_ids', []);

    if (empty($documentIds)) {
        return response()->json(['error' => 'No documents selected'], 400);
    }

    $documents = Documents::whereIn('id', $documentIds)->get();

    if ($documents->isEmpty()) {
        return response()->json(['error' => 'Documents not found'], 404);
    }

    // Buat nama file ZIP
    $zipFileName = 'documents_export_' . now()->format('Ymd_His') . '.zip';
    $zipPath = storage_path('app/temp/' . $zipFileName);

    // Pastikan folder temp ada
    if (!file_exists(storage_path('app/temp'))) {
        mkdir(storage_path('app/temp'), 0755, true);
    }

    $zip = new ZipArchive;

    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        return response()->json(['error' => 'Cannot create ZIP file'], 500);
    }

    $failed = [];

    foreach ($documents as $doc) {
        if (!$doc->path || !Storage::disk('public')->exists($doc->path)) {
            $failed[] = $doc->document_name;
            continue;
        }

        $filePath = Storage::disk('public')->path($doc->path);
        $fileNameInZip = $doc->document_name;

        // Tambahkan ekstensi jika belum ada
        if (!str_contains($fileNameInZip, '.')) {
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            $fileNameInZip .= '.' . $ext;
        }

        $zip->addFile($filePath, $fileNameInZip);
    }

    $zip->close();

    // Return file untuk di-download
    return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
}
public function index()
{
    $user = auth()->user();
    $organizationId = $user->current_organization_id ?? 1;

    // Load folder tree
    $folders = Folder::where('organization_id', $organizationId)
                ->with(['children' => function ($q) {
                    $q->with('children');
                }])
                ->whereNull('parent_id')
                ->orderBy('folder_name')
                ->get();

    // Buat paginator kosong secara manual
    $documents = new LengthAwarePaginator(
        collect(),                    // items kosong
        0,                            // total = 0
        15,                           // per page
        1,                            // current page
        [
            'path'  => request()->url(),
            'query' => request()->query(),
        ]
    );

    return view('inbox.index', compact('folders', 'documents'));
}


// public function showFolder(Folder $folder)
// {
//     $user = auth()->user();
//     $organizationId = $user->current_organization_id ?? 1;

//     if ($folder->organization_id !== $organizationId) {
//         abort(403);
//     }

//     $folders = Folder::where('organization_id', $organizationId)
//                 ->with(['children' => function ($q) {
//                     $q->with('children');
//                 }])
//                 ->whereNull('parent_id')
//                 ->orderBy('folder_name')
//                 ->get();

//     $documents = Documents::with(['requester', 'folder', 'documentapprovals'])
//         ->where('folder_id', $folder->id)
//         ->where('organization_id', $organizationId)
//         ->where('status', 'IN_PROGRESS')
//         ->whereHas('documentapprovals', function ($q) use ($user) {
//             $q->where('approver_id', $user->id)
//               ->where('status', 'PENDING')
//               ->whereColumn('document_approvals.tier', 'documents.current_tier');
//         })
//         ->orderBy('updated_at', 'desc')
//         ->paginate(15);

//     return view('inbox.index', compact('folders', 'documents', 'folder'));
// }

public function showFolder(Folder $folder)
{
    $user = auth()->user();
    $organizationId = $user->current_organization_id ?? 1;

    if ($folder->organization_id !== $organizationId) {
        abort(403);
    }

    // Ambil subfolders
    $folders = Folder::where('organization_id', $organizationId)
                ->where('parent_id', $folder->id)
                ->orderBy('folder_name')
                ->get();

    // Ambil dokumen
//    $documents = Documents::with(['requester'])
//     ->where('folder_id', $folder->id)
//     ->where('organization_id', $organizationId)
//     ->where('status', '!=', 'APPROVED')           // Tambahkan ini
//     ->whereHas('documentapprovals', function ($q) use ($user) {
//         $q->where('approver_id', $user->id)
//           ->whereColumn('document_approvals.tier', 'documents.current_tier')
//           ->whereIn('status', ['PENDING', 'APPROVED']); // Pastikan statusnya masih pending
//     })
//     // === Kunci Utama: Cek apakah user adalah approver dengan order terkecil yang belum approve ===
//     ->whereDoesntHave('documentapprovals', function ($q) use ($user) {
//         $q->whereColumn('document_approvals.tier', 'documents.current_tier')
//           ->where('status', 'PENDING')
//           ->where('approver_order', '<', function ($sub) use ($user) {
//               $sub->select('approver_order')
//                   ->from('document_approvals')
//                   ->whereColumn('document_approvals.document_id', 'documents.id')
//                   ->whereColumn('document_approvals.tier', 'documents.current_tier')
//                   ->where('approver_id', $user->id)
//                   ->where('status', 'PENDING');
//           });
//     })
//     ->orderBy('updated_at', 'desc')
//     ->paginate(15);
$documents = Documents::with(['requester'])
    ->where('folder_id', $folder->id)
    ->where('organization_id', $organizationId)
    ->where(function ($q) use ($user) {
        // User pernah approve dokumen ini
        $q->whereHas('documentapprovals', function ($sub) use ($user) {
            $sub->where('approver_id', $user->id)
                ->where('status', 'APPROVED');
        })
        // ATAU user adalah approver aktif saat ini
        ->orWhereHas('documentapprovals', function ($sub) use ($user) {
            $sub->where('approver_id', $user->id)
                ->where('status', 'PENDING')
                ->whereColumn('document_approvals.tier', 'documents.current_tier');
        });
    })
    // Untuk approver aktif, pastikan tidak ada approver_order lebih kecil yang masih pending
    ->where(function ($q) use ($user) {
        // Jika sudah approve, langsung lolos
        $q->whereHas('documentapprovals', function ($sub) use ($user) {
            $sub->where('approver_id', $user->id)
                ->where('status', 'APPROVED');
        })

        // Jika pending, cek urutannya
        ->orWhereDoesntHave('documentapprovals', function ($sub) use ($user) {
            $sub->whereColumn('document_approvals.tier', 'documents.current_tier')
                ->where('status', 'PENDING')
                ->where('approver_order', '<', function ($inner) use ($user) {
                    $inner->select('approver_order')
                        ->from('document_approvals')
                        ->whereColumn('document_approvals.document_id', 'documents.id')
                        ->whereColumn('document_approvals.tier', 'documents.current_tier')
                        ->where('approver_id', $user->id)
                        ->where('status', 'PENDING');
                });
        });
    })
    ->orderBy('updated_at', 'desc')
    ->paginate(15);

    // Generate breadcrumb path
    $breadcrumb = $this->getFolderBreadcrumb($folder);

    return view('inbox.index', compact('folders', 'documents', 'folder', 'breadcrumb'));
}

/**
 * Show document preview
 */
public function preview($id)
{
   $document = Documents::with([
        'requester',
        'documentApprovals' => function ($q) {
            $q->where('approver_id', auth()->id());
        }
    ])->findOrFail($id);

    $folder = null;
    if ($document->folder_id) {
        $folder = Folder::find($document->folder_id);
    }

    $document->update([
        'flag_open' => true,
    ]);

    return view('inbox.preview', compact('document', 'folder'));
}/**
 * Approve document by current approver
 */
public function approve(Request $request, $id)
{
    $document = Documents::findOrFail($id);

    if ($document->status === 'APPROVED') {
        return response()->json([
            'success' => false,
            'message' => 'Document has already been fully approved.'
        ], 400);
    }

    $approver = auth()->user();
    $approvalTime = now()->format('d M Y H:i');
    $textToInsert = "Approved by {$approver->name} at {$approvalTime}";

    // Ambil approval record
    $documentApproval = DocumentApproval::where('document_id', $id)
        ->where('approver_id', $approver->id)
        ->first();

    if (!$documentApproval) {
        return response()->json([
            'success' => false,
            'message' => 'Approval record not found or you are not authorized.'
        ], 404);
    }

    if ($documentApproval->status !== 'PENDING') {
        return response()->json([
            'success' => false,
            'message' => 'This approval has already been processed.'
        ], 400);
    }

    $showOnDoc = $documentApproval->show_on_doc ?? true;
    $positions = ApprovalPosition::where('document_approval_id', $documentApproval->id)->get();

    if ($positions->isEmpty() && $showOnDoc) {
        return response()->json([
            'success' => false,
            'message' => 'Approval position not configured for this document.'
        ], 400);
    }

    $originalPath = storage_path('app/public/' . $document->path);
    $newFilename = time() . '_' . basename($document->path);
    $newPath = 'documents/approved/' . $newFilename;
    $newFullPath = storage_path('app/public/' . $newPath);

    Storage::disk('public')->makeDirectory('documents/approved', 0755, true);

    try {
        $pdf = new Fpdi();
        $pdf->setFontSubsetting(true);
        $pageCount = $pdf->setSourceFile($originalPath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pdf->AddPage();
            $tplId = $pdf->importPage($pageNo);
            $pdf->useTemplate($tplId, 0, 0, null, null, true);

            if (!$showOnDoc) continue;

            $size = $pdf->getTemplateSize($tplId);
            $pageWidth = $size['width'];
            $pageHeight = $size['height'];

            foreach ($positions as $pos) {
                if ((int)$pos->page_number !== $pageNo) continue;

              $x = $pos->pos_x_percent * $pageWidth;
$y = $pos->pos_y_percent * $pageHeight;

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(0, 128, 0);

$pdf->SetXY($x, $y);
$pdf->Write(0, $textToInsert);
            }
        }

        $pdf->Output($newFullPath, 'F');

        // === UPDATE APPROVAL RECORD ===
        $now = now();
        $isOverdue = $documentApproval->due_at && $now->gt($documentApproval->due_at);

        $documentApproval->update([
            'status'        => 'APPROVED',
            'completed_at'  => $now,
            'is_overdue'    => $isOverdue,
        ]);

        // === HITUNG TIER PROGRESS ===
        $currentTier = $document->current_tier;

        $tierApprovals = DocumentApproval::where('document_id', $id)
            ->where('tier', $currentTier)
            ->get();

        $approvedInTier = $tierApprovals->where('status', 'APPROVED')->count();
        $totalInTier = $tierApprovals->count();

        $shouldAdvanceTier = ($approvedInTier === $totalInTier);
        $newTier = $shouldAdvanceTier ? $currentTier + 1 : $currentTier;

        // === CEK STATUS DOKUMEN ===
        $allApprovals = DocumentApproval::where('document_id', $id)->get();
        $totalApprovers = $allApprovals->count();
        $approvedCount = $allApprovals->where('status', 'APPROVED')->count();

        $documentStatus = 'PARTIALLY APPROVED';
        if ($approvedCount === $totalApprovers) {
            $documentStatus = 'APPROVED';
        }

        // === UPDATE DOCUMENT ===
        $document->update([
            'path'         => $newPath,
            'status'       => $documentStatus,
            'approved_by'  => $approver->id,
            'current_tier' => $newTier,
        ]);

        // ==================== REKOMENDASI: KIRIM EMAIL KE TIER BERIKUTNYA ====================
        $this->notifyNextApprover($document);

        // === RESPONSE ===
        if ($documentStatus === 'APPROVED') {
            return response()->json([
                'success' => true,
                'message' => 'Document has been fully approved by all approvers.',
                'status'  => $documentStatus
            ]);
        } elseif ($shouldAdvanceTier) {
            return response()->json([
                'success' => true,
                'message' => "Approved successfully. Moving to Tier {$newTier}.",
                'next_tier' => $newTier,
                'status'  => $documentStatus
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => "Approved ({$approvedInTier}/{$totalInTier}) - Waiting for remaining approvers.",
                'status'  => $documentStatus
            ]);
        }

    } catch (\Exception $e) {
        \Log::error('PDF Approval Error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to process document approval: ' . $e->getMessage()
        ], 500);
    }
}

// Di controller yang sama (misalnya InboxController atau DocumentController)
public function bulkApprove(Request $request)
{
    $documentIds = $request->input('document_ids', []);
    
    if (empty($documentIds)) {
        return response()->json([
            'success' => false,
            'message' => 'No documents selected.'
        ], 400);
    }

    $approver = auth()->user();

    // === CEK SEMUA DOKUMEN DULU ===
    $invalidDocuments = [];
    
    foreach ($documentIds as $id) {
        $document = Documents::find($id);
        if (!$document) {
            $invalidDocuments[] = "Document ID {$id} not found.";
            continue;
        }
        
        if (!$document->flag_open) {
            $invalidDocuments[] = $document->document_name;
        }
    }

    // Jika ada dokumen yang belum dibuka → GAGAL SEMUA
    if (!empty($invalidDocuments)) {
        return response()->json([
            'success' => false,
            'message' => 'Bulk approve cannot proceed.',
            'error'   => 'Some documents have not been opened yet. Please open and review them first before approving.',
            'invalid_documents' => $invalidDocuments
        ], 422);
    }

    // === Jika semua sudah dibuka, lanjut proses ===
    $results = [
        'success' => [],
        'failed'  => []
    ];

    foreach ($documentIds as $id) {
        try {
            $document = Documents::findOrFail($id);

            $documentApproval = DocumentApproval::where('document_id', $id)
                ->where('approver_id', $approver->id)
                ->first();

            if (!$documentApproval || $documentApproval->status !== 'PENDING') {
                $results['failed'][] = [
                    'id' => $id,
                    'name' => $document->document_name,
                    'reason' => 'No pending approval for you'
                ];
                continue;
            }

            $approvalResult = $this->processSingleApproval($document, $documentApproval, $approver);

            if ($approvalResult['success']) {
                $results['success'][] = [
                    'id' => $id,
                    'name' => $document->document_name,
                    'status' => $approvalResult['document_status']
                ];
            } else {
                $results['failed'][] = [
                    'id' => $id,
                    'name' => $document->document_name,
                    'reason' => $approvalResult['message']
                ];
            }

        } catch (\Exception $e) {
            \Log::error("Bulk Approve Error - Doc ID {$id}: " . $e->getMessage());
            $results['failed'][] = ['id' => $id, 'reason' => $e->getMessage()];
        }
    }

    return response()->json([
        'success' => true,
        'message' => "Bulk approve completed. Success: " . count($results['success']) . ", Failed: " . count($results['failed']),
        'results' => $results
    ]);
}
private function processSingleApproval($document, $documentApproval, $approver)
{
    $approvalTime = now()->format('d M Y H:i');
    $textToInsert = "Approved by {$approver->name} at {$approvalTime}";

    $showOnDoc = $documentApproval->show_on_doc ?? true;
    $positions = ApprovalPosition::where('document_approval_id', $documentApproval->id)->get();

    $originalPath = storage_path('app/public/' . $document->path);
    $newFilename = time() . '_' . basename($document->path);
    $newPath = 'documents/approved/' . $newFilename;
    $newFullPath = storage_path('app/public/' . $newPath);

    Storage::disk('public')->makeDirectory('documents/approved', 0755, true);

    try {
        $pdf = new Fpdi();
        $pdf->setFontSubsetting(true);
        $pageCount = $pdf->setSourceFile($originalPath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pdf->AddPage();
            $tplId = $pdf->importPage($pageNo);
            $pdf->useTemplate($tplId);

            if (!$showOnDoc) continue;

            $size = $pdf->getTemplateSize($tplId);
            $pageWidth = $size['width'];
            $pageHeight = $size['height'];

            foreach ($positions as $pos) {
                if ((int)$pos->page_number !== $pageNo) continue;

                $x = $pos->pos_x_percent * $pageWidth;
                $y = $pos->pos_y_percent * $pageHeight;

                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->SetTextColor(0, 128, 0);
                $pdf->SetXY($x, $y);
                $pdf->Write(0, $textToInsert);
            }
        }

        $pdf->Output($newFullPath, 'F');

        // Update approval record
        $now = now();
        $isOverdue = $documentApproval->due_at && $now->gt($documentApproval->due_at);

        $documentApproval->update([
            'status'       => 'APPROVED',
            'completed_at' => $now,
            'is_overdue'   => $isOverdue,
        ]);

        // Tier logic (sama seperti method approve lama)
        $currentTier = $document->current_tier;
        $tierApprovals = DocumentApproval::where('document_id', $document->id)
            ->where('tier', $currentTier)
            ->get();

        $approvedInTier = $tierApprovals->where('status', 'APPROVED')->count();
        $totalInTier = $tierApprovals->count();

        $shouldAdvanceTier = ($approvedInTier === $totalInTier);
        $newTier = $shouldAdvanceTier ? $currentTier + 1 : $currentTier;

        // Overall status
        $allApprovals = DocumentApproval::where('document_id', $document->id)->get();
        $documentStatus = ($allApprovals->where('status', 'APPROVED')->count() === $allApprovals->count())
            ? 'APPROVED'
            : 'PARTIALLY APPROVED';

        $document->update([
            'path'         => $newPath,
            'status'       => $documentStatus,
            'approved_by'  => $approver->id,
            'current_tier' => $newTier,
        ]);

        $this->notifyNextApprover($document); // jika method ini ada

        return [
            'success' => true,
            'document_status' => $documentStatus,
            'message' => $documentStatus === 'APPROVED' 
                ? 'Fully approved' 
                : ($shouldAdvanceTier ? "Tier {$newTier}" : 'Partially approved')
        ];

    } catch (\Exception $e) {
        \Log::error('PDF Approval Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
public function reject(Request $request, $id)
{
    $document = Documents::findOrFail($id);
    $reason = $request->input('reason');

    if (empty($reason)) {
        return response()->json([
            'success' => false,
            'message' => 'The rejection reason is required.'
        ], 422);
    }

    $approver = auth()->user();

    // Ambil approval record approver saat ini
    $documentApproval = DocumentApproval::where('document_id', $id)
        ->where('approver_id', $approver->id)
        ->first();

    if (!$documentApproval) {
        return response()->json([
            'success' => false,
            'message' => 'Approval record not found or you are not authorized to access it.'
        ], 404);
    }

    if ($documentApproval->status !== 'PENDING') {
        return response()->json([
            'success' => false,
            'message' => 'This approval has already been processed.'
        ], 400);
    }
    try {
        $now = now();

        // Update approval saat ini
        $documentApproval->update([
            'status'        => 'REJECTED',
            'completed_at'  => $now,
            'remarks'       => $reason,
            'is_overdue'    => $documentApproval->due_at && $now->gt($documentApproval->due_at),
        ]);

        // Reject semua approver di tier yang sama dan tier berikutnya
        $currentTier = $document->current_tier;

        DocumentApproval::where('document_id', $id)
            ->where('tier', '>=', $currentTier)
            ->where('status', 'PENDING')
            ->update([
                'status'       => 'REJECTED',
                'completed_at' => $now,
                'remarks'      => 'Document rejected by previous approver: ' . $reason,
            ]);

        // Update document status menjadi REJECTED
        $document->update([
            'status'      => 'REJECTED',
            'rejected_by' => $approver->id,
            'rejected_at' => $now,
            // path tetap sama (tidak perlu generate PDF baru saat reject)
        ]);

        // Optional: Kirim notifikasi ke pembuat dokumen atau approver lain
        // $this->notifyDocumentRejected($document, $reason);

        return response()->json([
            'success' => true,
            'message' => 'Document has been rejected.',
            'status'  => 'REJECTED'
        ]);

    } catch (\Exception $e) {
        \Log::error('PDF Rejection Error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to process document rejection: ' . $e->getMessage()
        ], 500);
    }
}
public function download($id)
{
    $document = Documents::findOrFail($id);

    // Cek apakah file ada
    if (!$document->path || !Storage::disk('public')->exists($document->path)) {
        abort(404, 'File tidak ditemukan');
    }

    $filePath = $document->path;
    $fileName = $document->document_name;

    // Tambahkan ekstensi jika belum ada di nama
    if (!str_contains($fileName, '.')) {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $fileName .= '.' . $extension;
    }

    return Storage::disk('public')->download($filePath, $fileName);
}
/**
 * Notify all approvers in the next tier
 */
/**
 * Kirim email hanya ke approver berikutnya berdasarkan approver_order
 */
private function notifyNextApprover($document)
{
    $currentTier = $document->current_tier;

    // Ambil semua approval yang masih PENDING, diurutkan per tier lalu per order
    $pendingApprovals = DocumentApproval::where('document_id', $document->id)
        ->where('status', 'PENDING')
        ->orderBy('tier')
        ->orderBy('approver_order')   // Sesuai contoh kamu
        ->get();

    // Ambil approver pertama yang masih pending (ini adalah "next approver")
    $nextApproval = $pendingApprovals->first();

    if (!$nextApproval) {
        return; // Semua sudah approve
    }

    $approverUser = User::find($nextApproval->approver_id);

    if ($approverUser && $approverUser->email) {
        try {
            Mail::to($approverUser->email)
                ->send(new DocumentApprovalMail($document, $nextApproval));

            \Log::info("Notifikasi approval dikirim ke: {$approverUser->email} | Tier: {$nextApproval->tier} | Order: {$nextApproval->approver_order}");
        } catch (\Exception $e) {
            \Log::error("Gagal kirim email ke {$approverUser->email}: " . $e->getMessage());
        }
    }
}

/**
 * Generate full breadcrumb path
 */
private function getFolderBreadcrumb(Folder $folder)
{
    $breadcrumb = [];
    $current = $folder;

    while ($current) {
        $breadcrumb[] = $current;
        $current = $current->parent;   // Pastikan relasi parent ada di model Folder
    }

    return array_reverse($breadcrumb); // dari root ke current
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
        //
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
