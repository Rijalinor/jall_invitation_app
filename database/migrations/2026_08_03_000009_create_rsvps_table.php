<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->onDelete('cascade');
            $table->foreignId('guest_id')->nullable()->constrained('guests')->onDelete('set null');
            $table->string('name');
            $table->string('status', 50)->index();
            $table->integer('party_size')->default(1);
            $table->text('note')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsvps');
    }
};
