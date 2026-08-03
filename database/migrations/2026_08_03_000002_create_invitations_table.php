<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('event_type', 50)->default('wedding')->index();
            $table->string('template_id', 100)->index();
            $table->string('template_version', 20)->nullable();
            $table->string('status', 50)->default('draft')->index();
            $table->json('settings_json')->nullable();
            $table->text('opening_text')->nullable();
            $table->text('closing_message')->nullable();
            $table->string('music_path', 500)->nullable();
            $table->boolean('music_autoplay')->default(true);
            $table->string('livestream_url', 500)->nullable();
            $table->string('livestream_label')->nullable();
            $table->text('share_message')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
