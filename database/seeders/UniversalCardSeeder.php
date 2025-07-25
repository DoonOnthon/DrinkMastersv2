<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UniversalCard;

class UniversalCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suits = [
            ['symbol' => '♠', 'name' => 'Spades', 'color' => 'black'],
            ['symbol' => '♥', 'name' => 'Hearts', 'color' => 'red'],
            ['symbol' => '♦', 'name' => 'Diamonds', 'color' => 'red'],
            ['symbol' => '♣', 'name' => 'Clubs', 'color' => 'black'],
        ];

        $values = [
            1 => 'Ace', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7',
            8 => '8', 9 => '9', 10 => '10', 11 => 'Jack', 12 => 'Queen', 13 => 'King'
        ];

        foreach ($suits as $suit) {
            foreach ($values as $value => $label) {
                UniversalCard::create([
                    'label' => $label,
                    'suit' => $suit['symbol'],
                    'value' => $value,
                    'color' => $suit['color']
                ]);
            }
        }
    }
}
