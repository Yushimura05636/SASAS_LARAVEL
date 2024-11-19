<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ParagonIE\Sodium\Core\Curve25519\H;

class User_Account extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $primarykey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'user_account';
    protected $fillable = [
        'customer_id',
        'email',
        'phone_number',
        'last_name',
        'first_name',
        'middle_name',
        'password',
        'status_id',
        'datetime_registered',
        'employee_id',
    ];

    protected $casts = [
        'two_factor_expires_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user account status that the user account belongs to.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(User_Account_Status::class, 'status_id');
    }

    /**
     * Get the employee that the user account belongs to.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function documentPermission(): HasMany
    {
        return $this->hasMany(Document_Permission::class, 'id', 'user_id');
    }
}
