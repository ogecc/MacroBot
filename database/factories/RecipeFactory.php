<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipe>
 */
class RecipeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement(['Grilled Chicken Salad', 'Beef Stir-Fry', 'Salmon with Quinoa', 'Veggie Omelette', 'Pasta Bolognese']),
            'prompt' => fake()->sentence(),
            'meal_type' => fake()->randomElement(['breakfast', 'lunch', 'dinner', 'snack', null]),
            'servings' => fake()->numberBetween(1, 4),
            'prep_time_min' => fake()->numberBetween(5, 30),
            'cook_time_min' => fake()->numberBetween(10, 60),
            'cuisine' => fake()->randomElement(['Italian', 'Asian', 'Mediterranean', 'Mexican', 'American']),
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard']),
            'calories_per_serving' => fake()->numberBetween(300, 800),
            'protein_g' => fake()->randomFloat(1, 15, 60),
            'carbs_g' => fake()->randomFloat(1, 20, 80),
            'fat_g' => fake()->randomFloat(1, 5, 40),
            'why_recommended' => fake()->sentence(),
            'ingredients' => [
                ['amount' => '200g', 'name' => 'chicken breast'],
                ['amount' => '1 cup', 'name' => 'spinach'],
            ],
            'steps' => [
                'Season the chicken with salt and pepper.',
                'Heat a pan over medium heat.',
                'Cook chicken for 6 minutes per side.',
                'Serve with fresh spinach.',
            ],
        ];
    }
}
