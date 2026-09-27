<?php

namespace App\Models;

use App\Enums\BlockType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'type',
        'content_json',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'type' => BlockType::class,
            'content_json' => 'array',
        ];
    }

    protected static function booted(): void
    {
        /**
         * A block only reaches a guest if the template renders the section that
         * holds it, so creating one has to make sure that section exists. Without
         * this the operator has to know to add a section separately, and blocks
         * they filled in never appear.
         */
        static::created(function (self $block): void {
            $invitation = $block->invitation;

            // A section that already exists is left untouched: switching it off is
            // a deliberate choice, and re-enabling it here would silently undo that.
            if (! $invitation || $invitation->sections()->where('key', 'blocks')->exists()) {
                return;
            }

            if ($invitation->sections()->doesntExist()) {
                // With no rows at all a template renders every section it declares,
                // so adding a single row would hide all the others. Seed the whole
                // default list instead, exactly like creating the invitation does.
                foreach (Invitation::defaultSectionKeys() as $index => $key) {
                    $invitation->sections()->create(['key' => $key, 'enabled' => true, 'position' => $index]);
                }

                return;
            }

            // Sit it where the templates put it: straight after the gallery, pushing
            // the later sections down so two rows never share a position.
            $gallery = $invitation->sections()->where('key', 'gallery')->value('position');
            $position = $gallery === null ? ((int) $invitation->sections()->max('position')) + 1 : $gallery + 1;

            $invitation->sections()->where('position', '>=', $position)->increment('position');
            $invitation->sections()->create(['key' => 'blocks', 'enabled' => true, 'position' => $position]);
        });
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
