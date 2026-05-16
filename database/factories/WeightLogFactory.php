<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WeightLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'weight_kg' => $this->faker->randomFloat(1, 50, 120),
            'logged_at' => $this->faker->unique()->dateTimeBetween('-30 days', 'now'),
            'notes' => null,
        ];
    }
}
