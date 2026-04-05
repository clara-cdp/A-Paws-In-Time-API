<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'avatar' => $this->faker->name(), 
            'user_id' => \App\Models\User::factory(),
            'room_id' => \App\Models\Room::factory(),
            'progress' => 0,
        ];
    }
}
