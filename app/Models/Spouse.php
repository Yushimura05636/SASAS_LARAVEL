<?php

// app/Models/Spouse.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spouse extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primarykey = 'id';
    protected $table = 'spouse';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'family_name',
        'first_name',
        'middle_name',
        'gender_code',
        'email_address',
        'cellphone_no',
    ];
}
