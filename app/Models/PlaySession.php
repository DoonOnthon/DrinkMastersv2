<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlaySession extends Model
{
    use HasFactory;

    protected $fillable = ['game_id', 'host_user_id', 'code', 'is_public', 'state'];
    protected $casts = ['state' => 'array'];

    public function game() {
        return $this->belongsTo(Game::class);
    }

    public function players() {
        return $this->hasMany(PlayPlayer::class);
    }

    public function host() {
        return $this->belongsTo(User::class, 'host_user_id');
    }
}

