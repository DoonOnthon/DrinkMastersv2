<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = ['game_id', 'label', 'suit', 'value', 'action_text'];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
