<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerRequirementResource extends JsonResource
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
            'customer_id' => $this->customer_id,
            'requirement_id' => $this->requirement_id,     // Assuming 'isactive' should be mapped from $this->isactive
            'expiry_date' => $this->expiry_date,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
