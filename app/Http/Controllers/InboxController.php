<?php

namespace App\Http\Controllers;

use App\Models\Documents;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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


public function showFolder(Folder $folder)
{
    $user = auth()->user();
    $organizationId = $user->current_organization_id ?? 1;

    if ($folder->organization_id !== $organizationId) {
        abort(403);
    }

    $folders = Folder::where('organization_id', $organizationId)
                ->with(['children' => function ($q) {
                    $q->with('children');
                }])
                ->whereNull('parent_id')
                ->orderBy('folder_name')
                ->get();

    $documents = Documents::with(['requester', 'folder', 'documentapprovals'])
        ->where('folder_id', $folder->id)
        ->where('organization_id', $organizationId)
        ->where('status', 'IN_PROGRESS')
        ->whereHas('documentapprovals', function ($q) use ($user) {
            $q->where('approver_id', $user->id)
              ->where('status', 'PENDING')
              ->whereColumn('document_approvals.tier', 'documents.current_tier');
        })
        ->orderBy('updated_at', 'desc')
        ->paginate(15);

    return view('inbox.index', compact('folders', 'documents', 'folder'));
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
