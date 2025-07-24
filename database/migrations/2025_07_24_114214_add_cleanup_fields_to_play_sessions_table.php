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
        Schema::table('play_sessions', function (Blueprint $table) {
            $table->timestamp('last_activity')->nullable()->after('state');
            $table->timestamp('completed_at')->nullable()->after('last_activity');
            $table->boolean('auto_cleanup')->default(true)->after('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('play_sessions', function (Blueprint $table) {
            $table->dropColumn(['last_activity', 'completed_at', 'auto_cleanup']);
        });
    }
};
