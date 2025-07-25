<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\GameRule;

class UniversalCard extends Model
{
    protected $fillable = [
        'label',
        'suit',
        'value',
        'color'
    ];

    public function gameRules(): BelongsToMany
    {
        return $this->belongsToMany(GameRule::class, 'card_rule_assignments')
                    ->withPivot('weight')
                    ->withTimestamps();
    }

    // Get rule for a specific game
    public function getRuleForGame($gameId)
    {
        return $this->gameRules()->where('game_id', $gameId)->first();
    }
}
