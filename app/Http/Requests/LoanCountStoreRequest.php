<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanCountStoreRequest extends FormRequest
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
            "loan_count" => ['required', 'integer'],
            "min_amount" => ['required', 'numeric', 'regex:/^\d{1,6}(\.\d{1,8})?$/'],
            "max_amount" => ['required', 'numeric', 'regex:/^\d{1,6}(\.\d{1,8})?$/'],
        ];
    }
}
