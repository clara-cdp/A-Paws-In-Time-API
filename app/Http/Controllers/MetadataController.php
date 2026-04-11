<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

use App\Enums\RoomType;
use App\Enums\Verb;

/**
 * @group 6. METADATA
 * Static data and enums used across the game engine.
 * @authenticated
 * 
 */
class MetadataController extends Controller
{

    /**
     * View Game Constants.
     * Returns a list of all available Verbs and Room Types. 
     * Use this to populate UI buttons or validate actions before sending them to the Play endpoint.
     * @response 200 scenario="Success" {
     * "verbs": [ "LOOK AT","USE","PICK UP","GO TO","OPEN","RESCUE","PULL","PUSH"],
     * "room_types": ["hor", "ver", "all"]
     * }
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'verbs' => array_column(Verb::cases(), 'value'),
            'room_types' => array_column(RoomType::cases(), 'value')
        ]);
    }
}