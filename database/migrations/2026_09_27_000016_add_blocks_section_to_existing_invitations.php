<?php

use App\Models\Invitation;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Blocks became addable before every invitation had the section that renders
     * them, so anything filled in during that window has blocks and no section to
     * show them in. Give those invitations the section; invitations without
     * blocks are untouched, and so is an existing section row.
     */
    public function up(): void
    {
        Invitation::query()
            ->whereHas('blocks')
            ->whereDoesntHave('sections', fn ($query) => $query->where('key', 'blocks'))
            ->each(fn (Invitation $invitation) => $invitation->sections()->create([
                'key' => 'blocks',
                'enabled' => true,
                'position' => 8,
            ]));
    }

    public function down(): void
    {
        // Deliberately not undone: the operator may have given the section its own
        // position since, and deleting it would throw that away.
    }
};
