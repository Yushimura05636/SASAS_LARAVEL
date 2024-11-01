<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanApplicationFeeResource extends JsonResource
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
            'loan_application_id' => $this->loan_application_id, // Assuming 'loan_application_id' should be mapped from $this->loan_application_id
            'fee_id' => $this->fee_id,                           // Assuming 'fee_id' should be mapped from $this->fee_id
            'amount' => $this->amount,                           // Assuming 'amount' should be mapped from $this->amount
            'encoding_order' => $this->encoding_order,           // Assuming 'encoding_order' should be mapped from $this->encoding_order
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'description' => $this->description,
        ];
    }
}
