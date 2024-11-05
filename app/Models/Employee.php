<?php

// app/Models/Employee.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        return $this->belongsTo(Employee::class, 'personality_id', 'id');
    }
}
