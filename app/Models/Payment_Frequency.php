<?php

// app/Models/PaymentFrequency.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment_Frequency extends Model
{
    use HasFactory;

    protected $primarykey = 'id';
    protected $table = 'payment_frequency';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'days_interval',
        'notes',
    ];

    public function factorRate():HasMany
    {
        return $this->hasMany(Factor_Rate::class, 'id', 'payment_frequency_id');
    }
}
