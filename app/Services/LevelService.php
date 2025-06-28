<?php

namespace App\Services;

use App\Models\Level;
use App\Models\User;

class LevelService
{
    public function addXp(User $user, int $amount): void
    {
        $user->xp += $amount;
        $user->save();
    }

    public function getUserLevel(User $user): int
    {
        return Level::where('xp_required', '<=', $user->xp)
            ->orderByDesc('xp_required')
            ->value('level') ?? 1;
    }
}
