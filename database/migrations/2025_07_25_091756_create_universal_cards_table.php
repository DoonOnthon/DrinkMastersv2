<?php
// filepath: database/migrations/2025_07_25_create_universal_cards_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universal_cards', function (Blueprint $table) {
            $table->id();
            $table->string('label'); // "Ace", "King", "2", etc.
            $table->string('suit');  // "♠", "♥", "♦", "♣"
            $table->integer('value'); // 1-13 for sorting/logic
            $table->string('color'); // "red", "black" for additional game logic
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universal_cards');
    }
};
