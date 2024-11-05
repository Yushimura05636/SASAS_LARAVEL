<?php

// app/Models/PaymentLine.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment_Line extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    protected $table = 'payment_line';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_id',
        'payment_schedule_id',
        'balance',
        'amount_paid',
        'remarks',
    ];

    /**
     * Get the payment that owns the payment line.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id');
    }

    /**
     * Get the payment schedule that owns the payment line.
     */
    public function paymentSchedule()
    {
        return $this->belongsTo(Payment_Schedule::class, 'payment_schedule_id', 'id');
    }
}
