<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Rule;
use App\Models\GameSession;
use App\Models\Card;

class Game extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'type', 'description'];

    public function rules()
    {
        return $this->hasMany(Rule::class);
    }
    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    public function sessions()
    {
        return $this->hasMany(GameSession::class);
    }
}
