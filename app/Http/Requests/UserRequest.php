<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // ================= BASIC =================
            'name' => [
                'required',
                'string',
                'max:100'
            ],

            'email' => [
                'required',
                'email',
                'max:100',
                    Rule::unique('users')
                    ->ignore($this->id)
                    ->where(function ($query) {
                        return $query->whereRaw(
                            'LOWER(email) = ?',
                            [strtolower($this->email)]
                        )->whereNull('deleted_at');
                    })
            ],

            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users')
                ->ignore($this->id)
                ->where(function ($query) {
                    return $query->whereRaw(
                        'LOWER(username) = ?',
                        [strtolower($this->username)]
                    )->whereNull('deleted_at');
                })
            ],

            // ================= SYSTEM ROLE =================
            'system_role_id' => [
                'required',
                'exists:system_roles,id'
            ],
        ];

        // ================= CONDITIONAL ORGANIZATION =================
        if ($this->system_role_id != 1) {
            $rules['organizations'] = [
                'required',
                'array',
                'min:1'
            ];

            $rules['organizations.*.organization_id'] = [
                'required',
                'exists:organizations,id',
                'distinct'
            ];

            $rules['organizations.*.division_id'] = [
                'required',
                'exists:divisions,id'
            ];

            $rules['organizations.*.role_id'] = [
                'required',
                'exists:roles,id'
            ];

            $rules['organizations.*.manager_id'] = [
                'nullable',
                'exists:users,id'
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            // BASIC
            'name.required' => 'Name is required',

            'email.required' => 'Email is required',
            'email.email' => 'Invalid email format',
            'email.unique' => 'Email already exists',

            'username.required' => 'Username is required',
            'username.unique' => 'Username already exists',

            // SYSTEM ROLE
            'system_role_id.required' => 'System role is required',

            // ORGANIZATION
            'organizations.required' => 'Organization is required',
            'organizations.*.organization_id.required' => 'Organization is required',
            'organizations.*.organization_id.distinct' => 'Organization must be unique',
            'organizations.*.division_id.required' => 'Division is required',
            'organizations.*.role_id.required' => 'Role is required',
        ];
    }
}