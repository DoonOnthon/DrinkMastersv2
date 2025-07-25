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

        echo "✅ Game found: {$game->title} (ID: {$game->id})\n";

        // Clear existing rules first
        $existingCount = GameRule::where('game_id', $game->id)->count();
        if ($existingCount > 0) {
            GameRule::where('game_id', $game->id)->delete();
            echo "🗑️ Deleted {$existingCount} existing rules\n";
        }

        $rules = [
            1 => [
                'name' => 'Waterfall',
                'action' => 'Everyone starts drinking, can\'t stop until person to your right stops',
                'requires_input' => false
            ],
            2 => [
                'name' => 'You',
                'action' => 'Pick someone to drink',
                'requires_input' => false
            ],
            3 => [
                'name' => 'Me',
                'action' => 'Take 3',
                'requires_input' => false
            ],
            4 => [
                'name' => 'Whores',
                'action' => 'Girls drink',
                'requires_input' => false
            ],
            5 => [
                'name' => 'Nominate',
                'action' => 'Nominate someone to drink double until the next 5 is drawn',
                'requires_input' => false
            ],
            6 => [
                'name' => 'Dicks',
                'action' => 'All guys drink',
                'requires_input' => false
            ],
            7 => [
                'name' => 'Heaven',
                'action' => 'Everyone points up, last person drinks',
                'requires_input' => false
            ],
            8 => [
                'name' => 'Mate',
                'action' => 'Pick a drinking buddy, they drink when you drink',
                'requires_input' => false
            ],
            9 => [
                'name' => 'Last to drink',
                'action' => 'Last person to take a drink, Takes a Shot',
                'requires_input' => false
            ],
            10 => [
                'name' => 'Everyone',
                'action' => 'Everyone drinks',
                'requires_input' => false
            ],
            11 => [
                'name' => 'Make a rule',
                'action' => 'Create a custom rule for the game',
                'requires_input' => true // ✅ This one requires input!
            ],
            12 => [
                'name' => 'Question Master',
                'action' => 'Upon drawing this card, You are the Question Master. If someone answers any of your questions, They Drink. If they say "Fuck You Question Master", Then You drink',
                'requires_input' => false
            ],
            13 => [
                'name' => 'King\'s Cup',
                'action' => 'Every King is 1 Shot. The Game will Finish After the 4th King is drawn. (1 shot is equal to 10 drinks)',
                'requires_input' => false
            ]
        ];

        $createdCount = 0;
        foreach ($rules as $value => $ruleData) {
            $rule = GameRule::create([
                'game_id' => $game->id,
                'card_value' => (string) $value,
                'name' => $ruleData['name'],
                'description' => $ruleData['action'],
                'action_text' => $ruleData['name'],
                'category' => 'drinking',
                'intensity' => in_array($value, [1, 13]) ? 'high' : 'medium',
                'requires_input' => $ruleData['requires_input'] // ✅ Add this field
            ]);

            echo "✅ Created rule: card_value='{$rule->card_value}' -> {$rule->name} (requires_input: " . ($rule->requires_input ? 'true' : 'false') . ")\n";
            $createdCount++;
        }

        echo "🎉 Total rules created: {$createdCount}\n";

        // Verify the rules were created
        $finalCount = GameRule::where('game_id', $game->id)->count();
        echo "🔍 Final rules count in DB: {$finalCount}\n";
    }
}
