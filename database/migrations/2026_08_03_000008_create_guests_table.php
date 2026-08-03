<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->onDelete('cascade');
            $table->string('token', 100)->unique();
            $table->string('display_name');
            $table->string('group')->nullable()->index();
            $table->string('phone', 50)->nullable();
            $table->integer('invitation_limit')->default(2);
            $table->boolean('has_attended')->nullable();
            $table->timestamp('link_opened_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
