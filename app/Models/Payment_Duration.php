<?php

// app/Models/PaymentDuration.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment_Duration extends Model
{
    use HasFactory;

    protected $primarykey = 'id';
    protected $table = 'payment_duration';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'number_of_payments',
        'notes',
    ];

    /**
     * Get the factor rates that belong to the payment duration.
     */
    public function factorRates()
    {
        return $this->hasMany(Factor_Rate::class);
    }
}
