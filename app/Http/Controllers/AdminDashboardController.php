<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Game;
use App\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $users = User::count();
        $games = Game::count();
        $xpTotal = User::sum('xp');

        // Optional: Define "active" as last login within 7 days
        $activePlayers = User::where('last_login_at', '>=', now()->subDays(7))->count();

        // XP earned over last 7 days (mock: sum by created_at)
        $xpData = collect(
            range(0, 6)
        )->map(function ($i) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');

            // You can change this logic to pull real activity or xp-earning data
            return [
                'label' => Carbon::now()->subDays($i)->format('D'),
                'value' => User::whereDate('created_at', $date)->sum('xp') // or get from `activities`
            ];
        })->reverse()->values();

        return Inertia::render('Admin/Dashboard', [
            'users' => $users,
            'games' => $games,
            'xpTotal' => $xpTotal,
            'activePlayers' => $activePlayers,
            'xpData' => $xpData,
        ]);
    }
}
