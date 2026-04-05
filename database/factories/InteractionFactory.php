<?php

namespace Database\Factories;

use App\Models\Interaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Interaction>
 */
class InteractionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'verb_trigger'  => 'LOOK AT',
            'item_id'       => \App\Models\Item::factory(),
            'step_required' => 0,
            'next_step'     => 0,
            'reward'        => 'You found something interesting!',
        ];
    }
}
