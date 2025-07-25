<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class PlaySession extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'game_id',
        'host_user_id',
        'state',
        'last_activity',
        'completed_at',
        'auto_cleanup'
    ];

    protected $casts = [
        'state' => 'array',
        'last_activity' => 'datetime',
        'completed_at' => 'datetime',
        'auto_cleanup' => 'boolean'
    ];

    // Update activity timestamp
    public function updateActivity()
    {
        $this->last_activity = Carbon::now();
        $this->save();
    }

    // Mark session as completed
    public function markCompleted()
    {
        $this->completed_at = Carbon::now();
        $this->state = array_merge($this->state ?? [], ['status' => 'completed']);
        $this->save();
    }

    // Check if deck is finished
    public function isDeckFinished()
    {
        $deck = $this->state['deck'] ?? [];
        $drawnCount = count($this->state['drawn'] ?? []);
        return $drawnCount >= count($deck);
    }

    // Relationships
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function players()
    {
        return $this->hasMany(PlayPlayer::class);
    }

    public function host()
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }
}

