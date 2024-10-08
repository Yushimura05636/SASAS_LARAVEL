<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanApplicationUpdateRequest extends FormRequest
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
            'customer_id' => ['required', 'integer'],
            'group_id' => ['required', 'integer'],
            'create_at' => ['required', 'date'],
            'datetime_prepared' => ['required', 'date'],
            'document_status_code' => ['required', 'integer'],
            'updated_at' => ['required', 'date'],
            'loan_application_no' => ['required', 'integer'],
            'amount_loan' => ['required', 'numeric'],
            'factor_rate' => ['required', 'numeric'],
            'amount_interest' => ['required', 'numeric'],
            'amount_paid' => ['required', 'numeric'],
            'datetime_target_release' => ['required', 'date'],
            'datetime_fully_paid' => ['required', 'date'],
            'datetime_approved' => ['required', 'date'],
            'payment_frequency_id' => ['required', 'integer'],
            'payment_duration_id' => ['required', 'integer'],
            'approved_by_id' => ['required', 'integer'],
            'prepared_by_id' => ['required', 'integer'],
            'released_by_id' => ['required', 'integer'],
            'last_modified_by_id' => ['required', 'integer'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
