<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'group_id' => $this->group_id,
            'passbook_no' => $this->passbook_no,
            'loan_count' => $this->loan_count,
            // 'enable_mortuary' => $this->enable_mortuary,
            // 'mortuary_coverage_start' => $this->mortuary_coverage_start,
            // 'mortuary_coverage_end' => $this->mortuary_coverage_end,
            'personality_id' => $this->personality_id,
            'personality' => [
                'first_name' => $this->personality->first_name, 
                'middle_name' => $this->personality->middle_name,   // Access personality first_name
                'family_name' => $this->personality->family_name,
                'email_address' => $this->personality->email_address,     // Access personality last_name
                'cellphone_no' => $this->personality->cellphone_no,     // Access personality last_name
                // Add other personality fields if needed
            ],
        ];
    }
}
