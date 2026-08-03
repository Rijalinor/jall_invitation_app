<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->onDelete('cascade');
            $table->string('role', 50);
            $table->string('name');
            $table->string('nickname', 100)->nullable();
            $table->string('photo_path', 500)->nullable();
            $table->text('bio')->nullable();
            $table->string('birth_order', 100)->nullable();
            $table->string('parent_father')->nullable();
            $table->string('parent_mother')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_tiktok')->nullable();
            $table->integer('position')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosts');
    }
};
