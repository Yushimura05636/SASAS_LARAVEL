<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanReleaseResource extends JsonResource
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
            'datetime_created' => $this->datetime_created,
            'datetime_prepared' => $this->datetime_prepared,
            'passbook_number' => $this->passbook_number,
            'loan_application_id' => $this->loan_application_id,
            'prepared_by_id' => $this->prepared_by_id,
            'datetime_first_due' => $this->datetime_first_due,
            'notes' => $this->notes,
        ];
    }
}
