<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Folder;
use Illuminate\Support\Facades\DB;

class FolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_id' => 'required',
            'parent_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $folderId = $this->route('id'); 

                    if ($value && $value == $folderId) {
                        $fail('Folder cannot be its own parent.');
                    }
                }
            ],
            'folder_name' => [
                'required',
                'max:50',
                function ($attribute, $value, $fail) {

                    $exists = DB::table('folders')
                        ->whereNull('deleted_at')
                        ->where('organization_id', $this->organization_id)
                        ->where('folder_name', $value)
                        ->when($this->route('id'), function ($query) {
                            $query->where('id', '!=', $this->route('id'));
                        })
                        ->exists();

                    if ($exists) {
                        $fail('Folder name already exists in this organization.');
                    }
                }
            ],
        ];
    }
}