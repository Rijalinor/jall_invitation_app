<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Host extends Model
{
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'role',
        'name',
        'nickname',
        'photo_path',
        'bio',
        'birth_order',
        'parent_father',
        'parent_mother',
        'social_instagram',
        'social_tiktok',
        'position',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
