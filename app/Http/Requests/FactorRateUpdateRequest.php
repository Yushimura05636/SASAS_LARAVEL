<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FactorRateUpdateRequest extends FormRequest
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
            'payment_frequency_id' => ['required', 'integer'],
            'payment_duration_id' => ['required', 'integer'],
            'description' => ['required', 'string'],
            'value' => ['required', 'numeric'],
            'notes' => ['required', 'string'],
        ];
    }
}
