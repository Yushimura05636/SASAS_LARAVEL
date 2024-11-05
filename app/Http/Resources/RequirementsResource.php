<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequirementsResource extends JsonResource
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
            'isActive' => $this->isActive,     // Assuming 'isactive' should be mapped from $this->isactive
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
