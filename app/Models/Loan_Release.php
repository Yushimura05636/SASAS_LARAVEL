<?php

// app/Models/LoanRelease.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan_Release extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    protected $table = 'loan_release';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'datetime_created',
        'datetime_prepared',
        'passbook_number',
        'loan_application_id',
        'prepared_by_id',
        'datetime_first_due',
        'notes',
    ];

    /**
     * Get the loan application that owns the loan release.
     */
    public function loanApplication()
    {
        return $this->belongsTo(Loan_Application::class, 'loan_application_id', 'id');
    }

    /**
     * Get the user that prepared the loan release.
     */
    public function preparedBy()
    {
        return $this->belongsTo(User_Account::class, 'prepared_by_id', 'id');
    }
}
