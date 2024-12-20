<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanApplicationResource extends JsonResource
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
            'group_id' => $this->group_id,
            'datetime_prepared' => $this->datetime_prepared,
            'document_status_code' => $this->document_status_code,
            'document_status_description' => $this->document_status_description,
            'loan_application_no' => $this->loan_application_no,
            'amount_loan' => $this->amount_loan,
            'factor_rate' => $this->factor_rate,
            'amount_interest' => $this->amount_interest,
            'amount_paid' => $this->amount_paid,
            'datetime_target_release' => $this->datetime_target_release,
            'datetime_fully_paid' => $this->datetime_fully_paid,
            'datetime_approved' => $this->datetime_approved,
            'datetime_rejected' => $this->datetime_rejected,
            'payment_frequency_id' => $this->payment_frequency_id,
            'payment_duration_id' => $this->payment_duration_id,
            'approved_by_id' => $this->approved_by_id,
            'prepared_by_id' => $this->prepared_by_id,
            'released_by_id' => $this->released_by_id,
            'rejected_by_id' => $this->rejected_by_id,
            'last_modified_by_id' => $this->last_modified_by_id,
            'notes' => $this->notes,
            'factor_rate_value' => $this->factor_rate_value,
            'family_name' => $this->family_name,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'approved_by_user' => $this->approved_by_user,
            'rejected_by_user' => $this->rejected_by_user,
            'prepared_by_user' => $this->prepared_by_user,
            'released_by_user' => $this->released_by_user,
            'last_modified_by_user' => $this->last_modified_by_user,
        ];
    }
}
