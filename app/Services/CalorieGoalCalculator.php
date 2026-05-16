<?php

namespace App\Services;

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\Goal;
use App\Models\UserProfile;

class CalorieGoalCalculator
{
    public function calculate(UserProfile $profile): int
    {
        $bmr = $this->bmr($profile);

        $multiplier = match ($profile->activity_level) {
            ActivityLevel::Sedentary => 1.2,
            ActivityLevel::LightlyActive => 1.375,
            ActivityLevel::ModeratelyActive => 1.55,
            ActivityLevel::VeryActive => 1.725,
            ActivityLevel::ExtremelyActive => 1.9,
        };

        $adjustment = match ($profile->goal) {
            Goal::Lose => -500,
            Goal::Maintain => 0,
            Goal::Gain => 300,
        };

        $calories = (int) round($bmr * $multiplier + $adjustment);

        return max(1200, $calories);
    }

    private function bmr(UserProfile $profile): float
    {
        $base = (10 * $profile->weight_kg)
            + (6.25 * $profile->height_cm)
            - (5 * $profile->age);

        return match ($profile->gender) {
            Gender::Male => $base + 5,
            Gender::Female => $base - 161,
            Gender::Other => ($base + 5 + $base - 161) / 2,
        };
    }
}
