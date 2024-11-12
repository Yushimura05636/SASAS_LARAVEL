<?php

// app/Models/PaymentSchedule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment_Schedule extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    protected $table = 'payment_schedule';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'datetime_created',
        'customer_id',
        'loan_released_id',
        'datetime_due',
        'amount_due',
        'amount_interest',
        'amount_paid',
        'payment_status_code',
        'remarks',
    ];

    /**
     * Get the customer that owns the payment schedule.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * Get the loan release that owns the payment schedule.
     */
    public function loanRelease()
    {
        return $this->belongsTo(Loan_Release::class, 'loan_released_id', 'id');
    }

    public function paymentLine(): HasMany
    {
        return $this->hasMany(Payment_Line::class, 'id', 'payment_schedule_id');
    }
}
