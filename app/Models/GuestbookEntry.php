<?php

namespace App\Models;

use App\Enums\ModerationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestbookEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'guest_id',
        'name',
        'message',
        'moderation_status',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'moderation_status' => ModerationStatus::class,
        ];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
