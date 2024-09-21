<?php

// app/Models/Customer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    protected $table = 'customer';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'passbook_no',
        'loan_count',
        'enable_motuary',
        'motuary_coverage_start',
        'mortuary_coverage_end',
        'personality_id',
    ];

    /**
     * Get the customer group that owns the customer.
     */
    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(Customer_Group::class, 'group_id', 'id');
    }

    public function personality(): BelongsTo
    {
        return $this->belongsTo(Personality::class,'personality_id', 'id');
    }
}
