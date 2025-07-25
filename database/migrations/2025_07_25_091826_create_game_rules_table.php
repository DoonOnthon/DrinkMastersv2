<?php
// filepath: database/migrations/2025_07_25_create_game_rules_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->string('card_value'); // "1", "11", "12", "13" (can match multiple cards)
            $table->string('name'); // "Waterfall", "Categories", etc.
            $table->text('description'); // Full rule explanation
            $table->string('action_text'); // Short text shown on card
            $table->string('category')->nullable(); // "drinking", "social", "dare"
            $table->enum('intensity', ['low', 'medium', 'high'])->default('medium');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_rules');
    }
};
