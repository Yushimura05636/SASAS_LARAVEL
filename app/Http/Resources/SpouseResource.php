<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpouseResource extends JsonResource
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
            'family_name' => $this->family_name,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'gender_code' => $this->gender_code,
            'email_address' => $this->email_address,
            'cellphone_no' => $this->cellphone_no,
        ];
    }
}
