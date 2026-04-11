<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Providers\Game\GameStart;
use App\Http\Resources\GameResource; 
use Illuminate\Http\JsonResponse;

/**
 * @group 4. GAMES
 * Endpoints for managing game saves, character avatars, and progression.
 * @authenticated
 */
class GameController extends Controller
{
    /**
     * GAME - INDEX
     * 
     * Allows a user to view a listing of the user's started games.
     * 
     * @response 200 scenario="OK"
     * [
     * { "id": 1, "avatar": "Sir Isaac Mewton"},
     * { "id": 2, "avatar": "Lucifur"},
     * { "id": 3, "avatar": "Genghis Kat"}
     * ]
     * 
     * @response 401 scenario="UNAUTHORIZED"{ "message": "Unauthenticated."}
     * 
     */
    public function index(request $request):JsonResponse
    {
        $saves = $request->user()->games()->get(['id', 'avatar']);  

        return response()->json($saves, 200);
    }

    /** 
     * GAME - NEW
     * 
     * Creates a new game instance. Users are limited to 3 save slots.
     * @bodyParam avatar string required The name of your character. Min 3, Max 45 chars. Example: Sir Isaac Mewton
     * 
     * @response 201 escenario="created"
     * {
     *      "message": "New journey started!",
     *      "game": {
     *           "id": 1,
     *           "avatar": "Sir Isaac Mewton",
     *           "progress": 0,
     *           "current_room": {
     *               "id": 2,
     *               "name": "Intro",
     *               "image_url": "assets/rooms/intro.svg",
     *               "layout_type": "hor",
     *               "items": [
     *                   {
     *                       "id": 59,
     *                       "name_id": "start",
     *                       "description": "Click 'GO TO' to start your journey",
     *                       "image_url": null,
     *                       "is_portable": false,
     *                       "is_visible": true
     *                   }
     *               ]
     *           },
     *           "pocket": [
     *               {
     *                   "id": 58,
     *                   "name_id": "fish",
     *                   "description": "A red herring",
     *                   "image_url": "assets/items/redHerring.png",
     *                   "is_portable": false,
     *                   "is_visible": true
     *               }
     *           ]
     *      }
     *   }
     * 
     * @response 422 escenario="Unprocessable content"{"message": "You have reached the maximum number of save slots (3)."}
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
     * GAME - CONTINUE
     * 
     * @response 200 scenario="OK"
     * {
     *      "message": "Game progress saved.",
     *      "game": {
     *           "id": 4,
     *           "avatar": "Lucifur",
     *           "progress": 0,
     *           "current_room": {
     *               "id": 2,
     *               "name": "Intro",
     *               "image_url": "assets/rooms/intro.svg",
     *               "layout_type": "hor",
     *               "items": [
     *                   {
     *                       "id": 116,
     *                       "name_id": "start",
     *                       "description": "Click 'GO TO' to start your journey",
     *                       "image_url": null,
     *                       "is_portable": false,
     *                       "is_visible": true
     *                   }
     *               ]
     *           },
     *           "pocket": [
     *               {
     *                   "id": 115,
     *                   "name_id": "fish",
     *                   "description": "A red herring",
     *                   "image_url": "assets/items/redHerring.png",
     *                   "is_portable": false,
     *                   "is_visible": true
     *               }
     *           ]
     *       }
     *   }
     */
    public function show(Game $game)
    {
        if ($game->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json([
            'game' => new GameResource($game->load(['room.items', 'pocket.items']))
        ], 200);
    }

    /**
     * GAME - Update Save
     * 
     * Update the avatar name or trigger a "touch" to update the last-played timestamp.
     * @bodyParam avatar string optional. Example: Purrlock Holmes
     * @response 200 scenario="OK"
     * {
     *       "message": "Game progress saved.",
     *       "game": {
     *           "id": 2,
     *           "avatar": "Mawdonna",
     *           "progress": 0,
     *           "current_room": {
     *               "id": 2,
     *               "name": "Intro",
     *               "image_url": "assets/rooms/intro.svg",
     *               "layout_type": "hor",
     *               "items": [
     *                   {
     *                       "id": 116,
     *                       "name_id": "start",
     *                       "description": "Click 'GO TO' to start your journey",
     *                       "image_url": null,
     *                       "is_portable": false,
     *                       "is_visible": true
     *                   }
     *               ]
     *           },
     *           "pocket": [
     *               {
     *                   "id": 115,
     *                   "name_id": "fish",
     *                   "description": "A red herring",
     *                   "image_url": "assets/items/redHerring.png",
     *                   "is_portable": false,
     *                   "is_visible": true
     *               }
     *           ]
     *       }
     *   }
     * 
     * @response 422 escenario="Unprocessable Content"
     * {
     *       "message": "The avatar field must be at least 3 characters.",
     *       "errors": {
     *           "avatar": [
     *               "The avatar field must be at least 3 characters."
     *           ]
     *       }
     *   }
     * @response 403 scenario="Forbidden" { "message": "Forbidden" }
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
     * GAME - DELETE
     * 
     * Permanently remove a game save.
     * <aside class="warning">This action is permanent and cannot be undone.</aside>
     * 
     * @response 200 scenario="Deleted" { "message": "Game deleted successfully." }
     * @response 403 scenario="Forbidden" { "message": "Unauthorized" }
     * 
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
