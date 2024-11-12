<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'sss_no' => $this->sss_no,
            'phic_no' => $this->phic_no,
            'tin_no' => $this->tin_no,
            'datetime_hired' => $this->datetime_hired,
            'datetime_resigned' => $this->datetime_hired,
            'personality_id' => $this->personality_id,
            'name_type' => $this->name_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
