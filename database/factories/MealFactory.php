<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MealFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->randomElement(['Breakfast', 'Lunch', 'Dinner', 'Snack']),
            'eaten_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'image_path' => null,
            'notes' => null,
        ];
    }
}
