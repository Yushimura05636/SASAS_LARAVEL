<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'family_name' => $this->family_name,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'prepared_at' => $this->prepared_at,
            'document_status_code' => $this->document_status_code,
            'prepared_by_id' => $this->prepared_by_id,
            'amount_paid' => $this->amount_paid,
            'notes' => $this->notes,
        ];
    }
}
