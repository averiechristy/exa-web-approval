<?php

namespace App\Http\Requests;

use DB;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role_name' => [
                'min:1',
                'max:24',
                'required',
                'regex:/^[A-Za-z\s]+$/',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('roles')
                        ->whereNull('deleted_at')
                        ->whereRaw('LOWER(role_name) = ?', [strtolower($value)])
                        ->when($this->route('role'), function ($query) {
                            $query->where('id', '!=', $this->route('role')->id);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('The role name has already been taken.');
                    }
                },
            ],

            'role_level' => [
                'min:1',
                'max:999',
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('roles')
                        ->whereNull('deleted_at')
                        ->where('role_level', $value)
                        ->when($this->route('role'), function ($query) {
                            $query->where('id', '!=', $this->route('role')->id);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('The role_level has already been taken.');
                    }
                },
            ],
        ];
    }
}
