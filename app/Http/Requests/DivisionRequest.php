<?php

namespace App\Http\Requests;

use DB;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
                        ->whereRaw(
                            'LOWER(division_name) = ?',
                            [strtolower($value)]
                        )
                        ->when($this->route('division'), function ($query) {
                            $query->where(
                                'id',
                                '!=',
                                $this->route('division')->id
                            );
                        })
                        ->exists();

                    if ($exists) {
                        $fail('The division name has already been taken.');
                    }
                },
            ],
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