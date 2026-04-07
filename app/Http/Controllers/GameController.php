<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Providers\Game\GameStart;
use App\Http\Resources\GameResource; 
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET api/games
     * display all user's games
     */
    public function index(request $request):JsonResponse
    {
        $saves = $request->user()->games()->get(['id', 'avatar']);
        //$avatars = $request->user()->games()->pluck('avatar');
        return response()->json($saves, 200);
    }

    /**
     * Store a newly created resource in storage.
     * POST /api/games
     * Creates a new game
     */
    public function store(Request $request, GameStart $startGame)
    {
        if ($request->user()->games()->count() >= 3) {
            return response()->json([
                'message' => 'You have reached the maximum number of save slots (3).'
            ], 422);
        }
        
        $validated = $request->validate([
            'avatar' => 'required|string|max:45|min:3',
        ]);

        try {
            $game = $startGame->handle($request->user(), $validated['avatar']);

            return response()->json([
                'message' => 'New journey started!',
                'game'    => new GameResource($game->load(['room.items', 'pocket.items']))
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to start a new game.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * GET /api/games/{game}
     * Continue/Read a specific game.
     */
    public function show(Game $game)
    {
        if ($game->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $inventory = $game->pocket->items()
            ->get(['items.id', 'name_id'])
            ->makeHidden('pivot');

        return response()->json(
            new GameResource($game->load(['room.items', 'pocket.items'])),
            200
        );
    }

    /**
     * Update the specified resource in storage.
     * PUT api/games/{game}
     */
    public function update(Request $request, Game $game) 
    {
        if ($game->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'avatar' => 'nullable|string|max:45',
        ]);

        $game->update($validated);
        $game->touch();

        return response()->json([
            'message' => 'Game progress saved.',
            'game'    => new GameResource($game->load(['room.items', 'pocket.items']))
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/games/{game}
     */
    public function destroy(Game $game)
    {
        if ($game->user_id !== Auth::id()) {  
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $game->delete();

        return response()->json([
            'message'=>'Game deleted successfully.'
        ],200);
    }
}
