<?php

namespace App\Services;

use App\Jobs\LogActivityJob;
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
        $folder = Folder::create([
            'organization_id' => $data['organization_id'],
            'parent_id' => $data['parent_id'] ?: null,
            'folder_name' => $data['folder_name']
        ]);

        $folder->load([
            'organization',
            'parent',
        ]);

        LogActivityJob::dispatchSync(
            logName: 'folder',
            causedBy: auth()->user(),
            performedOn: $folder,
            event: 'folder.created',
            description: 'Create Folder',
            properties: [
                'attributes' => [
                    'folder_name' => $folder->folder_name,
                    'organization' => $folder->organization?->organization_name,
                    'parent_folder' => $folder->parent?->folder_name,
                ],
            ],
        );

        return $folder;
    }

    public function updateFolder($data, $folder)
    {
        $folder->load([
            'organization',
            'parent',
        ]);

        $oldData = [
            'folder_name' => $folder->folder_name,
            'organization' => $folder->organization?->organization_name,
            'parent_folder' => $folder->parent?->folder_name,
        ];

        $folder->update([
            'organization_id' => $data['organization_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'folder_name' => $data['folder_name']
        ]);

        $folder->refresh()->load([
            'organization',
            'parent',
        ]);

        $newData = [
            'folder_name' => $folder->folder_name,
            'organization' => $folder->organization?->organization_name,
            'parent_folder' => $folder->parent?->folder_name,
        ];

        LogActivityJob::dispatchSync(
            logName: 'folder',
            causedBy: auth()->user(),
            performedOn: $folder,
            event: 'folder.updated',
            description: 'Update Folder',
            properties: [
                'old' => $oldData,
                'attributes' => $newData,
            ],
        );

        return $folder;
    }

    public function deleteFolder(Folder $folder)
    {

        if (
            $folder->children()->exists() ||
            $folder->document()->exists()
        ) {
            throw new \Exception('Folder cannot be deleted because it is already used.');
        }

        $folder->load([
            'organization',
            'parent',
            'children',
        ]);

        $deletedFolders = $this->collectFolders($folder);

        LogActivityJob::dispatchSync(
            logName: 'folder',
            causedBy: auth()->user(),
            performedOn: $folder,
            event: 'folder.deleted',
            description: 'Delete Folder',
            properties: [
                'old' => [
                    'folder_name' => $folder->folder_name,
                    'organization' => $folder->organization?->organization_name,
                    'parent_folder' => $folder->parent?->folder_name,
                    'deleted_folders' => $deletedFolders,
                ],
            ],
        );

        $this->deleteFolderRecursive($folder);
    }

    private function collectFolders(Folder $folder, array &$folders = []): array
    {
        $folder->load('children');

        foreach ($folder->children as $child) {
            $folders[] = [
                'folder_name' => $child->folder_name,
            ];

            $this->collectFolders($child, $folders);
        }

        return $folders;
    }

    private function deleteFolderRecursive(Folder $folder): void
    {
        $folder->load('children');

        foreach ($folder->children as $child) {
            $this->deleteFolderRecursive($child);
        }

        $folder->delete();
    }
}
