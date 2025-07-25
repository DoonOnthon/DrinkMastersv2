<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\Card;
use Illuminate\Support\Facades\Log;

use Inertia\Inertia;

class GameController extends Controller
{
    public function index()
    {
        return Inertia::render('Games/Index', [
            'games' => Game::all()
        ]);
    }

    public function create()
    {
        return Inertia::render('Games/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:card,movie,video',
            'description' => 'nullable|string',
        ]);

        Game::create($validated);

        return redirect()->route('games.index');
    }

    public function edit(Game $game)
    {
        return Inertia::render('Games/Edit', [
            'game' => $game
        ]);
    }

    public function cards(Game $game)
    {
        Log::info('GameController::cards called', [
            'game_id' => $game->id,
            'game_title' => $game->title
        ]);

        $cardsWithRules = $game->getCardsWithRules();

        Log::info('Cards with rules generated', [
            'count' => count($cardsWithRules),
            'first_card_action' => $cardsWithRules[0]->action_text ?? 'null'
        ]);

        return response()->json($cardsWithRules);
    }
    public function play($id)
    {
        $game = Game::findOrFail($id);

        return Inertia::render('Games/Play', [
            'game' => $game,
        ]);
    }

    public function update(Request $request, Game $game)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:card,movie,video',
            'description' => 'nullable|string',
        ]);

        $game->update($validated);

        return redirect()->route('games.index');
    }

    public function destroy(Game $game)
    {
        $game->delete();

        return redirect()->route('games.index');
    }
}
