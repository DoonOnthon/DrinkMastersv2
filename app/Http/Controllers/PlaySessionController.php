<?php

namespace App\Http\Controllers;

use App\Models\PlaySession;
use App\Models\Game;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PlaySessionController extends Controller
{
    // 🎮 Create new play session
    public function store(Request $request, Game $game)
    {
        $request->validate([
            'mode' => 'required|in:host,multiplayer'
        ]);

        $mode = $request->input('mode');

        // For multiplayer mode, user must be logged in
        if ($mode === 'multiplayer' && !Auth::check()) {
            return redirect()->route('login')
                ->with('message', 'Login required for multiplayer mode and XP earning!');
        }

        // For host mode, user must be logged in to host
        if ($mode === 'host' && !Auth::check()) {
            return redirect()->route('login')
                ->with('message', 'Login required to host games');
        }

        Log::info('Creating session with mode: ' . $mode);

        $cards = $game->cards()->pluck('id')->toArray();
        shuffle($cards);

        $session = PlaySession::create([
            'game_id' => $game->id,
            'host_user_id' => Auth::id(),
            'code' => strtoupper(Str::random(6)),
            'is_public' => true,
            'state' => [
                'mode' => $mode,
                'status' => 'waiting',
                'drawn' => [],
                'turn' => 0,
                'deck' => $cards,
                'max_players' => 8,
                'created_at' => now()->toISOString(),
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

    // 🃏 Draw a card
    public function draw(string $code)
    {
        try {
            $session = PlaySession::where('code', $code)->firstOrFail();
            $state = $session->state;
            $mode = $state['mode'];

            // In host mode, only host can draw
            if ($mode === 'host') {
                if (!Auth::check() || $session->host_user_id !== Auth::id()) {
                    return response()->json(['error' => 'Only the host can draw cards in host mode'], 403);
                }
            }

            // In multiplayer mode, only the current turn player can draw
            if ($mode === 'multiplayer') {
                if (!Auth::check()) {
                    return response()->json(['error' => 'Must be logged in to draw in multiplayer mode'], 401);
                }

                $players = $session->players;
                $currentTurn = $state['turn'] ?? 0;

                if ($players->isEmpty()) {
                    return response()->json(['error' => 'No players in session'], 400);
                }

                $currentPlayer = $players->where('turn_order', $currentTurn)->first();
                if (!$currentPlayer || $currentPlayer->user_id !== Auth::id()) {
                    return response()->json(['error' => 'Not your turn'], 403);
                }
            }

            $cardsCount = Game::findOrFail($session->game_id)->cards()->count();
            $drawn = $state['drawn'] ?? [];

            if (count($drawn) >= $cardsCount) {
                return response()->json(['error' => 'All cards drawn'], 400);
            }

            for ($i = 0; $i < $cardsCount; $i++) {
                if (!in_array($i, $drawn)) {
                    $drawn[] = $i;
                    break;
                }
            }

            $state['drawn'] = $drawn;
            $state['status'] = 'playing';
            $session->state = $state;
            $session->save();

            return response()->json(['drawn' => $drawn]);
        } catch (\Throwable $e) {
            Log::error('Draw error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error during draw'], 500);
        }
    }

    // 🔄 Advance turn (BOTH HOST AND MULTIPLAYER)
    public function nextTurn(string $code)
    {
        $session = PlaySession::with('players')->where('code', $code)->firstOrFail();
        $state = $session->state ?? [];
        $mode = $state['mode'];

        Log::info('NextTurn called', [
            'code' => $code,
            'mode' => $mode,
            'user_id' => Auth::id()
        ]);

        if (!Auth::check()) {
            return response()->json(['error' => 'Must be logged in to advance turns'], 401);
        }

        // Get players ordered by turn_order
        $players = $session->players()->orderBy('turn_order')->get();
        $playerCount = $players->count();

        if ($playerCount === 0) {
            return response()->json(['error' => 'No players available'], 400);
        }

        $currentTurn = $state['turn'] ?? 0;
        $currentPlayer = $players->where('turn_order', $currentTurn)->first();

        // Permission check based on mode
        if ($mode === 'host') {
            // In host mode, only the host can advance turns
            if ($session->host_user_id !== Auth::id()) {
                return response()->json(['error' => 'Only the host can advance turns in host mode'], 403);
            }
        } elseif ($mode === 'multiplayer') {
            // In multiplayer mode, only host or current player can advance turn
            if ($session->host_user_id !== Auth::id() &&
                (!$currentPlayer || $currentPlayer->user_id !== Auth::id())) {
                return response()->json(['error' => 'Only host or current player can advance turn'], 403);
            }
        } else {
            return response()->json(['error' => 'Invalid session mode'], 400);
        }

        // Advance to next player (cycle through 0 to playerCount-1)
        $nextTurn = ($currentTurn + 1) % $playerCount;
        $state['turn'] = $nextTurn;
        $session->state = $state;
        $session->save();

        $nextPlayer = $players->where('turn_order', $nextTurn)->first();

        Log::info('Turn advanced', [
            'old_turn' => $currentTurn,
            'new_turn' => $nextTurn,
            'next_player' => $nextPlayer ? $nextPlayer->name : null
        ]);

        return response()->json([
            'success' => true,
            'turn' => $nextTurn,
            'old_turn' => $currentTurn,
            'current_player' => $nextPlayer ? [
                'id' => $nextPlayer->id,
                'name' => $nextPlayer->name,
                'turn_order' => $nextPlayer->turn_order
            ] : null
        ]);
    }

    // 🧑 Join session
    public function join(Request $request, string $code)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $session = PlaySession::with('players')->where('code', $code)->firstOrFail();
        $name = trim($request->input('name'));
        $mode = $session->state['mode'];

        // Check if name is already taken
        if ($session->players()->where('name', $name)->exists()) {
            return response()->json(['error' => 'Name already taken'], 422);
        }

        // Check max players
        $maxPlayers = $session->state['max_players'] ?? 8;
        if ($session->players()->count() >= $maxPlayers) {
            return response()->json(['error' => 'Session is full'], 400);
        }

        if ($mode === 'host') {
            // Host-led: Only host can add players, and host must be logged in
            if (!Auth::check()) {
                return response()->json(['error' => 'Host must be logged in to add players'], 401);
            }

            if ($session->host_user_id !== Auth::id()) {
                return response()->json(['error' => 'Only the host can add players in host mode'], 403);
            }

            // Add as anonymous player (no user_id)
            $player = $session->players()->create([
                'name' => $name,
                'user_id' => null,
                'turn_order' => $session->players()->count(),
            ]);

            return response()->json([
                'player' => $player,
                'message' => 'Player added to host-led session'
            ]);

        } elseif ($mode === 'multiplayer') {
            // Multiplayer: Must be logged in
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Login required for multiplayer mode',
                    'requires_auth' => true,
                    'message' => 'Create an account to join multiplayer sessions and earn XP!'
                ], 401);
            }

            // Check if user already in session
            if ($session->players()->where('user_id', Auth::id())->exists()) {
                return response()->json(['error' => 'You are already in this session'], 422);
            }

            // Add as authenticated player
            $player = $session->players()->create([
                'name' => $name,
                'user_id' => Auth::id(),
                'turn_order' => $session->players()->count(),
            ]);

            return response()->json([
                'player' => $player,
                'message' => 'Joined multiplayer session - you can earn XP!'
            ]);
        }

        return response()->json(['error' => 'Invalid session mode'], 400);
    }

    // 🚪 Leave session
    public function leave(string $code)
    {
        $session = PlaySession::with('players')->where('code', $code)->firstOrFail();

        if (!Auth::check()) {
            return response()->json(['error' => 'Must be logged in to leave'], 401);
        }

        $player = $session->players()->where('user_id', Auth::id())->first();

        if (!$player) {
            return response()->json(['error' => 'You are not in this session'], 404);
        }

        $player->delete();

        // Reorder remaining players
        $remainingPlayers = $session->players()->orderBy('turn_order')->get();
        foreach ($remainingPlayers as $index => $p) {
            $p->update(['turn_order' => $index]);
        }

        // Adjust turn if needed
        $state = $session->state;
        $playerCount = $remainingPlayers->count();

        if ($playerCount > 0 && $state['turn'] >= $playerCount) {
            $state['turn'] = 0;
            $session->state = $state;
            $session->save();
        }

        return response()->json(['success' => true, 'message' => 'Left session successfully']);
    }

    // 🖥️ Initial load
    public function show(string $code)
    {
        $session = PlaySession::with('game', 'players')->where('code', $code)->firstOrFail();

        return Inertia::render('PlaySession/Show', [
            'session' => $session,
            'auth' => [
                'user' => Auth::user(),
            ],
            'xp_incentives' => [
                'multiplayer_xp' => 50, // Future XP amount
                'completion_bonus' => 25,
                'show_banners' => !Auth::check(),
            ]
        ]);
    }

    // 🗑️ Delete session (host only)
    public function destroy(string $code)
    {
        $session = PlaySession::where('code', $code)->firstOrFail();

        if (!Auth::check() || $session->host_user_id !== Auth::id()) {
            return response()->json(['error' => 'Only the host can delete the session'], 403);
        }

        $session->delete();

        return response()->json(['success' => true, 'message' => 'Session deleted']);
    }
}
