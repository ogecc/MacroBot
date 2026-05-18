<?php

use App\Models\Meal;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('dashboard redirects to onboarding for user without profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('onboarding.edit'));
});

test('dailyCalorieGoal matches the user profile value', function () {
    $user = User::factory()->withProfile()->create();
    $goal = $user->userProfile->daily_calorie_goal;

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('dailyCalorieGoal', $goal)
        );
});

test('todayTotals calories is 0 when no meals logged today', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('todayTotals.calories', 0)
            ->where('todayTotals.protein_g', 0)
            ->where('todayTotals.carbs_g', 0)
            ->where('todayTotals.fat_g', 0)
        );
});

test('todayTotals correctly sums today meal items', function () {
    $user = User::factory()->withProfile()->create();

    $meal = Meal::factory()->for($user)->create(['eaten_at' => now()]);
    $meal->items()->createMany([
        ['name' => 'Chicken', 'quantity' => 1, 'unit' => 'piece', 'calories' => 300, 'protein_g' => 30, 'carbs_g' => 0, 'fat_g' => 10],
        ['name' => 'Rice', 'quantity' => 100, 'unit' => 'g', 'calories' => 130, 'protein_g' => 3, 'carbs_g' => 28, 'fat_g' => 0],
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('todayTotals.calories', 430)
            ->where('todayTotals.protein_g', 33)
            ->where('todayTotals.carbs_g', 28)
            ->where('todayTotals.fat_g', 10)
        );
});

test('recentMealsByDay only covers the last 7 days', function () {
    $user = User::factory()->withProfile()->create();

    Meal::factory()->for($user)->create(['eaten_at' => now()]);
    Meal::factory()->for($user)->create(['eaten_at' => now()->subDays(8)]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->has('recentMealsByDay', 1)
        );
});

test('recentMealsByDay groups meals by date with totals', function () {
    $user = User::factory()->withProfile()->create();

    $meal = Meal::factory()->for($user)->create(['eaten_at' => now()]);
    $meal->items()->createMany([
        ['name' => 'Chicken', 'quantity' => 1, 'unit' => 'piece', 'calories' => 300, 'protein_g' => 30, 'carbs_g' => 0, 'fat_g' => 10],
        ['name' => 'Rice', 'quantity' => 100, 'unit' => 'g', 'calories' => 130, 'protein_g' => 3, 'carbs_g' => 28, 'fat_g' => 0],
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('recentMealsByDay.0.total_calories', 430)
            ->has('recentMealsByDay.0.meals', 1)
        );
});

test('dashboard passes currentWeight from user profile', function () {
    $user = User::factory()->withProfile(['weight_kg' => 82.5])->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('currentWeight', 82.5)
        );
});

test('streak is 0 when no meals logged', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where('streak', 0));
});

test('streak is 1 when meals logged only today', function () {
    $user = User::factory()->withProfile()->create();
    Meal::factory()->for($user)->create(['eaten_at' => now()]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where('streak', 1));
});

test('streak counts consecutive days including today', function () {
    $user = User::factory()->withProfile()->create();
    Meal::factory()->for($user)->create(['eaten_at' => now()]);
    Meal::factory()->for($user)->create(['eaten_at' => now()->subDay()]);
    Meal::factory()->for($user)->create(['eaten_at' => now()->subDays(2)]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where('streak', 3));
});

test('streak breaks on a gap day', function () {
    $user = User::factory()->withProfile()->create();
    Meal::factory()->for($user)->create(['eaten_at' => now()]);
    // gap: no meal yesterday
    Meal::factory()->for($user)->create(['eaten_at' => now()->subDays(2)]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where('streak', 1));
});

test('streak counts from yesterday when no meal today', function () {
    $user = User::factory()->withProfile()->create();
    Meal::factory()->for($user)->create(['eaten_at' => now()->subDay()]);
    Meal::factory()->for($user)->create(['eaten_at' => now()->subDays(2)]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where('streak', 2));
});
