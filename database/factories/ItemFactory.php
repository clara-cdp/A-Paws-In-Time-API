<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_id'      => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'image_url'   => 'items/default.png',
            'is_portable' => true,
            'is_visible'  => true,
            'room_id'     => \App\Models\Room::factory(), // Creates a room if one isn't provided
            'game_id'     => null, // Default to a "Master" item
        ];
    }
}
