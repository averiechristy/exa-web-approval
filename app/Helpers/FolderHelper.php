<?php

namespace App\Helpers;

use App\Models\Folder;

class FolderHelper
{
    /**
     * Get all parent IDs including itself for a folder
     */
    public static function getOpenFolderIds($folderId)
    {
        $openIds = [];
        $current = Folder::find($folderId);

        while ($current) {
            $openIds[] = $current->id;
            $current = $current->parent; // asumsi ada relation parent()
        }

        return array_unique($openIds);
    }
}