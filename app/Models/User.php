<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'xp',
        'reports_count',
        'is_admin',
        'is_moderator',
        'is_manager',
        'is_suspended',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_admin' => 'boolean',
            'is_moderator' => 'boolean',
            'is_manager' => 'boolean',
            'is_suspended' => 'boolean',
            'xp' => 'integer',
            'reports_count' => 'integer',
        ];
    }

    /**
     * Check if user has admin privileges
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Check if user has moderator privileges
     */
    public function isModerator(): bool
    {
        return $this->is_moderator || $this->is_admin;
    }

    /**
     * Check if user has manager privileges
     */
    public function isManager(): bool
    {
        return $this->is_manager || $this->is_admin;
    }

    /**
     * Check if user is suspended
     */
    public function isSuspended(): bool
    {
        return $this->is_suspended;
    }

    /**
     * Relationship to play players
     */
    public function playPlayers()
    {
        return $this->hasMany(PlayPlayer::class);
    }

    /**
     * Relationship to hosted play sessions
     */
    public function hostedSessions()
    {
        return $this->hasMany(PlaySession::class, 'host_user_id');
    }

    /**
     * Relationship to activities
     */
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * The "booted" method is called when the model is being booted.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function ($user) {
            // Auto-verify email in development
            if (app()->environment('local')) {
                $user->markEmailAsVerified();
            }
        });
    }
}
