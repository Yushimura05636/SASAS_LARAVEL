<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentScheduleStoreRequest extends FormRequest
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
        'datetime_created' => ['required', 'date'],
        'customer_id' => ['required', 'integer'],
        'loan_released_id' => ['required', 'integer'],
        'datetime_due' => ['required', 'date'],
        'amount_due' => ['required', 'date'],
        'amount_interest' => ['required', 'integer'],
        'amount_paid' => ['required', 'integer'],
        'payment_status_code' => ['required', 'integer'],
        'remarks' => ['required', 'string'],
        ];
    }
}
