<?php

namespace App\Http\Resources;

use App\Enums\RolesEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class userResource extends JsonResource
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
        ];
    }
}
