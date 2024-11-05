<?php

// app/Models/UserAccountStatus.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_Account_Status extends DBLibrary
{
    use HasFactory;

    protected $primarykey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = "user_account_status";
    protected $fillable = [
        'description',
    ];
}
