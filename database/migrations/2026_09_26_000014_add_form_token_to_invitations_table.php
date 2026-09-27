<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A per-invitation credential that lets the customer fill in their own
     * invitation content without an account. Only the hash is stored, so a
     * database leak does not hand out write access to live invitations.
     */
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('form_token_hash', 64)->nullable()->unique()->after('is_catalog_demo');
            $table->timestamp('form_token_expires_at')->nullable()->after('form_token_hash');
            $table->timestamp('form_token_used_at')->nullable()->after('form_token_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropUnique('invitations_form_token_hash_unique');
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['form_token_hash', 'form_token_expires_at', 'form_token_used_at']);
        });
    }
};
