<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Loan_Count extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    protected $table = 'loan_count';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'loan_count',
        'min_amount',
        'max_amount',
    ];

    /**
     * Get the loan application that owns the loan release.
     */

    public function loanCount(): HasOne
    {
        return $this->hasOne(Loan_Count::class, 'id', 'id');
    }
}
