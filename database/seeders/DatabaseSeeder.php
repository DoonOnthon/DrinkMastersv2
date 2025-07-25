<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Game;
use App\Models\Level;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        $this->call([
            UniversalCardSeeder::class,  // Create universal deck first
            KingsCupRulesSeeder::class,  // Then add rules for King's Cup
        ]);

        User::firstOrCreate(
            ['email' => 'admin@admin.nl'],
            [
                'name' => 'Admin',
                'password' => bcrypt('admin123'),
                'is_admin' => true,
            ]
        );

        User::factory()->create([
            'name' => 'Troll McSpam',
            'email' => 'troll@example.com',
            'xp' => 42,
            'reports_count' => 3,
        ]);


        Game::insert([
            [
                'title' => "King's Cup",
                'type' => 'card',
                'description' => 'Classic card-based drinking game',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Lord of the Rings',
                'type' => 'movie',
                'description' => 'Drink every time someone says “ring”',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Mario Kart Drinking',
                'type' => 'video',
                'description' => 'Winner drinks water, loser drinks booze!',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
