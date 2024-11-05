<?php

// app/Models/LoanApplication.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan_Application extends Model
{
    use HasFactory;

    protected $primarykey = 'id';
    protected $table = 'loan_application';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'group_id',
        'document_status_code',
        'loan_application_no',
        'amount_loan',
        'factor_rate',
        'amount_interest',
        'amount_paid',
        'datetime_target_release',
        'datetime_fully_paid',
        'datetime_approved',
        'payment_frequency_id',
        'payment_duration_id',
        'approved_by_id',
        'prepared_by_id',
        'released_by_id',
        'last_modified_by_id',
        'notes',
    ];

    /**
     * Get the customer that the loan application belongs to.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * Get the group that the loan application belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Customer_Group::class, 'group_id', 'id');
    }

    /**
     * Get the payment frequency of the loan application.
     */
    public function paymentFrequency(): BelongsTo
    {
        return $this->belongsTo(Payment_Frequency::class, 'payment_frequency_id', 'id');
    }

    /**
     * Get the payment duration of the loan application.
     */
    public function paymentDuration(): BelongsTo
    {
        return $this->belongsTo(Payment_Duration::class, 'payment_duration_id', 'id');
    }

    /**
     * Get the user who approved the loan application.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User_Account::class, 'approved_by_id', 'id');
    }

    /**
     * Get the user who prepared the loan application.
     */
    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User_Account::class, 'prepared_by_id', 'id');
    }

    /**
     * Get the user who released the loan application.
     */
    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User_Account::class, 'released_by_id', 'id');
    }

    /**
     * Get the user who last modified the loan application.
     */
    public function lastModifiedBy(): BelongsTo
    {
        return $this->belongsTo(User_Account::class, 'last_modified_by_id', 'id');
    }
}
