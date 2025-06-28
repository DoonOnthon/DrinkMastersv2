<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Game extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'type', 'description'];

    public function rules()
    {
        return $this->hasMany(Rule::class);
    }

    public function sessions()
    {
        return $this->hasMany(GameSession::class);
    }
}
