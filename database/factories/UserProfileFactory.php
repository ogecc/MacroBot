<?php

namespace Database\Factories;

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\Goal;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserProfile>
 */
class UserProfileFactory extends Factory
{
    public function definition(): array
    {
        $gender = fake()->randomElement(Gender::cases());
        $goal = fake()->randomElement(Goal::cases());

        return [
            'age' => fake()->numberBetween(18, 65),
            'gender' => $gender,
            'height_cm' => fake()->numberBetween(155, 195),
            'weight_kg' => fake()->randomFloat(2, 50, 120),
            'activity_level' => fake()->randomElement(ActivityLevel::cases()),
            'goal' => $goal,
            'target_weight_kg' => $goal !== Goal::Maintain ? fake()->randomFloat(2, 50, 110) : null,
            'daily_calorie_goal' => fake()->numberBetween(1500, 3000),
        ];
    }
}
