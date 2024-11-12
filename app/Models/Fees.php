<?php

// app/Models/Fee.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fees extends Model
{
    use HasFactory;

    protected $primarykey = 'id';
    protected $table = 'fees';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'amount',
        'isactive',
        'notes',
    ];

    public function fees(): HasMany
    {
        return $this->hasMany(Loan_Application_Fees::class, 'id', 'fee_id');
    }
}
