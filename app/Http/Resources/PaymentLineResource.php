<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentLineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id' => $this->id,
            'payment_id' => $this->payment_id,
            'payment_schedule_id' => $this->payment_schedule_id,
            'balance' => $this->balance,
            'amount_paid' => $this->amount_paid,
            'remarks' => $this->remarks,
        ];
    }
}
