<?php

namespace App\Services;

use App\Enums\Gender;
use App\Models\UserProfile;

class CalorieGoalCalculator
{
    public function calculate(UserProfile $profile): int
    {
        $bmr = $this->bmr($profile);

        $multiplier = 1.2; // sedentary baseline — actual activity is logged daily

        $calories = (int) round($bmr * $multiplier);

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
