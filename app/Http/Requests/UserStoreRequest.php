<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
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
            'employee_id' =>    ['nullable', 'integer'],
            'customer_id' =>    ['nullable', 'integer'],
            'last_name' =>      ['required', 'string'],
            'middle_name' =>    ['required', 'string'],
            'email' =>          ['required', 'string'],
            'first_name' =>     ['required', 'string'],
            'password' =>       ['required', 'string'],
            'status_id' =>      ['required', 'integer'],
        ];
    }
}
