<?php

// app/Models/CustomerGroup.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer_Group extends DBLibrary
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $table = 'customer_group';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
    ];

    /**
     * Get the customers that belong to the customer group.
     */
    public function customers()
    {
        return $this->hasMany(Customer::class, 'customer_id', 'id');
    }
}
