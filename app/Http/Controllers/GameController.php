<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Providers\Game\GameStart;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET api/games
     * display all user's games
     */
    public function index(request $request)
    {
       return response()->json($request->user()->games,200);
    }

    /**
     * Store a newly created resource in storage.
     * POST /api/games
     * Creates a new game
     */
    public function store(Request $request, GameStart $startGame)
    {
        $validated = $request->validate([
            'avatar' => 'required|string|max:45|min:3',
        ]);

        try {
            $game = $startGame->handle( 
                $request -> user(), 
                $validated['avatar']
            );

            return response()->json($game, 201);

        } catch (\exception $e){
            return response()->json([
                'message'=>'Failed to start a new game.',
                'error'=>$e->getMessage()
            ],500);
        }
    }

    /**
     * Display the specified resource.
     * GET /api/games/{game}
     * Continue/Read a specific game.
     */
    public function show(Game $game)
    {
        //TODO: add full code here
        /*if ($game->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }*/

        return response()->json($game, 200);
    }

    /**
     * Update the specified resource in storage.
     * PUT api/games/{game}
     */
    public function update(Request $request, Game $game) 
    {
        //TODO: add full code here
        //updates will happen when user plays
        /*if ($game->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }*/
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
