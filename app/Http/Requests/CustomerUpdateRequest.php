<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerUpdateRequest extends FormRequest
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
            'group_id' => ['required', 'integer'],
            'passbook_no' => ['required', 'integer'],
            'loan_count' => ['required', 'integer'],
            'enable_mortuary' => ['nullable', 'integer'],
            'mortuary_coverage_start' => ['nullable', 'date'],
            'motuary_coverage_end' => ['nullable', 'date'],
            'personality_id' => ['nullable', 'integer'],
        ];
    }
}
