<?php

// app/Models/LoanApplicationFee.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan_Application_Fees extends Model
{
    use HasFactory;

    protected $primarykey = 'id';
    protected $table = 'loan_application_fees';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'loan_application_id',
        'fee_id',
        'amount',
        'encoding_order',
    ];

    /**
     * Get the loan application that owns the loan application fee.
     */
    public function loanApplication()
    {
        return $this->belongsTo(Loan_Application::class, 'loan_application_id', 'id');
    }

    /**
     * Get the fee that owns the loan application fee.
     */
    public function fee()
    {
        return $this->belongsTo(Fees::class , 'fees_id', 'id');
    }
}
