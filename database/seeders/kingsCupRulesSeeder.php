<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Game;
use App\Models\GameRule;

class KingsCupRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $game = Game::where('title', "King's Cup")->first();

        if (!$game) {
            $game = Game::create([
                'title' => "King's Cup",
                'type' => 'card',
                'description' => 'Classic drinking game with universal rules'
            ]);
        }

        $rules = [
            1 => ['name' => 'Waterfall', 'action' => 'Everyone starts drinking, can\'t stop until person to your right stops', 'category' => 'drinking'],
            2 => ['name' => 'You', 'action' => 'Pick someone to drink', 'category' => 'social'],
            3 => ['name' => 'Me', 'action' => 'Take 3', 'category' => 'drinking'],
            4 => ['name' => 'Whores', 'action' => 'Girls drink', 'category' => 'social'],
            5 => ['name' => 'Nominate', 'action' => ' Nominate someone to drink double until the next 5 is drawn', 'category' => 'drinking'],
            6 => ['name' => 'Dicks', 'action' => 'All guys drink', 'category' => 'drinking'],
            7 => ['name' => 'Heaven', 'action' => 'Everyone points up, last person drinks', 'category' => 'social'],
            8 => ['name' => 'Mate', 'action' => 'Pick a drinking buddy, they drink when you drink', 'category' => 'social'],
            9 => ['name' => 'Last to drink', 'action' => 'Last person to take a drink, Takes a Shot', 'category' => 'social'],
            10 => ['name' => 'Everyone', 'action' => 'Everyone drinks', 'category' => 'social'],
            11 => ['name' => 'Make a rule', 'action' => 'Make a Rule. Some Examples are Drinks are Doubled, You Can Only Drink with Your Left Hand, You Can Only Say Last Names.', 'category' => 'social'],
            12 => ['name' => 'Question Master', 'action' => 'Upon drawing this card, You are the Question Master. If someone answers any of your questions, They Drink. If they say "Fuck You Question Master", Then You drink', 'category' => 'social'],
            13 => ['name' => 'King\'s Cup', 'action' => 'Every King is 1 Shot. The Game will Finish After the 4th King is drawn. (1 shot is equal to 10 drinks)', 'category' => 'drinking']
        ];

        foreach ($rules as $value => $ruleData) {
            GameRule::create([
                'game_id' => $game->id,
                'card_value' => (string) $value,
                'name' => $ruleData['name'],
                'description' => $ruleData['action'],
                'action_text' => $ruleData['name'],
                'category' => $ruleData['category'],
                'intensity' => $value === 1 || $value === 13 ? 'high' : 'medium'
            ]);
        }
    }
}
