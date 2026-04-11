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

/**
 * @group 5. GAME - PLAY
 * This is the core engine endpoint where the magic happens. 
 * Use this to interact with the world by sending verbs and targets.
 * @authenticated
 */
class GameActionController extends Controller
{

    /**
     * PLAY
     * 
     * Handles game interactions. Every action returns a narrative message and the updated state 
     * of the room and inventory according to the user's story progress.<br>
     * 
     * <b>HOW TO START PLAYING:<br></b>
     * 1. register/login. <br>
     * 2. create a new game. <br>
     * 3. check the game id and the "start" id. <br>
     * 4. fill up 'verb' with "GO TO" (uppercase), <br>
     * 5. fill up 'target_id' : use the "start" id number. <br>
     * 6. leave blank 'item_id'<br>
     * A new room with objects will appear. <br><br>
     * * Can you 'PICK UP' the crowbar? <br>
     * * try 'LOOK AT' with any item so view a description. <br>
     * * try to 'PUSH' the tree. <br>
     * * try to 'USE' the tennis Ball (pocket item) with the dog (target_id).<br>
     * 
     *  
     * @bodyParam verb string required action verb. 
     * <br> Use any of the following:<br> 
     * 'LOOK AT','USE', 'PICK UP','GO TO', 'OPEN', 'RESCUE','PULL', 'PUSH'.
     * <br>Example: GO TO 
     * @bodyParam target_id integer required target_id. Example: use 'start' item id
     * @bodyParam item_id integer required The item_id of a pocket item. Example: id of the key 
     * 
     * @authenticated
     * 
     * @response 200 escenario="OK"
     * {
     *      "message": "You step through the doorway. Here we Go!",
     *       "game": {
     *           "id": 1,
     *           "avatar": "Sir Isaac Mewton",
     *           "progress": 1,
     *           "current_room": {
     *               "id": 1,
     *               "name": "Mansion's Garden",
     *               "image_url": "assets/rooms/garden_1.svg",
     *               "layout_type": "all",
     *               "items": [
     *                   {
     *                       "id": 60,
     *                       "name_id": "tree",
     *                       "description": "Didn't know Cypress had yellow fruits...",
     *                       "image_url": null,
     *                       "is_portable": false,
     *                       "is_visible": true
     *                   },
     *                   {
     *                       "id": 61,
     *                       "name_id": "tennis ball",
     *                       "description": "You only live once, but you get to serve twice",
     *                       "image_url": "assets/items/tennisBall.png",
     *                       "is_portable": true,
     *                       "is_visible": false
     *                   },
     *                   {
     *                   ... ...
     *                   },
     *                   {
     *                       "id": 72,
     *                       "name_id": "blink",
     *                       "description": " I could swear that thing was five feet back a second ago...",
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
     *       }
     *   }
     * 
     * Received response (422):{"message": "Action incomplete."}
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
