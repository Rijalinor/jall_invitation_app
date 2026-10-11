<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Composite indexes for the public read paths. Each one matches a query that
     * runs on every invitation view or catalogue visit: the approved wish list,
     * the gallery, the RSVP recap and the catalogue sample lookup. The single
     * column indexes already in place cannot serve these as well, because the
     * engine still has to filter or sort the rows they return.
     */
    public function up(): void
    {
        Schema::table('guestbook_entries', function (Blueprint $table) {
            $table->index(['invitation_id', 'moderation_status', 'created_at'], 'guestbook_entries_public_list_index');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->index(['invitation_id', 'type', 'position'], 'media_gallery_index');
        });

        Schema::table('rsvps', function (Blueprint $table) {
            $table->index(['invitation_id', 'status'], 'rsvps_summary_index');
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->index(['is_catalog_demo', 'status', 'id'], 'invitations_catalog_sample_index');
        });
    }

    public function down(): void
    {
        Schema::table('guestbook_entries', function (Blueprint $table) {
            $table->dropIndex('guestbook_entries_public_list_index');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('media_gallery_index');
        });

        Schema::table('rsvps', function (Blueprint $table) {
            $table->dropIndex('rsvps_summary_index');
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->dropIndex('invitations_catalog_sample_index');
        });
    }
};
