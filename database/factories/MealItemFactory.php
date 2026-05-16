<?php

namespace Database\Factories;

use App\Models\Meal;
use Illuminate\Database\Eloquent\Factories\Factory;

class MealItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'meal_id' => Meal::factory(),
            'name' => $this->faker->randomElement(['Chicken breast', 'Brown rice', 'Broccoli', 'Banana', 'Egg']),
            'quantity' => $this->faker->randomFloat(1, 50, 300),
            'unit' => $this->faker->randomElement(['g', 'ml', 'piece']),
            'calories' => $this->faker->numberBetween(50, 500),
            'protein_g' => $this->faker->randomFloat(1, 0, 50),
            'carbs_g' => $this->faker->randomFloat(1, 0, 60),
            'fat_g' => $this->faker->randomFloat(1, 0, 30),
        ];
    }
}
