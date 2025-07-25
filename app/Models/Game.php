<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Game extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'type', 'description'];

    public function gameRules(): HasMany
    {
        return $this->hasMany(GameRule::class);
    }

    // ✅ FIXED: Get all cards with rules applied for this game
    public function getCardsWithRules()
    {
        // ✅ Store the game ID to ensure context is preserved
        $gameId = $this->id;

        $universalCards = \App\Models\UniversalCard::all();

        // ✅ Use explicit game ID instead of relationship to avoid context loss
        $gameRules = \App\Models\GameRule::where('game_id', $gameId)
            ->get()
            ->keyBy('card_value');

        Log::info('Game rules loaded with explicit ID', [
            'game_id' => $gameId,
            'rules_count' => $gameRules->count(),
            'rule_keys' => $gameRules->keys()->toArray()
        ]);

        return $universalCards->map(function ($card, $index) use ($gameRules, $gameId) {
            // ✅ Try string match first (since DB stores as strings)
            $rule = $gameRules->get((string) $card->value);

            // ✅ Fallback to integer match
            if (!$rule) {
                $rule = $gameRules->get($card->value);
            }

            // ✅ DEBUG: Log failed matches with more context
            if (!$rule) {
                Log::warning('No rule found for card', [
                    'game_id' => $gameId,
                    'card_value' => $card->value,
                    'card_value_type' => gettype($card->value),
                    'card_value_as_string' => (string) $card->value,
                    'available_rule_keys' => $gameRules->keys()->toArray(),
                    'rules_count' => $gameRules->count()
                ]);
            }

            return (object) [
                'id' => $index,
                'label' => $card->label,
                'suit' => $card->suit,
                'value' => $card->value,
                'color' => $card->color,
                'action_text' => $rule ? $rule->action_text : 'No rule defined',
                'rule_name' => $rule ? $rule->name : null,
                'rule_description' => $rule ? $rule->description : null,
                'category' => $rule ? $rule->category : null,
                'intensity' => $rule ? $rule->intensity : 'medium',
                'requires_input' => $rule ? $rule->requires_input : false // ✅ Add this
            ];
        })->toArray();
    }
}
