<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlayPlayer extends Model
{
    use HasFactory;

    protected $fillable = ['play_session_id', 'user_id', 'name', 'turn_order'];

    public function session() {
        return $this->belongsTo(PlaySession::class, 'play_session_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}

