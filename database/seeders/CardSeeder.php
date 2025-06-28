<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Game;
use App\Models\Card;

class CardSeeder extends Seeder
{
    public function run()
    {
        $game = Game::firstOrCreate([
            'title' => "King's Cup",
            'type' => 'card',
            'description' => 'Classic drinking game played with a full 52-card deck.'
        ]);

        $suits = ['♠', '♥', '♦', '♣'];
        $values = [
            1 => ['label' => 'Ace', 'action' => 'Waterfall'],
            2 => ['label' => '2', 'action' => 'You'],
            3 => ['label' => '3', 'action' => 'Me'],
            4 => ['label' => '4', 'action' => 'Floor'],
            5 => ['label' => '5', 'action' => 'Guys'],
            6 => ['label' => '6', 'action' => 'Chicks'],
            7 => ['label' => '7', 'action' => 'Heaven'],
            8 => ['label' => '8', 'action' => 'Mate'],
            9 => ['label' => '9', 'action' => 'Rhyme'],
            10 => ['label' => '10', 'action' => 'Categories'],
            11 => ['label' => 'Jack', 'action' => 'Never Have I Ever'],
            12 => ['label' => 'Queen', 'action' => 'Questions'],
            13 => ['label' => 'King', 'action' => 'Make a Rule'],
        ];

        foreach ($suits as $suit) {
            foreach ($values as $value => $info) {
                Card::create([
                    'game_id' => $game->id,
                    'label' => $info['label'],
                    'suit' => $suit,
                    'value' => $value,
                    'action_text' => $info['action'],
                ]);
            }
        }
    }
}
