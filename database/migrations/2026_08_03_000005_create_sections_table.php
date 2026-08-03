<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->onDelete('cascade');
            $table->string('key', 100);
            $table->boolean('enabled')->default(true);
            $table->integer('position')->default(0);
            $table->json('content_json')->nullable();
            $table->timestamps();

            $table->unique(['invitation_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
