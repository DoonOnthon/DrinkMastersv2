<?php
use App\Models\Game;
use Illuminate\Support\Str;

return [
    'title' => fake()->words(2, true),
    'type' => fake()->randomElement(['card', 'movie', 'video']),
    'description' => fake()->sentence(),
];
