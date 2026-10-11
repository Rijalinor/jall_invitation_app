<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per public invitation open. A table rather than a counter column,
     * because the operator needs to see which guest link was opened and when,
     * and unique visitors stay a query instead of a growing list of hashes.
     */
    public function up(): void
    {
        Schema::create('invitation_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            // Set only when the open came through a personal guest link. Nulled
            // rather than removed when a guest is deleted, so the count survives.
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            // A salted hash, never the raw address: enough to count a unique
            // visitor, useless for identifying one.
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('referrer', 255)->nullable();
            $table->timestamps();

            // The report reads one invitation newest-first and counts distinct
            // hashes, so both paths get an index.
            $table->index(['invitation_id', 'created_at'], 'invitation_visits_recent_index');
            $table->index(['invitation_id', 'ip_hash'], 'invitation_visits_unique_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_visits');
    }
};
