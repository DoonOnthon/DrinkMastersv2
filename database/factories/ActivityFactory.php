<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'description' => fake()->randomElement([
                'Logged in',
                'Played a game',
                'Updated profile',
                'Joined a tournament',
                'Reported a bug',
                'Earned XP',
                'Made a purchase',
                'Sent feedback',
            ]),
            'created_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'updated_at' => now(),
        ];
    }
}
