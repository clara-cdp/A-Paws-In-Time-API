<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'avatar'   => $this->avatar,
            'progress' => (int) $this->progress,
            'current_room' => (new RoomResource($this->whenLoaded('room')))
                    ->additional(['game_id' => $this->id]),
            'pocket' => ItemResource::collection($this->pocket->items),
        ];
    }

}

