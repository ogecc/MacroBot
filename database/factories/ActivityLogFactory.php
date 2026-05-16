<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    public function definition(): array
    {
        $intensities = ['light', 'moderate', 'vigorous'];

        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Running', 'Cycling', 'Swimming', 'Weight Training', 'Yoga', 'Walking']),
            'description' => fake()->sentence(),
            'duration_min' => fake()->numberBetween(15, 90),
            'calories_burned' => fake()->numberBetween(100, 600),
            'intensity' => fake()->randomElement($intensities),
            'logged_at' => now(),
        ];
    }
}
