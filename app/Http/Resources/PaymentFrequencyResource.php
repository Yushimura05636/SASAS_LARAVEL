<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentFrequencyResource extends JsonResource
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
            'description' => $this->description, // This maps 'loan_application_id' to 'description'
            'days_interval' => $this->days_interval,     // Assuming 'days_interval' should be mapped from $this->days_interval
            'notes' => $this->notes,                     // Assuming 'notes' should be mapped from $this->notes
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
