<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rule extends Model
{
    use HasFactory;

    protected $fillable = ['game_id', 'text', 'xp_reward'];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
