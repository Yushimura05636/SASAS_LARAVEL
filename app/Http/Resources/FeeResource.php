<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeResource extends JsonResource
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
            'description' => $this->description,
            'amount' => $this->amount,         // Assuming 'amount' should be mapped from $this->amount
            'isactive' => $this->isactive,     // Assuming 'isactive' should be mapped from $this->isactive
            'notes' => $this->notes,           // Assuming 'notes' should be mapped from $this->notes
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
