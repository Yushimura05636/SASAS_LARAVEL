<?php

// app/Models/Personality.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class Personality extends Model
{
    use HasFactory, HasApiTokens;

    protected $primarykey = 'id';
    protected $table = 'personality';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name_type_code',
        'family_name',
        'middle_name',
        'first_name',
        'birthday',
        'civil_status',
        'gender_code',
        'house_street',
        'purok_zone',
        'postal_code',
        'telephone_no',
        'email_address',
        'cellphone_no',
        'personality_status_code',
        'barangay_id',
        'city_id',
        'country_id',
        'province_id',
        'credit_status_id',
        'notes',
    ];

    /**
     * Get the branch that the personality belongs to.
     */
    // public function branch(): BelongsTo
    // {
    //     return $this->belongsTo(Branch::class, 'branch_id', 'id');
    // }

    /**
     * Get the barangay that the personality belongs to.
     */
    // public function barangay(): BelongsTo
    // {
    //     return $this->belongsTo(Barangay::class, 'barangay_id', 'id');
    // }

    /**
     * Get the city that the personality belongs to.
     */
    // public function city(): BelongsTo
    // {
    //     return $this->belongsTo(City::class, 'city_id', 'id');
    // }

    // /**
    //  * Get the country that the personality belongs to.
    //  */
    // public function country(): BelongsTo
    // {
    //     return $this->belongsTo(Country::class, 'country_id', 'id');
    // }

    // /**
    //  * Get the province that the personality belongs to.
    //  */
    // public function province(): BelongsTo
    // {
    //     return $this->belongsTo(Province::class, 'province_id', 'id');
    // }

    // /**
    //  * Get the spouse of the personality.
    //  */
    // public function spouse(): BelongsTo
    // {
    //     return $this->belongsTo(Personality::class, 'personality_id', 'id');
    // }

    // /**
    //  * Get the credit status of the personality.
    //  */
    // public function creditStatus(): BelongsTo
    // {
    //     return $this->belongsTo(Credit_Status::class, 'credit_status_id', 'id');
    // }

    // /**
    //  * Get the personality status of the personality.
    //  */
    // public function personalityStatus(): BelongsTo
    // {
    //     return $this->belongsTo(Personality_Status_Map::class, 'personality_status_map', 'id');
    // }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class,'personality_id', 'id');
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class,'personality_id', 'id');
    }
}
