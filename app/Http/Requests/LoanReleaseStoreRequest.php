<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanReleaseStoreRequest extends FormRequest
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
        'datetime_created' =>['required', 'date'],
        'datetime_prepared' =>['required', 'date'],
        'passbook_number' =>['required', 'integer'],
        'loan_application_id' =>['required', 'integer'],
        'prepared_by_id' =>['required', 'integer'],
        'datetime_first_due' =>['required', 'date'],
        'notes' =>['required','string' ],
        ];
    }
}
