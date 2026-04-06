<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

use App\Enums\RoomType;
use App\Enums\Verb;

class MetadataController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'verbs' => array_column(Verb::cases(), 'value'),
            'room_types' => array_column(RoomType::cases(), 'value')
        ]);
    }
}