<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionPlayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'play_session_id',
        'user_id',
        'name',
        'turn_order',
        'is_active',
        'joined_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'joined_at' => 'datetime',
        'turn_order' => 'integer',
    ];

    /**
     * Relationship to play session
     */
    public function playSession()
    {
        return $this->belongsTo(PlaySession::class);
    }

    /**
     * Relationship to user (nullable for anonymous players)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is an authenticated player
     */
    public function isAuthenticated(): bool
    {
        return !is_null($this->user_id);
    }

    /**
     * Check if this is an anonymous player
     */
    public function isAnonymous(): bool
    {
        return is_null($this->user_id);
    }
}
