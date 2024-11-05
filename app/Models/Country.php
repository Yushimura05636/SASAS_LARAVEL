<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends DBLibrary
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primarykey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'country';
    protected $fillable = [
        'description',
    ];
}

