<?php

namespace App\Http\Controllers;

use App\Models\ApprovalPosition;
use App\Models\DocumentApproval;
use App\Models\Documents;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use setasign\Fpdi\Tcpdf\Fpdi;
use Storage;

class InboxControllerBackup extends Controller
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
}
// public function approve(Request $request, $id)
// {
//     $document = Documents::findOrFail($id);

//     if ($document->status !== 'IN_PROGRESS') {
//         return response()->json([
//             'success' => false, 
//             'message' => 'Document cannot be approved in current status.'
//         ], 400);
//     }

//     $approver = auth()->user();
//     $approvalTime = now()->format('d M Y H:i');

//     $textToInsert = "Approved by {$approver->name} at {$approvalTime}";

//     $originalPath = storage_path('app/public/' . $document->path);
//     $newFilename = time() . '_' . basename($document->path);
//     $newPath = 'documents/approved/' . $newFilename;
//     $newFullPath = storage_path('app/public/' . $newPath);

//     // Buat direktori jika belum ada
//     Storage::disk('public')->makeDirectory('documents/approved');

//     try {
//         $pdf = new Fpdi();

//         // Penting: Set font path untuk TCPDF
//         $pdf->setFontSubsetting(true);
        
//         $pageCount = $pdf->setSourceFile($originalPath);

//         for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
//             $pdf->AddPage();
//             $tplId = $pdf->importPage($pageNo);
//             $pdf->useTemplate($tplId, 0, 0, null, null, true);

//             // === Tambahkan Teks Approval ===
//             $pdf->SetFont('helvetica', 'B', 12);
//             $pdf->SetTextColor(0, 128, 0); // Green

//             // Sesuaikan posisi ini sesuai kebutuhan PDF Anda
//             // X = 120mm, Y = 240mm (bottom area)
//             $pdf->SetXY(120, 240);
//             $pdf->Cell(80, 10, $textToInsert, 0, 1, 'L');
//         }

//         $pdf->Output($newFullPath, 'F');

//         // Update database
//         $document->update([
//             'path'         => $newPath,
//             'status'       => 'APPROVED',
//             'approved_by'  => $approver->id,
//             'approved_at'  => now(),
//         ]);

//         return response()->json([
//             'success' => true,
//             'message' => 'Document approved and signed successfully.'
//         ]);

//     } catch (\Exception $e) {
//         \Log::error('PDF Approval Error: ' . $e->getMessage());
        
//         return response()->json([
//             'success' => false,
//             'message' => 'Failed to process PDF: ' . $e->getMessage()
//         ], 500);
//     }
// }


public function approve(Request $request, $id)
{
    $document = Documents::findOrFail($id);

    if ($document->status == 'APPROVED') {
        return response()->json([
            'success' => false, 
            'message' => 'Document cannot be approved in current status.'
        ], 400);
    }

    $approver = auth()->user();
    $approvalTime = now()->format('d M Y H:i');
    $textToInsert = "Approved by {$approver->name} at {$approvalTime}";

    // === Ambil data approval record ===
    $documentApproval = DocumentApproval::where('document_id', $id)
        ->where('approver_id', $approver->id)
        ->first();

    if (!$documentApproval) {
        return response()->json([
            'success' => false,
            'message' => 'Approval record not found or already processed.'
        ], 404);
    }

    // === CEK: Apakah perlu tampilkan di PDF? ===
    $showOnDoc = $documentApproval->show_on_doc ?? true;

    // === Ambil posisi ===
    $positions = ApprovalPosition::where('document_approval_id', $documentApproval->id)
        ->get();

    if ($positions->isEmpty() && $showOnDoc) {
        return response()->json([
            'success' => false,
            'message' => 'Approval position not configured.'
        ], 400);
    }

    $originalPath = storage_path('app/public/' . $document->path);
    $newFilename = time() . '_' . basename($document->path);
    $newPath = 'documents/approved/' . $newFilename;
    $newFullPath = storage_path('app/public/' . $newPath);

    Storage::disk('public')->makeDirectory('documents/approved');

    try {
       $pdf = new Fpdi();

    $pdf->setFontSubsetting(true);
    $pageCount = $pdf->setSourceFile($originalPath);

    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $pdf->AddPage();
        $tplId = $pdf->importPage($pageNo);
        $pdf->useTemplate($tplId, 0, 0, null, null, true);

        if (!$showOnDoc) {
            continue;
        }

        $size = $pdf->getTemplateSize($tplId);
        $pageWidth  = $size['width'];
        $pageHeight = $size['height'];

        // === Cari posisi paling bawah di halaman ini ===
        $bottomPosition = $positions->where('page_number', $pageNo)
            ->sortByDesc('pos_y_percent')
            ->first();

     // Di dalam loop page
            foreach ($positions as $pos) {
                if ((int)$pos->page_number !== $pageNo) continue;

               
                    $textWidth = $pdf->GetStringWidth($textToInsert);
                    // ✅ Tanpa padding - langsung gunakan lebar teks asli
                    $x = ($pos->pos_x_percent / 100) * $pageWidth;
                    $y = ($pos->pos_y_percent / 100) * $pageHeight;
                    
                    $pdf->SetFont('helvetica', 'B', 11);
                    $pdf->SetTextColor(0, 128, 0);

$pdf->SetXY($x, $y);
$pdf->Write(0, $textToInsert);
                    
              
                // // STANDARD/FIXED: Pakai logic lama (dengan margin)
                // $x = ($pos->pos_x_percent / 100) * $pageWidth;
                // $y = ($pos->pos_y_percent / 100) * $pageHeight;

                // $pdf->SetFont('helvetica', 'B', 11);
                // $pdf->SetTextColor(0, 128, 0);

                // $textWidth = $pdf->GetStringWidth($textToInsert);

                // // === MODIFIKASI KHUSUS POSISI PALING BAWAH ===
                // if ($bottomPosition && $pos->id === $bottomPosition->id) {
                //     if ($pos->pos_x_percent >= 50) {
                //         $marginX = -55;
                //     } else {
                //         $marginX = 8;
                //     }
                //     $marginY = 18;
                // } else {
                //     if ($pos->pos_x_percent >= 50) {
                //         $marginX = -55;
                //     } else {
                //         $marginX = 8;
                //     }
                // }

                // $finalX = $x + $marginX;
                // $finalY = $y - $marginY;

                // $pdf->SetXY($finalX, $finalY);
                // $align = ($pos->pos_x_percent >= 50) ? 'R' : 'L';

                // $pdf->Cell($textWidth + 10, 8, $textToInsert, 0, 1, $align);
            }
        }

        $pdf->Output($newFullPath, 'F');

        // === UPDATE APPROVAL RECORD ===
        // === UPDATE APPROVAL RECORD (dengan is_overdue) ===
        $now = now();

        $isOverdue = false;
        if ($documentApproval->due_at && $now->gt($documentApproval->due_at)) {
            $isOverdue = true;
        }

        $documentApproval->update([
            'status'        => 'APPROVED',
            'completed_at'  => $now,
            'is_overdue'    => $isOverdue,     // ← TAMBAHKAN INI
        ]);

        // === CEK: Apakah perlu update tier? ===
        $currentTier = $document->current_tier;
        $tierApprovals = DocumentApproval::where('document_id', $id)
            ->where('tier', $currentTier)
            ->get();

        $approvedInTier = $tierApprovals->where('status', 'APPROVED')->count();
        $totalInTier = $tierApprovals->count();

        // Jika semua approver di tier ini sudah approve, naikkan tier
        $shouldAdvanceTier = ($approvedInTier === $totalInTier);
        $newTier = $shouldAdvanceTier ? $currentTier + 1 : $currentTier;

        // === CEK: Apakah semua approver sudah selesai? ===
        $allApprovals = DocumentApproval::where('document_id', $id)->get();
        $totalApprovers = $allApprovals->count();
        $approvedCount = $allApprovals->where('status', 'APPROVED')->count();

        $documentStatus = 'PARTIALLY APPROVED';
        if ($approvedCount === $totalApprovers) {
            $documentStatus = 'APPROVED';
        } elseif ($shouldAdvanceTier) {
            $documentStatus = 'PARTIALLY APPROVED'; // Jadi pending untuk tier berikutnya
        }

        // === UPDATE DOCUMENT ===
        $document->update([
            'path'          => $newPath,
            'status'        => $documentStatus,
            'approved_by'  => $approver->id,
            'current_tier' => $newTier, // <-- UPDATE CURRENT_TIER
        ]);

        // === RESPONSE ===
        $remainingApprovers = $totalApprovers - $approvedCount;

        if ($documentStatus === 'APPROVED') {
            return response()->json([
                'success' => true,
                'message' => 'Document fully approved by all approvers.',
                'show_on_doc' => $showOnDoc
            ]);
        } elseif ($shouldAdvanceTier) {
            return response()->json([
                'success' => true,
                'message' => "Approved. Moving to Tier {$newTier}.",
                'show_on_doc' => $showOnDoc
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => "Approved ({$approvedInTier}/{$totalInTier}) - Waiting for " . ($totalInTier - $approvedInTier) . " more approver(s).",
                'show_on_doc' => $showOnDoc
            ]);
        }

    } catch (\Exception $e) {
        \Log::error('PDF Approval Error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to process PDF: ' . $e->getMessage()
        ], 500);
    }
}


// public function approve(Request $request, $id)
// {
//     $document = Documents::findOrFail($id);
//     if ($document->status == 'APPROVED') {
//         return response()->json([
//             'success' => false,
//             'message' => 'Document cannot be approved in current status.'
//         ], 400);
//     }

//     $approver = auth()->user();
//     $approvalTime = now()->format('d M Y H:i');
//     $textToInsert = "Approved by {$approver->name} at {$approvalTime}";

//     // === Ambil data approval record ===
//     $documentApproval = DocumentApproval::where('document_id', $id)
//         ->where('approver_id', $approver->id)
//         ->first();

//     if (!$documentApproval) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Approval record not found or already processed.'
//         ], 404);
//     }

//     // === CEK: Apakah perlu tampilkan di PDF? ===
//     $showOnDoc = $documentApproval->show_on_doc ?? true;

//     // === Ambil posisi ===
//     $positions = ApprovalPosition::where('document_approval_id', $documentApproval->id)
//         ->get();

//     if ($positions->isEmpty() && $showOnDoc) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Approval position not configured.'
//         ], 400);
//     }

//     $originalPath = storage_path('app/public/' . $document->path);
//     $newFilename = time() . '_' . basename($document->path);
//     $newPath = 'documents/approved/' . $newFilename;
//     $newFullPath = storage_path('app/public/' . $newPath);

//     Storage::disk('public')->makeDirectory('documents/approved');

//     try {
//         $pdf = new Fpdi();
//         $pdf->setFontSubsetting(true);
//         $pageCount = $pdf->setSourceFile($originalPath);

//         for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
//             $pdf->AddPage();
//             $tplId = $pdf->importPage($pageNo);
//             $pdf->useTemplate($tplId, 0, 0, null, null, true);

//             if (!$showOnDoc) {
//                 continue;
//             }

//             $size = $pdf->getTemplateSize($tplId);
//             $pageWidth = $size['width'];
//             $pageHeight = $size['height'];

//             // === Cari posisi paling bawah di halaman ini ===
//             $bottomPosition = $positions->where('page_number', $pageNo)
//                 ->sortByDesc('pos_y_percent')
//                 ->first();

//             foreach ($positions as $pos) {
//                 if ((int)$pos->page_number !== $pageNo) continue;

//                 $x = ($pos->pos_x_percent / 100) * $pageWidth;
//                 $y = ($pos->pos_y_percent / 100) * $pageHeight;

//                 // === Tentukan Alignment (Rata Kanan / Kiri) ===
//                 $align = ($pos->pos_x_percent >= 50) ? 'R' : 'L';

//                 // === Tentukan Margin X dan Y ===
//                 if ($bottomPosition && $pos->id === $bottomPosition->id) {
//                     // Posisi PALING BAWAH
//                     if ($pos->pos_x_percent >= 50) {
//                         $marginX = -15;   // Rata kanan
//                         $marginY = 22;
//                     } else {
//                         $marginX = 8;     // Rata kiri
//                         $marginY = 15;
//                     }
//                 } else {
//                     // Posisi Biasa
//                     if ($pos->pos_x_percent >= 50) {
//                         $marginX = -15;   // Rata kanan
//                         $marginY = 12;
//                     } else {
//                         $marginX = 8;     // Rata kiri
//                         $marginY = 12;
//                     }
//                 }

//                 $finalX = $x + $marginX;
//                 $finalY = $y - $marginY;

//                 // === Set Font & Warna ===
//                 $pdf->SetFont('helvetica', 'B', 11);
//                 $pdf->SetTextColor(0, 128, 0);

//                 $textWidth = $pdf->GetStringWidth($textToInsert);

//                 // === Tulis teks dengan alignment yang sesuai ===
//                 if ($align === 'R') {
//                     // Rata Kanan: geser X ke kiri sesuai panjang teks
//                     $pdf->SetXY($finalX - $textWidth - 5, $finalY);
//                     $pdf->Cell($textWidth + 10, 8, $textToInsert, 0, 1, 'R');
//                 } else {
//                     // Rata Kiri
//                     $pdf->SetXY($finalX, $finalY);
//                     $pdf->Cell($textWidth + 10, 8, $textToInsert, 0, 1, 'L');
//                 }
//             }
//         }

//         $pdf->Output($newFullPath, 'F');

//         // === UPDATE APPROVAL RECORD ===
//         $now = now();
//         $isOverdue = false;
//         if ($documentApproval->due_at && $now->gt($documentApproval->due_at)) {
//             $isOverdue = true;
//         }

//         $documentApproval->update([
//             'status' => 'APPROVED',
//             'completed_at' => $now,
//             'is_overdue' => $isOverdue,
//         ]);

//         // === CEK TIER & STATUS DOKUMEN ===
//         $currentTier = $document->current_tier;
//         $tierApprovals = DocumentApproval::where('document_id', $id)
//             ->where('tier', $currentTier)
//             ->get();

//         $approvedInTier = $tierApprovals->where('status', 'APPROVED')->count();
//         $totalInTier = $tierApprovals->count();
//         $shouldAdvanceTier = ($approvedInTier === $totalInTier);
//         $newTier = $shouldAdvanceTier ? $currentTier + 1 : $currentTier;

//         $allApprovals = DocumentApproval::where('document_id', $id)->get();
//         $totalApprovers = $allApprovals->count();
//         $approvedCount = $allApprovals->where('status', 'APPROVED')->count();

//         $documentStatus = 'PARTIALLY APPROVED';
//         if ($approvedCount === $totalApprovers) {
//             $documentStatus = 'APPROVED';
//         }

//         // === UPDATE DOCUMENT ===
//         $document->update([
//             'path' => $newPath,
//             'status' => $documentStatus,
//             'approved_by' => $approver->id,
//             'current_tier' => $newTier,
//         ]);

//         // === RESPONSE ===
//         if ($documentStatus === 'APPROVED') {
//             return response()->json([
//                 'success' => true,
//                 'message' => 'Document fully approved by all approvers.',
//                 'show_on_doc' => $showOnDoc
//             ]);
//         } elseif ($shouldAdvanceTier) {
//             return response()->json([
//                 'success' => true,
//                 'message' => "Approved. Moving to Tier {$newTier}.",
//                 'show_on_doc' => $showOnDoc
//             ]);
//         } else {
//             return response()->json([
//                 'success' => true,
//                 'message' => "Approved ({$approvedInTier}/{$totalInTier}) - Waiting for " . ($totalInTier - $approvedInTier) . " more approver(s).",
//                 'show_on_doc' => $showOnDoc
//             ]);
//         }

//     } catch (\Exception $e) {
//         \Log::error('PDF Approval Error: ' . $e->getMessage());
//         return response()->json([
//             'success' => false,
//             'message' => 'Failed to process PDF: ' . $e->getMessage()
//         ], 500);
//     }
// }

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
