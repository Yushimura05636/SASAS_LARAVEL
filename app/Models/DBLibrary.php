<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

abstract class DBLibrary extends Model
{
    // Define common fields
    protected $fillable = ['description'];
    protected $id;

    // Common methods can be defined here
    public static function createEntry(string $description)
    {
        return self::create(['description' => $description]);
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
        $entry = self::paginate(10);
        return $entry;
    }

    public static function updateEntry($id = null, $description = null)
    {
        $entry = self::findOrFail($id);
        $entry->description = $description;
        $entry->save();
        return $entry;
    }
}
