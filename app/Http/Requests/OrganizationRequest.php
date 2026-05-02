<?php

namespace App\Http\Requests;

use DB;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizationRequest extends FormRequest
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
            'organization_name' => [
                'min:1',
                'max:24',
                'required',
                'regex:/^[A-Za-z\s]+$/',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('organizations')
                        ->whereNull('deleted_at')
                        ->whereRaw('LOWER(organization_name) = ?', [strtolower($value)])
                        ->when($this->route('organization'), function ($query) {
                            $query->where('id', '!=', $this->route('organization')->id);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('The organization name has already been taken.');
                    }
                },
            ],
        ];
    }
}
