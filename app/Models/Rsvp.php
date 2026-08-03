<?php

namespace App\Models;

use App\Enums\RsvpStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rsvp extends Model
{
    use HasFactory;

    protected $table = 'rsvps';

    protected $fillable = [
        'invitation_id',
        'guest_id',
        'name',
        'status',
        'party_size',
        'note',
        'ip_address',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RsvpStatus::class,
            'submitted_at' => 'datetime',
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
