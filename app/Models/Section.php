<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'key',
        'enabled',
        'position',
        'content_json',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'content_json' => 'array',
        ];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    /**
     * The next free key for a section the operator adds.
     *
     * Keys are unique per invitation, so taking the first number nothing uses means
     * deleting a section and adding another cannot collide with an existing one.
     */
    public static function nextCustomKey(Invitation $invitation): string
    {
        $taken = $invitation->sections()
            ->where('key', 'like', 'blocks:%')
            ->pluck('key')
            ->map(fn (string $key): int => (int) substr($key, 7))
            ->all();

        $number = 2;

        while (in_array($number, $taken, true)) {
            $number++;
        }

        return 'blocks:'.$number;
    }
}
