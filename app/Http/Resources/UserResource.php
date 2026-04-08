<?php

namespace App\Http\Resources;

use App\Enums\RolesEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         $roleName = $this->roles->pluck('name')->first();
         $roleEnum = RolesEnum::tryFrom($roleName) ?? RolesEnum::User;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $roleEnum->value,
            'is_active' => (bool) $this->is_active,

            'games_count' => $this->whenNotNull($this->games_count),
            'game_list' => $this->whenLoaded('games', function () {
                return $this->games->pluck('avatar');
            }),
        ];
    }
}
