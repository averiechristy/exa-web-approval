<?php

namespace App\Http\Controllers;

use App\Models\Documents;
use App\Models\DocumentShare;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Storage;
use ZipArchive;

class SharedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        $organizationId = session('active_organization_id')
            ?? $user->current_organization_id
            ?? 1;

        $status = request('status');
        $requester_id = request('requester_id');
        $from_date = request('from_date');
        $to_date = request('to_date');
        $search = request('search');
        $perPage = request('perPage', 10);

        $sharedDocumentIds = DocumentShare::where('share_to', $user->id)
            ->pluck('document_id');

        $query = Documents::with(['requester'])
            ->where('organization_id', $organizationId)
            ->whereIn('id', $sharedDocumentIds)
            ->where('status', 'Approved');

        if (!empty($search)) {
            $query->whereRaw(
                'LOWER(document_name) LIKE ?',
                ['%' . strtolower($search) . '%']
            );
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($requester_id)) {
            $query->where('requester_id', $requester_id);
        }

        if (!empty($from_date)) {
            $query->whereDate('created_at', '>=', $from_date);
        }

        if (!empty($to_date)) {
            $query->whereDate('created_at', '<=', $to_date);
        }

        $documents = $query
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        $userOptions = User::whereHas('userAccesses', function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            })
            ->orderBy('name')
            ->get();

        return view('shared.index', compact(
            'documents',
            'userOptions'
        ));
    }

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

        return view('shared.preview', compact('document', 'folder'));
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

        $zip = new ZipArchive();

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
