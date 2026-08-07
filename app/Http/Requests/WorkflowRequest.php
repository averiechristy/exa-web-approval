<?php

namespace App\Http\Requests;

use DB;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class WorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type' => [
    'required',
    'string',
    function ($attribute, $value, $fail) {
        $exists = DB::table('workflows')
            ->whereNull('deleted_at')
            ->where('organization_id', $this->organization_id)
            ->whereRaw('LOWER(document_type) = ?', [strtolower($value)])
            ->when($this->route('workflow'), function ($query) {
                $query->where('id', '!=', $this->route('workflow')->id);
            })
            ->exists();

        if ($exists) {
            $fail('The document type has already been taken for this organization.');
        }
    },
],
            'organization_id'=>['required'],

            'steps' => ['required', 'array', 'min:1'],

            'steps.*.tier' => ['required', 'integer', 'min:1'],
            'steps.*.division_id' => ['required', 'integer', 'exists:divisions,id'],
            'steps.*.sla_days' => ['required', 'integer', 'min:1'],
            'steps.*.min_role_level' => ['required'],
        ];
    }


    public function messages(): array
    {
        return [
            'document_type.required' => 'Document Type is required',
            'document_type.max' => 'Max 24 characters allowed',

            'steps.required' => 'At least 1 approval division is required',
            'steps.min' => 'At least 1 approval division is required',

            'steps.*.division_id.required' => 'Division is required',
            'steps.*.division_id.exists' => 'Invalid division selected',
        ];
    }

    protected function failedValidation(Validator $validator)
{
    throw new HttpResponseException(
        redirect()
            ->back()
            ->withInput()
            ->withErrors($validator)
    );
}
    
}