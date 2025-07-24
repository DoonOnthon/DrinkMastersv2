<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description',
        'type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an activity for a user
     */
    public static function log(User $user, string $description, string $type = 'user'): self
    {
        return self::create([
            'user_id' => $user->id,
            'description' => $description,
            'type' => $type,
        ]);
    }
}
