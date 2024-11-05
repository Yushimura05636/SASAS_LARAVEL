<?php

// app/Models/DocumentPermission.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document_Permission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $primarykey = 'id';
    protected $table = 'document_permission';
    protected $fillable = [
        'user_id',
        'document_map_code',
        'document_permission',
        'datetime_granted',
    ];

    /**
     * Get the user account that the document permission belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User_Account::class, 'user_id', 'id');
    }

    /**
     * Get the document map that the document permission belongs to.
     */
    public function documentMap(): BelongsTo
    {
        return $this->belongsTo(Document_Map::class, 'document_map_code', 'id');
    }

    /**
     * Get the document permission map that the document permission belongs to.
     */
    public function documentPermissionMap(): BelongsTo
    {
        return $this->belongsTo(Document_Permission_Map::class, 'document_permission' , 'id');
    }
}
