<?php

use App\Models\Invitation;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The first backfill dropped the blocks section at a fixed position, which
     * collided with a section already sitting there. Two rows sharing a position
     * make the display order depend on the database's tie-break, so realign only
     * the invitations where that actually happened and leave the rest alone.
     */
    public function up(): void
    {
        Invitation::query()
            ->whereHas('sections', fn ($query) => $query->where('key', 'blocks'))
            ->each(function (Invitation $invitation): void {
                $sections = $invitation->sections()->orderBy('position')->get();
                $blocks = $sections->firstWhere('key', 'blocks');

                if (! $blocks || $sections->where('position', $blocks->position)->count() < 2) {
                    return;
                }

                $gallery = $sections->firstWhere('key', 'gallery');
                $position = $gallery ? $gallery->position + 1 : ((int) $sections->max('position')) + 1;

                $invitation->sections()
                    ->where('key', '!=', 'blocks')
                    ->where('position', '>=', $position)
                    ->increment('position');

                $blocks->update(['position' => $position]);
            });
    }

    public function down(): void
    {
        // Deliberately not undone: the realignment only removed an ambiguity, and
        // restoring a duplicate position would reintroduce it.
    }
};
