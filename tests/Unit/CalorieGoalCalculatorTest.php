<?php

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\Goal;
use App\Models\UserProfile;
use App\Services\CalorieGoalCalculator;

test('calculates correctly for male sedentary maintain', function () {
    $profile = new UserProfile([
        'gender' => Gender::Male,
        'age' => 30,
        'height_cm' => 175,
        'weight_kg' => 80,
        'activity_level' => ActivityLevel::Sedentary,
        'goal' => Goal::Maintain,
    ]);

    // BMR = (10 * 80) + (6.25 * 175) - (5 * 30) + 5 = 800 + 1093.75 - 150 + 5 = 1748.75
    // TDEE = 1748.75 * 1.2 = 2098.5 → 2099
    $result = (new CalorieGoalCalculator)->calculate($profile);

    expect($result)->toBe(2099);
});

test('calculates correctly for female very active lose', function () {
    $profile = new UserProfile([
        'gender' => Gender::Female,
        'age' => 25,
        'height_cm' => 165,
        'weight_kg' => 65,
        'activity_level' => ActivityLevel::VeryActive,
        'goal' => Goal::Lose,
    ]);

    // BMR = (10 * 65) + (6.25 * 165) - (5 * 25) - 161 = 650 + 1031.25 - 125 - 161 = 1395.25
    // TDEE = 1395.25 * 1.725 = 2406.8 → 2407
    // Goal: -500 → 1907
    $result = (new CalorieGoalCalculator)->calculate($profile);

    expect($result)->toBe(1907);
});

test('result never falls below 1200', function () {
    $profile = new UserProfile([
        'gender' => Gender::Female,
        'age' => 80,
        'height_cm' => 100,
        'weight_kg' => 30,
        'activity_level' => ActivityLevel::Sedentary,
        'goal' => Goal::Lose,
    ]);

    $result = (new CalorieGoalCalculator)->calculate($profile);

    expect($result)->toBeGreaterThanOrEqual(1200);
});

test('calculates correctly for other gender using average of male and female bmr', function () {
    $profile = new UserProfile([
        'gender' => Gender::Other,
        'age' => 30,
        'height_cm' => 175,
        'weight_kg' => 80,
        'activity_level' => ActivityLevel::Sedentary,
        'goal' => Goal::Maintain,
    ]);

    // Male BMR = 1748.75, Female BMR = 1748.75 - 166 = 1582.75, Average = 1665.75
    // TDEE = 1665.75 * 1.2 = 1998.9 → 1999
    $result = (new CalorieGoalCalculator)->calculate($profile);

    expect($result)->toBe(1999);
});
