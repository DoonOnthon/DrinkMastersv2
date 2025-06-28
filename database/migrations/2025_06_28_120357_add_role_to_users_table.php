<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('email_verified_at');
            $table->boolean('is_moderator')->default(false)->after('is_admin');
            $table->boolean('is_manager')->default(false)->after('is_moderator');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'is_moderator', 'is_manager']);
        });
    }
};
// This migration adds role columns to the users table for admin, moderator, and manager roles.
