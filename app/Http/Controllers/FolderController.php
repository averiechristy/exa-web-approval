<?php

namespace App\Http\Controllers;

use App\Http\Requests\FolderRequest;
use App\Models\Folder;
use App\Models\Organization;
use App\Services\FolderService;
use Illuminate\Http\Request;

class FolderController extends Controller
{

    public function __construct(private FolderService $folderService)
    {
    }
    public function index()
    {
        $rootFolders = Folder::with('children')
            ->whereNull('parent_id')
            ->get();

        return view('folders.index', [
            'rootFolders' => $rootFolders,
            'folders' => Folder::all(), // buat dropdown
            'organizations' => Organization::all()
        ]);
    }

    public function store(FolderRequest $request)
    {
        $this->folderService->createFolder($request->validated());

        return redirect()->route('folders.index')
            ->with('success', 'Success Add Data');
        
    }

    public function update(FolderRequest $request, Folder $folder)
    {
        $data = $request->validated();

        $this->folderService->updateFolder($data, $folder);

        return redirect()->route('folders.index')
            ->with('success', 'Success Update Data');
    }

    public function destroy(Folder $folder)
    {
        $this->folderService->deleteFolder($folder);

        return redirect()->route('folders.index')
            ->with('success', 'Success Delete Data');
    }
}