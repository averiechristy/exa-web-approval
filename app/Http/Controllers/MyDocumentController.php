<?php

namespace App\Http\Controllers;

use App\Models\DocumentApproval;
use App\Models\Documents;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Storage;
use ZipArchive;

class MyDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

        $documents = new LengthAwarePaginator(
            collect(), 0, 15, 1, [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('mydoc.index', compact('folders', 'documents'));
    }

    public function showFolder(Folder $folder)
    {
        $user = auth()->user();
        $organizationId = session('active_organization_id') ?? $user->current_organization_id ?? 1;

        if ($folder->organization_id !== $organizationId) {
            return redirect()->route('mydoc.index');
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
    ->where('status', 'Approved') // workflow sudah selesai
    ->whereHas('documentapprovals', function ($q) use ($user) {
        $q->where('approver_id', $user->id)
            ->where('status', 'Approved');
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

          $userOptions = User::whereHas('userAccesses', function ($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        })
        ->orderBy('name')
        ->get();


        return view('mydoc.index', compact('folders', 'documents', 'folder', 'breadcrumb', 'userOptions'));
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

        return view('mydoc.preview', compact('document', 'folder'));
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
