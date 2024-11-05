<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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
            'prepared_at' => ['required', 'date'],
            'document_status_code' => ['required', 'integer'],
            'prepared_by_id' => ['required', 'integer'],
            'amount_paid' => ['required', 'integer'],
            'notes' => ['required', 'string'],
        ];
    }
}
