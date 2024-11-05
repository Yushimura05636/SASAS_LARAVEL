<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonalityUpdateRequest extends FormRequest
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
            'first_name'                 => ['required', 'string'],
            'family_name'                => ['required', 'string'],
            'datetime_registered'        => ['required', 'date'],
            'name_type_code'             => ['required', 'integer'],
            'middle_name'                => ['required', 'string'],
            'birthday'                   => ['required', 'date'],
            'civil_status'               => ['required', 'integer'],
            'gender_code'                => ['required', 'integer'],
            'house_street'               => ['required', 'string'],
            'purok_zone'                 => ['required', 'string'],
            'postal_code'                => ['required', 'string'],
            'telephone_no'               => ['required', 'string'],
            'email_address'              => ['required', 'string'],
            'cellphone_no'               => ['required', 'string'],
            'personality_status_code'    => ['required', 'integer'],
            'barangay_id'                => ['required', 'integer'],
            'city_id'                    => ['required', 'integer'],
            'country_id'                 => ['required', 'integer'],
            'province_id'                => ['required', 'integer'],
            'credit_status_id'           => ['required', 'integer'],
            'notes'                      => ['nullable', 'string'],
        ];
    }
}
