<?php

use App\Ai\Agents\MealAnalyzer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('endpoint requires authentication', function () {
    $this->postJson(route('meals.analyze'))
        ->assertUnauthorized();
});

test('returns 422 if neither image nor description provided', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->postJson(route('meals.analyze'), [])
        ->assertUnprocessable();
});

test('returns structured json with valid description', function () {
    $user = User::factory()->withProfile()->create();

    $fakeResponse = [
        'is_food_related' => true,
        'ai_observation' => 'I can see grilled chicken breast (~150g).',
        'items' => [
            ['name' => 'Chicken breast', 'quantity' => 150, 'unit' => 'g', 'calories' => 247, 'protein_g' => 46.5, 'carbs_g' => 0.0, 'fat_g' => 5.3],
        ],
        'meal_name_suggestion' => 'Lunch',
    ];

    MealAnalyzer::fake([$fakeResponse]);

    $this->actingAs($user)
        ->postJson(route('meals.analyze'), ['description' => 'Grilled chicken breast 150g'])
        ->assertOk()
        ->assertJsonStructure(['ai_observation', 'items', 'meal_name_suggestion'])
        ->assertJsonPath('ai_observation', 'I can see grilled chicken breast (~150g).')
        ->assertJsonPath('items.0.name', 'Chicken breast');
});

test('returns structured json with valid image', function () {
    Storage::fake('local');
    $user = User::factory()->withProfile()->create();

    $fakeResponse = [
        'is_food_related' => true,
        'ai_observation' => 'I can see a pizza slice.',
        'items' => [
            ['name' => 'Pizza slice', 'quantity' => 1, 'unit' => 'piece', 'calories' => 285, 'protein_g' => 12.0, 'carbs_g' => 36.0, 'fat_g' => 10.0],
        ],
        'meal_name_suggestion' => 'Dinner',
    ];

    MealAnalyzer::fake([$fakeResponse]);

    $image = UploadedFile::fake()->image('meal.jpg');

    $this->actingAs($user)
        ->post(route('meals.analyze'), ['image' => $image])
        ->assertOk()
        ->assertJsonStructure(['items', 'meal_name_suggestion']);
});

test('returns 422 when input is not food related', function () {
    $user = User::factory()->withProfile()->create();

    MealAnalyzer::fake([[
        'is_food_related' => false,
        'ai_observation' => '',
        'items' => [],
        'meal_name_suggestion' => '',
    ]]);

    $this->actingAs($user)
        ->postJson(route('meals.analyze'), ['description' => 'Write me a poem'])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Please describe a meal or food item. MacroBot only analyses food.');
});

test('returns 422 when description is too short', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->postJson(route('meals.analyze'), ['description' => 'hi'])
        ->assertUnprocessable();
});

test('returns 422 when ai agent throws an exception', function () {
    $user = User::factory()->withProfile()->create();

    MealAnalyzer::fake(fn () => throw new Exception('AI failed'));

    $this->actingAs($user)
        ->postJson(route('meals.analyze'), ['description' => 'Something'])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Failed to analyze meal. Please try again.');
});
