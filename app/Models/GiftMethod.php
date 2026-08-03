<?php

namespace App\Models;

use App\Enums\GiftMethodType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'type',
        'provider',
        'account_name',
        'account_number',
        'delivery_address',
        'notes',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'type' => GiftMethodType::class,
        ];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
