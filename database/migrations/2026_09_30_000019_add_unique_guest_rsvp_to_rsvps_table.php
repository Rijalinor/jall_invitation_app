<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rsvps', function (Blueprint $table) {
            // A token'd guest has exactly one RSVP per invitation. The nullable
            // guest_id keeps anonymous RSVPs unconstrained, since a NULL is
            // distinct from another NULL in a unique index.
            $table->unique(['invitation_id', 'guest_id'], 'rsvps_invitation_guest_unique');
        });
    }

    public function down(): void
    {
        Schema::table('rsvps', function (Blueprint $table) {
            $table->dropUnique('rsvps_invitation_guest_unique');
        });
    }
};
