<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanApplicationCoMakerUpdateRequest extends FormRequest
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
            'loan_applicaiton_id' => ['required', 'integer'],
            'customer_id' => ['required', 'integer'],
            'encoding_order' => ['required', 'string'],
        ];
    }
}
