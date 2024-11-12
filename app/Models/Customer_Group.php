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
        'customer_id',
    ];

    public static function createEntry(string $description, int $collector_id = null)
    {
        return self::create(['description' => $description, 'collector_id' => $collector_id]);
    }

    public static function deleteEntry(int $id)
    {
        $entry = self::find($id);
        if ($entry) {
            $entry->delete();
            return true;
        }
        return false;
    }

    public static function findOne(int $id)
    {
        return self::where('id', $id)->first();
    }

    public static function findMany()
    {
        $entry = self::get();
        return $entry;
    }

    public static function updateEntry($id = null, $description = null, $collector_id = null)
    {
        $entry = self::findOrFail($id);
        $entry->description = $description;
        $entry->collector_id = $collector_id;
        $entry->save();
        return $entry;
    }

    /**
     * Get the customers that belong to the customer group.
     */
    public function customers()
    {
        return $this->hasMany(Customer::class, 'customer_id', 'id');
    }
}
