<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use App\Providers\Game\GameState;
use App\Providers\Game\GameEngine;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Enums\Verb;
use App\Http\Resources\GameResource;
use Illuminate\Http\JsonResponse;

class GameActionController extends Controller
{

    /**
     * POST api/games/{game}/actions
     */
    public function play(Request $request, Game $game, GameEngine $engine): JsonResponse
    {
        if ($game->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'verb'      => 'required|string',   
            'target_id' => 'required|integer',  
            'item_id'   => 'nullable|integer',  
        ]);

        $state = new GameState(
            Verb::from($validated['verb']),
            $validated['target_id'],
            $validated['item_id'] ?? null 
        );

        if (!$state->verb || !$state->targetItemId) {
            return response()->json(['message' => 'Action incomplete.'], 422);
        }

        $displayMessage = $engine->resolve($state, $game);

        return response()->json([
            'message' => $displayMessage,
            'game'    => new GameResource($game->load(['room.items', 'pocket.items']))
        ], 200);
    }



    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
