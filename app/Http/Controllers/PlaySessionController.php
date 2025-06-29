<?php

namespace App\Http\Controllers;

use App\Models\PlaySession;
use App\Models\Game;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlaySessionController extends Controller
{
    // 🎮 Create new play session
    public function store(Request $request, Game $game)
    {
        $mode = $request->input('mode');

        $cards = $game->cards()->pluck('id')->toArray();
        shuffle($cards);

        $session = PlaySession::create([
            'game_id' => $game->id,
            'host_user_id' => auth()->id(),
            'code' => strtoupper(Str::random(6)),
            'is_public' => true,
            'state' => [
                'mode' => $mode,
                'drawn' => [],
                'turn' => 0,
                'deck' => $cards, // ✅ Shared deck order
            ],
        ]);

        return redirect()->route('play.session.show', $session->code);
    }

    // 📡 API for polling session state
    public function api($code)
    {
        $session = PlaySession::with(['game', 'players'])->where('code', $code)->firstOrFail();
        return response()->json($session);
    }

    // 🃏 Draw a card (add card index to state.drawn)
    public function draw(string $code)
    {
        try {
            $session = PlaySession::where('code', $code)->firstOrFail();
            $state = $session->state;

            $cardsCount = Game::findOrFail($session->game_id)->cards()->count();
            $drawn = $state['drawn'] ?? [];

            if (count($drawn) >= $cardsCount) {
                return response()->json(['error' => 'All cards drawn'], 400);
            }

            // Get next available index
            for ($i = 0; $i < $cardsCount; $i++) {
                if (!in_array($i, $drawn)) {
                    $drawn[] = $i;
                    break;
                }
            }

            $state['drawn'] = $drawn;
            $session->state = $state;
            $session->save();

            return response()->json(['drawn' => $drawn]);
        } catch (\Throwable $e) {
            \Log::error('Draw error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error during draw'], 500);
        }
    }

    // 🔄 Advance turn to next player
    public function nextTurn(string $code)
    {
        $session = PlaySession::with('players')->where('code', $code)->firstOrFail();
        $state = $session->state ?? [];

        $players = $session->players;
        $count = $players->count();

        if ($count === 0) {
            return response()->json(['error' => 'No players available'], 400);
        }

        $currentTurn = $state['turn'] ?? 0;
        $state['turn'] = ($currentTurn + 1) % $count;

        $session->state = $state;
        $session->save();

        return response()->json(['success' => true, 'turn' => $state['turn']]);
    }

    // 🧑 Join session
    public function join(Request $request, string $code)
    {
        $session = PlaySession::with('players')->where('code', $code)->firstOrFail();
        $name = trim($request->input('name'));

        if (!$name) {
            return response()->json(['error' => 'Name required'], 422);
        }

        if ($session->players()->where('name', $name)->exists()) {
            return response()->json(['error' => 'Name already taken'], 422);
        }

        $player = $session->players()->create([
            'name' => $name,
            'user_id' => auth()->id(),
            'turn_order' => $session->players()->count(),
        ]);

        return response()->json($player);
    }

    // 🖥️ Load full Inertia page for initial entry
    public function show(string $code)
    {
        $session = PlaySession::with('game', 'players')->where('code', $code)->firstOrFail();

        return Inertia::render('PlaySession/Show', [
            'session' => $session,
        ]);
    }
}
