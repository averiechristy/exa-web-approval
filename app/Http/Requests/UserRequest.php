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
                'max:20'
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
                'max:20',
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
                'exists:organizations,id'
            ];

            $rules['organizations.*.division_id'] = [
                'required',
                'exists:divisions,id',
                function ($attribute, $value, $fail) {
                    preg_match('/organizations\.(\d+)\.division_id/', $attribute, $matches);
                    $organizationId = $this->input("organizations.{$matches[1]}.organization_id");

                    if (!\App\Models\Division::whereKey($value)
                        ->where('organization_id', $organizationId)
                        ->exists()) {
                        $fail('The selected division does not belong to the selected organization.');
                    }
                },
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $pairs = [];

            foreach ($this->input('organizations', []) as $index => $organization) {
                $pair = ($organization['organization_id'] ?? '') . ':' . ($organization['division_id'] ?? '');

                if ($pair !== ':' && in_array($pair, $pairs, true)) {
                    $validator->errors()->add(
                        "organizations.{$index}.division_id",
                        'The organization and division combination must be unique.'
                    );
                }

                $pairs[] = $pair;
            }
        });
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
            'organizations.*.division_id.required' => 'Division is required',
            'organizations.*.role_id.required' => 'Role is required',
        ];
    }
}