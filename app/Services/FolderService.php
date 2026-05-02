<?php

namespace App\Services;

use App\Models\Folder;

class FolderService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function createFolder($data)
    {
        $folders = Folder::create([
            'organization_id' => $data['organization_id'],
            'parent_id' => $data['parent_id'] ?: null,
            'folder_name' => $data['folder_name']
        ]);

        return $folders;
    }

    public function updateFolder($data, $folder)
    {
        $folder->update([
            'organization_id' => $data['organization_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'folder_name' => $data['folder_name']
        ]);

        return $folder;
    }

    public function deleteFolder($folder)
    {
        $folder->load('children');

        foreach ($folder->children as $child) {
            $this->deleteFolder($child);
        }

        $folder->delete();
    }
}
