<?php

// app/Models/Employee.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'employee';
    protected $fillable = [
        'sss_no',
        'phic_no',
        'tin_no',
        'datetime_hired',
        'datetime_resigned',
        'personality_id',
    ];

    public function personality(): BelongsTo
    {
        return $this->belongsTo(Personality::class, 'personality_id', 'id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User_Account::class, 'id', 'employee_id');
    }
}
