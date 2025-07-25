<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GameRule extends Model
{
    protected $fillable = [
        'game_id',
        'card_value',
        'name',
        'description',
        'action_text',
        'category',
        'intensity',
        'requires_input'
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function universalCards(): BelongsToMany
    {
        return $this->belongsToMany(UniversalCard::class, 'card_rule_assignments')
                    ->withPivot('weight')
                    ->withTimestamps();
    }
}
