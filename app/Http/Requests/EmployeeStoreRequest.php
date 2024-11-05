<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeStoreRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sss_no' => ['required', 'integer'],
            'phic_no' => ['required', 'integer'],
            'tin_no' => ['required', 'integer'],
            'date_hired' => ['nullable', 'date'],
            'date_resigned' => ['nullable', 'date'],
            'personality_id' => ['required', 'integer']
        ];
    }
}
