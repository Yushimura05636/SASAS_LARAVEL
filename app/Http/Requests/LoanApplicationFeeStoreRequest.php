<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanApplicationFeeStoreRequest extends FormRequest
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
            'loan_application_id' => ['required', 'integer'], // Changed to 'integer' if 'loan_application_id' is numeric
            'fee_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric'],              // Assuming 'amount' should be a numeric value
            'encoding_order' => ['nullable', 'string'],       // Assuming 'encoding_order' should be an optional string
        ];
    }
}
