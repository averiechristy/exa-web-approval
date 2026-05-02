<?php

namespace App\Http\Requests;

use DB;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DivisionRequest extends FormRequest
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
            'division_name' => [
                'min:1',
                'max:24',
                'required',
                'regex:/^[A-Za-z\s]+$/',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('divisions')
                        ->whereNull('deleted_at')
                        ->whereRaw('LOWER(division_name) = ?', [strtolower($value)])
                        ->when($this->route('division'), function ($query) {
                            $query->where('id', '!=', $this->route('division')->id);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('The division name has already been taken.');
                    }
                },
            ],
        ];
    }
}
