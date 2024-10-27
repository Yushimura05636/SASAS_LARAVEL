<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'datetime_registered' => $this->datetime_registered,
            'family_name' => $this->family_name,
            'middle_name' => $this->middle_name,
            'first_name' => $this->first_name,
            //'description' => $this->description,
            'birthday' => $this->birthday,
            'civil_status' => $this->civil_status,
            'gender_code' => $this->gender_code,
            'house_street' => $this->house_street,
            'purok_zone' => $this->purok_zone,
            'postal_code' => $this->postal_code,
            'telephone_no' => $this->telephone_no,
            'email_address' => $this->email_address,
            'cellphone_no' => $this->cellphone_no,
            'name_type_code' => $this->name_type_code,
            'personality_status_code' => $this->personality_status_code,
            //'branch_id' => $this->branch_id,
            'barangay_id' => $this->barangay_id,
            'city_id' => $this->city_id,
            'country_id' => $this->country_id,
            'province_id' => $this->province_id,
            //'spouse_id' => $this->spouse_id,
            'credit_status_id' => $this->credit_status_id,

            //unique
            'personality_status_description' => $this->personality_status_description,
        ];
    }
}
