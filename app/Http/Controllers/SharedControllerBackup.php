<?php

namespace App\Http\Controllers;

use App\Models\Documents;
use App\Models\DocumentShare;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SharedControllerBackup extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        
        $organizationId = session('active_organization_id') ?? $user->current_organization_id ?? 1;

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

        $rootFoldersQuery = Folder::with('children')
        ->whereNull('parent_id')
        ->where('organization_id', $organizationId);
        

        $rootFolders = $rootFoldersQuery->get();

        $folderOptions = $this->buildFolderOptions($rootFolders);

        return view('shared.index', compact('folders', 'documents', 'folderOptions'));
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

        $folders = Folder::where('organization_id', $organizationId)
            ->where('parent_id', $folder->id)
            ->orderBy('folder_name')
            ->get();

        // Ambil document_id yang di-share ke user login
        $sharedDocumentIds = DocumentShare::where('share_to', $user->id)
            ->pluck('document_id');

        $documents = Documents::with(['requester'])
            ->where('folder_id', $folder->id)
            ->where('organization_id', $organizationId)
            ->whereIn('id', $sharedDocumentIds)
            ->orderBy('updated_at', 'desc')
            ->paginate(15)
            ->appends(request()->query());

        $breadcrumb = $this->getFolderBreadcrumb($folder);

        return view('shared.index', compact(
            'folders',
            'documents',
            'folder',
            'breadcrumb'
        ));
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
