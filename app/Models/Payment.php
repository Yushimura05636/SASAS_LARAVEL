<?php

// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $table = 'payment';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'prepared_at',
        'document_status_code',
        'prepared_by_id',
        'amount_paid',
        'notes',
    ];

    /**
     * Get the customer that owns the payment.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * Get the user that prepared the payment.
     */
    public function preparedBy()
    {
        return $this->belongsTo(User_Account::class, 'prepared_by_id', 'id');
    }
}
