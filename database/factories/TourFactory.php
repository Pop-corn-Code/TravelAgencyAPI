<?php

namespace Database\Factories;

use App\Models\Travel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tour>
 */
class TourFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'travel_id' => Travel::factory(),
            'name' => fake()->text(20),
            'starting_date' => fake()->date('Y-m-d', now()),
            'ending_date' => fake()->date('Y-m-d', now()->addDays(5)),
            'price' => rand(1, 1500),
        ];
    }
}
