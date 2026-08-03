<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->onDelete('cascade');
            $table->string('type', 50);
            $table->string('provider', 100)->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('notes')->nullable();
            $table->integer('position')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_methods');
    }
};
