<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DBLibraryResource extends JsonResource
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
            'collector_id' => $this->collector_id,
            'first_name' => $this->first_name ?? null,
            'last_name' => $this->last_name ?? null,
            'middle_name' => $this->middle_name ?? null,
        ];
    }
}
