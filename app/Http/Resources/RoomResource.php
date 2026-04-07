<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\RoomType;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $gameId = $this->additional['game_id'] ?? null;
        return [
            'id'=> $this->id,
            'name'=> $this->name,
            'image_url'=> $this->image_url,
            'layout_type'=> $this->room_type->value,

            'items' => ItemResource::collection(
                $this->items()->where('game_id', $gameId)->get()
            ),
        ];
    }
}
