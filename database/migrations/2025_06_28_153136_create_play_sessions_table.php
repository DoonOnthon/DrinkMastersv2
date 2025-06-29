<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('play_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('host_user_id')->nullable()->constrained('users');
            $table->string('code')->unique(); // for join links
            $table->boolean('is_public')->default(true); // allow joining via code
            $table->json('state')->nullable(); // cards drawn, turn index, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('play_sessions');
    }
};
