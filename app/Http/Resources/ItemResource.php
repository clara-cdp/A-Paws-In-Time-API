<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_id' => $this->name_id,
            'description' => $this->description,
            'image_url' => $this->image_url, 
            'is_portable' => (bool) $this->is_portable,
            'is_visible'  => (bool) $this->is_visible,
        ];
    }
}
