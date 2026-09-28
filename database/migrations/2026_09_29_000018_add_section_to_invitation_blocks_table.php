<?php

use App\Models\Invitation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Blocks belonged to the invitation, so every extra section an operator built
     * ended up in one pile that could not be moved on its own. A block belongs to
     * a section now, which is what lets each one be placed separately.
     *
     * Existing blocks move to the section that already renders them, so nothing
     * changes for an invitation until a second section is added.
     */
    public function up(): void
    {
        Schema::table('invitation_blocks', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('invitation_id')
                ->constrained('sections')->cascadeOnDelete();
        });

        Invitation::query()->whereHas('blocks')->each(function (Invitation $invitation): void {
            $section = $invitation->sections()->where('key', 'blocks')->first()
                ?? $invitation->sections()->create(['key' => 'blocks', 'enabled' => true, 'position' => 8]);

            $invitation->blocks()->update(['section_id' => $section->id]);
        });
    }

    public function down(): void
    {
        Schema::table('invitation_blocks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('section_id');
        });
    }
};
