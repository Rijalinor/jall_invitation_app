<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'label',
        'date',
        'start_time',
        'end_time',
        'timezone',
        'venue_name',
        'address',
        'map_url',
        'latitude',
        'longitude',
        'parking_notes',
        'entrance_notes',
        'landmark_notes',
        'dress_code',
        'is_primary',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_primary' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
