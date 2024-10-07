<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FactorRateResource extends JsonResource
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
            'payment_frequency_id'=> $this->payment_frequency_id,
            'payment_duration_id'=> $this->payment_duration_id,
            'description'=> $this->description,
            'value'=> $this->value,
            'notes'=> $this->notes,
            'payment_frequency' => $this->paymentFrequency,
            'payment_duration' => $this->paymentDuration,
        ];
    }
}
