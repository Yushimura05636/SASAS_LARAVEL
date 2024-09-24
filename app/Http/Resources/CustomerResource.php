<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'group_id' => $this->group_id,
            'passbook_no' => $this->passbook_no,
            'loan_count' => $this->loan_count,
            'enable_mortuary' => $this->enable_mortuary,
            'mortuary_coverage_start' => $this->mortuary_coverate_start,
            'mortuary_coverage_end' => $this->mortuary_coverage_end,
            'personality_id' => $this->personality_id,
        ];
    }
}
