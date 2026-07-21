<?php

namespace App\Http\Controllers;

use App\Mail\DocumentApprovalMail;
use App\Models\ApprovalPosition;
use App\Models\DocumentApproval;
use App\Models\Documents;
use App\Models\Folder;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use setasign\Fpdi\Tcpdf\Fpdi;
use Storage;

use ZipArchive;

class InboxControllerBackup extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function moveFolder(Request $request)
    {
        $request->validate([
            'document_id' => ['required', 'exists:documents,id'],
            'folder_id'   => ['required', 'exists:folders,id'],
        ]);

        $document = Documents::findOrFail($request->document_id);

        $document->update([
            'folder_id' => $request->folder_id,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Document moved successfully.');
    }

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
        $organizationId = session('active_organization_id') ?? $user->current_organization_id ?? 1;

        // Search folder
        $folderSearch = request('folder_search');

        // Load root folders dengan pagination + search
        $foldersQuery = Folder::where('organization_id', $organizationId)
                            ->whereNull('parent_id')
                            ->orderBy('folder_name');

        if ($folderSearch) {
            $foldersQuery->whereRaw('LOWER(folder_name) LIKE ?', ['%' . strtolower($folderSearch) . '%']);
        }

        $folders = $foldersQuery->paginate(10)
                            ->appends(request()->query());

        // Documents tetap kosong (seperti sebelumnya)
        $documents = new LengthAwarePaginator(
            collect(), 0, 15, 1, [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]
        );

        // Folder Options untuk move document
        $rootFoldersQuery = Folder::with('children')
            ->whereNull('parent_id')
            ->where('organization_id', $organizationId);
        $rootFolders = $rootFoldersQuery->get();
        $folderOptions = $this->buildFolderOptions($rootFolders);

        return view('inbox.index', compact('folders', 'documents', 'folderOptions'));
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

    public function showFolder(Folder $folder)
    {
        $user = auth()->user();
        $organizationId = session('active_organization_id') ?? $user->current_organization_id ?? 1;

        if ($folder->organization_id !== $organizationId) {
            return redirect()->route('inbox.index');
        }

        // === FOLDERS dengan Pagination + Search ===
        $folderSearch = request('folder_search');

        $foldersQuery = Folder::where('organization_id', $organizationId)
                            ->where('parent_id', $folder->id)
                            ->orderBy('folder_name');

        if ($folderSearch) {
            $foldersQuery->whereRaw('LOWER(folder_name) LIKE ?', ['%' . strtolower($folderSearch) . '%']);
        }

        $folders = $foldersQuery->paginate(10, ['*'], 'folder_page')   // ← Tambahkan 'folder_page'
                            ->appends(request()->query());

        // === Documents (kode lama kamu tetap) ===
      $documents = Documents::with(['requester'])
    ->where('folder_id', $folder->id)
    ->where('organization_id', $organizationId)
    ->where(function ($q) use ($user) {

        // User pernah approve dokumen ini
        $q->whereHas('documentapprovals', function ($sub) use ($user) {
            $sub->where('approver_id', $user->id)
                ->where('status', 'Approved');
        })

        // User adalah approver aktif saat ini
        ->orWhereHas('documentapprovals', function ($sub) use ($user) {
            $sub->where('approver_id', $user->id)
                ->where('status', 'Pending')
                ->whereColumn('document_approvals.tier', 'documents.current_tier')
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('document_approvals as da2')
                        ->whereColumn('da2.document_id', 'document_approvals.document_id')
                        ->whereColumn('da2.tier', 'document_approvals.tier')
                        ->whereColumn('da2.approver_order', '<', 'document_approvals.approver_order')
                        ->where('da2.status', 'Pending');
                });
        })

        // User reject di current tier
        ->orWhereHas('documentapprovals', function ($sub) use ($user) {
            $sub->where('approver_id', $user->id)
                ->where('status', 'Rejected')
                ->whereColumn('document_approvals.tier', 'documents.current_tier');
        });

    });

        // === FILTERS ===
        $status = request('status');
        $requester_id = request('requester_id');
        $from_date = request('from_date');
        $to_date = request('to_date');
        $search = request('search');

        $perPage = request('perPage', 10);

        // Filter Search by Document Name
        if ($search) {
            $documents->whereRaw(
                'LOWER(document_name) LIKE ?',
                ['%' . strtolower($search) . '%']
            );
        }
        // Filter Status
        if ($status) {
            $documents->where('status', $status);
        }

        // Filter Requester
        if ($requester_id) {
            $documents->where('requester_id', $requester_id);
        }

        // Filter Tanggal
        if ($from_date) {
            $documents->whereDate('created_at', '>=', $from_date);
        }
        if ($to_date) {
            $documents->whereDate('created_at', '<=', $to_date);
        }

        $documents = $documents
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        // Breadcrumb, Folder Options, User Options
        $breadcrumb = $this->getFolderBreadcrumb($folder);
        $rootFoldersQuery = Folder::with('children')
            ->whereNull('parent_id')
            ->where('organization_id', $organizationId);
        $rootFolders = $rootFoldersQuery->get();
        $folderOptions = $this->buildFolderOptions($rootFolders);

        $userOptions = User::whereHas('userAccesses', function ($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        })
        ->orderBy('name')
        ->get();

        return view('inbox.index', compact('folders', 'documents', 'folder', 'breadcrumb', 'folderOptions', 'userOptions'));
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

    // Tandai bahwa approver ini sudah membuka dokumen
    DocumentApproval::where('document_id', $document->id)
        ->where('approver_id', auth()->id())
        ->update([
            'flag_open' => true
        ]);

    return view('inbox.preview', compact('document', 'folder'));
}
    /**
     * Approve document by current approver
     */
    public function approve(Request $request, $id)
    {
        $document = Documents::findOrFail($id);

        if ($document->status === 'Approved') {
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
        
        $approvalPosition = ApprovalPosition::where('document_approval_id', $documentApproval['id'])->first();
        $placementType = $approvalPosition['mode'] ?? 'custom';
        $isFixedMode = $placementType === 'fixed';
        if (!$documentApproval) {
            return response()->json([
                'success' => false,
                'message' => 'Approval record not found or you are not authorized.'
            ], 404);
        }

        if ($documentApproval->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'This approval has already been processed.'
            ], 400);
        }

        $showOnDoc = $documentApproval->show_on_doc ?? true;
        $requesterApproval = DocumentApproval::where('document_id', $document->id)
            ->where('is_requester', true)
            ->first();

        $needCreateSummaryPage =
            $isFixedMode &&
            $showOnDoc &&
            !$document->approval_summary_created;
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
            $pdf->document_id = $document->id;

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $pdf->AddPage();
                $tplId = $pdf->importPage($pageNo);
                $pdf->useTemplate($tplId, 0, 0, null, null, true);

                if (!$showOnDoc) continue;

                if (!$isFixedMode && $showOnDoc) {
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
     $pdf->SetAutoPageBreak(false);
     $textWidth = $pdf->GetStringWidth($textToInsert);

if (($x + $textWidth) > $pageWidth) {
    $x = $pageWidth - $textWidth - 5;
}

$pdf->Text($x, $y, $textToInsert);
                }
            }

                if ($isFixedMode && $showOnDoc && $pageNo === $pageCount) {

                   if ($needCreateSummaryPage) {

                        $this->createApprovalSummaryPage(
                            $pdf,
                            $document,
                            $pageCount
                        );

                        $document->approval_summary_created = true;
                    }
                        $this->addApproverToFixedSummary(
                        $pdf,
                        $approver,
                        $approvalTime,
                        $document
                    );
                    
                }
            }

            $pdf->Output($newFullPath, 'F');

            // === UPDATE APPROVAL RECORD ===
            $now = now();
            $isOverdue = $documentApproval->due_at && $now->gt($documentApproval->due_at);

            $documentApproval->update([
                'status'        => 'Approved',
                'completed_at'  => $now,
                'is_overdue'    => $isOverdue,
            ]);

            // === HITUNG TIER PROGRESS ===
            $currentTier = $document->current_tier;

            $tierApprovals = DocumentApproval::where('document_id', $id)
                ->where('tier', $currentTier)
                ->get();

            $approvedInTier = $tierApprovals->where('status', 'Approved')->count();
            $totalInTier = $tierApprovals->count();

            $shouldAdvanceTier = ($approvedInTier === $totalInTier);
            $newTier = $shouldAdvanceTier ? $currentTier + 1 : $currentTier;

            // === CEK STATUS DOKUMEN ===
            $allApprovals = DocumentApproval::where('document_id', $id)->get();
            $totalApprovers = $allApprovals->count();
            $approvedCount = $allApprovals->where('status', 'Approved')->count();

            $documentStatus = 'In Progress';
            if ($approvedCount === $totalApprovers) {
                $documentStatus = 'Approved';
            }

            // === UPDATE DOCUMENT ===
            $document->update([
                'path'         => $newPath,
                'status'       => $documentStatus,
                'approved_by'  => $approver->id,
                'current_tier' => $newTier,
                'approval_summary_created' => $document->approval_summary_created,
                'approval_start_y' => $document->approval_start_y,
            ]);

            // ==================== REKOMENDASI: KIRIM EMAIL KE TIER BERIKUTNYA ====================
            $this->notifyNextApprover($document);

            // === RESPONSE ===
            if ($documentStatus === 'Approved') {
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

            $documentApproval = DocumentApproval::where('document_id', $id)
                ->where('approver_id', $approver->id)
                ->first();

            if (!$documentApproval || !$documentApproval->flag_open) {
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

                if (!$documentApproval || $documentApproval->status !== 'Pending') {
                    $results['failed'][] = [
                        'id' => $id,
                        'name' => $document->document_name,
                        'reason' => 'No Pending approval for you'
                    ];
                    continue;
                }

                $approvalResult = $this->processSingleApproval($document, $documentApproval, $approver, $id);

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
    private function processSingleApproval($document, $documentApproval, $approver, $id)
    {
        $approvalTime = now()->format('d M Y H:i');
        $textToInsert = "Approved by {$approver->name} at {$approvalTime}";

        $documentApproval = DocumentApproval::where('document_id', $id)
            ->where('approver_id', $approver->id)
            ->first();
        
        $approvalPosition = ApprovalPosition::where('document_approval_id', $documentApproval['id'])->first();
        $placementType = $approvalPosition['mode'] ?? 'custom';
        $isFixedMode = $placementType === 'fixed';
        $showOnDoc = $documentApproval->show_on_doc ?? true;
        $positions = ApprovalPosition::where('document_approval_id', $documentApproval->id)->get();

        $originalPath = storage_path('app/public/' . $document->path);
        $newFilename = time() . '_' . basename($document->path);
        $newPath = 'documents/approved/' . $newFilename;
        $newFullPath = storage_path('app/public/' . $newPath);

        $needCreateSummaryPage =
            $isFixedMode &&
            $showOnDoc &&
            !$document->approval_summary_created;
        Storage::disk('public')->makeDirectory('documents/approved', 0755, true);

        try {
            $pdf = new Fpdi();
            $pdf->setFontSubsetting(true);
            $pageCount = $pdf->setSourceFile($originalPath);

            $pdf->document_id = $document->id;
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $pdf->AddPage();
                $tplId = $pdf->importPage($pageNo);
                $pdf->useTemplate($tplId);

                if (!$showOnDoc) continue;

                  if (!$isFixedMode && $showOnDoc) {
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
                if ($isFixedMode && $showOnDoc && $pageNo === $pageCount) {

                   if ($needCreateSummaryPage) {

                        $this->createApprovalSummaryPage(
                            $pdf,
                            $document,
                            $pageCount
                        );

                        $document->approval_summary_created = true;
                    }
                        $this->addApproverToFixedSummary(
                        $pdf,
                        $approver,
                        $approvalTime,
                        $document
                    );
                    
                }
            }

            $pdf->Output($newFullPath, 'F');

            // Update approval record
            $now = now();
            $isOverdue = $documentApproval->due_at && $now->gt($documentApproval->due_at);

            $documentApproval->update([
                'status'       => 'Approved',
                'completed_at' => $now,
                'is_overdue'   => $isOverdue,
            ]);

            // Tier logic (sama seperti method approve lama)
            $currentTier = $document->current_tier;
            $tierApprovals = DocumentApproval::where('document_id', $document->id)
                ->where('tier', $currentTier)
                ->get();

            $approvedInTier = $tierApprovals->where('status', 'Approved')->count();
            $totalInTier = $tierApprovals->count();

            $shouldAdvanceTier = ($approvedInTier === $totalInTier);
            $newTier = $shouldAdvanceTier ? $currentTier + 1 : $currentTier;

            // Overall status
            $allApprovals = DocumentApproval::where('document_id', $document->id)->get();
            $documentStatus = ($allApprovals->where('status', 'Approved')->count() === $allApprovals->count())
                ? 'Approved'
                : 'In Progress';

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
                'message' => $documentStatus === 'Approved' 
                    ? 'Fully approved' 
                    : ($shouldAdvanceTier ? "Tier {$newTier}" : 'In Progress')
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

        if ($documentApproval->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'This approval has already been processed.'
            ], 400);
        }

        try {
            $now = now();
            $currentTier = $document->current_tier;

            // Update approval saat ini dengan alasan asli
            $documentApproval->update([
                'status'        => 'Rejected',
                'completed_at'  => $now,
                'remarks'       => $reason,
                'is_overdue'    => $documentApproval->due_at && $now->gt($documentApproval->due_at),
            ]);

            // Reject semua approver di tier yang sama dan tier berikutnya
            DocumentApproval::where('document_id', $id)
                ->where('tier', '>=', $currentTier)
                ->where('status', 'Pending')
                ->update([
                    'status'       => 'Rejected',
                    'completed_at' => $now,
                    'remarks'      => 'Document rejected by previous approver: ' . $reason,
                ]);

            // === MODIFIKASI BARU ===
            // Update remarks untuk semua approver sebelumnya (tier < currentTier)
            // yang sudah Approved agar mereka juga tahu alasan penolakan
            DocumentApproval::where('document_id', $id)
                ->where('tier', '<', $currentTier)
                ->where('status', 'Approved')           // hanya yang sudah approve
                ->update([
                    'remarks' => $documentApproval->remarks ?? $reason   // pakai remarks asli
                ]);

            // Update document status menjadi Rejected
            $document->update([
                'status'      => 'Rejected',
                'rejected_by' => $approver->id,
                'rejected_at' => $now,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document has been rejected.',
                'status'  => 'Rejected'
            ]);

        } catch (\Exception $e) {
            \Log::error('Document Rejection Error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
            
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
            abort(404, 'File not found');
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

    private function notifyNextApprover($document)
    {
        $currentTier = $document->current_tier;

        // Ambil semua approval yang masih Pending, diurutkan per tier lalu per order
        $PendingApprovals = DocumentApproval::where('document_id', $document->id)
            ->where('status', 'Pending')
            ->orderBy('tier')
            ->orderBy('approver_order')   // Sesuai contoh kamu
            ->get();

        // Ambil approver pertama yang masih Pending (ini adalah "next approver")
        $nextApproval = $PendingApprovals->first();

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
 * Tambahkan approver baru ke bagian bawah "Approved by" di Summary Page
 */
/**
 * Tambahkan approver baru ke Summary Page dengan posisi dinamis
 * Tanpa bergantung pada kolom is_requester
 */
private function addApproverToFixedSummary(Fpdi $pdf, $approver, $approvalTime, $document)
{
    // Pindah ke halaman terakhir (Summary Page)
    $pdf->setPage($pdf->getNumPages());

    // Hitung jumlah approver yang SUDAH APPROVED (kecuali requester)
    $approvedCount = DocumentApproval::where('document_id', $document->id)  // pakai $document dari scope luar
        ->where('status', 'Approved')
        ->count();

    // Karena requester biasanya sudah approved duluan, kurangi 1
    $approvedCount = max(0, $approvedCount - 1);

    // Posisi dasar (sesuaikan dengan layout kamu)
    $baseX = 10;           // mm dari kiri
    $baseY = $document->approval_start_y;
    $lineHeight = 6;       // jarak antar baris

    $y = $baseY + ($approvedCount * $lineHeight);

    $text = "{$approver->name} at {$approvalTime}";

    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY($baseX, $y);
    $pdf->Cell(0, 8, $text, 0, 1);
}

private function createApprovalSummaryPage(Fpdi $pdf, Documents $document, int $pageCount)
{
    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 20, 'APPROVAL SUMMARY', 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Document Information', 0, 1);

    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 8, 'File Name : ' . $document->document_name, 0, 1);
    $pdf->Cell(0, 8, 'Total Pages : ' . $pageCount . ' page(s)', 0, 1);

    // kalau requester ada
    if ($document->requester) {
        $pdf->Cell(0, 8, 'Requester : ' . $document->requester->name, 0, 1);
    }

    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Approved by :', 0, 1);
    $document->approval_start_y = $pdf->GetY();
}
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
