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
        $travels = Travel::pluck('id');

        return [
            'travel_id' => fake()->randomElement($travels),
            'name' => fake()->text(50),
            'starting_date' => now(),
            'ending_date' => now()->addDays(rand(1, 10)),
            'price' => fake()->randomFloat(2, 10, 999),
        ];
    }
}
