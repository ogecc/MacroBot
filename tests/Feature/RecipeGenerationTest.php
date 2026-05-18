<?php

use App\AI\Agents\RecipeChef;
use App\Models\User;

test('endpoint requires authentication', function () {
    $this->postJson(route('recipes.generate'))
        ->assertUnauthorized();
});

test('returns 422 if prompt is missing', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->postJson(route('recipes.generate'), [])
        ->assertUnprocessable();
});

test('returns 422 if prompt is too short', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->postJson(route('recipes.generate'), ['prompt' => 'hi'])
        ->assertUnprocessable();
});

test('returns structured json with ai coach note and recipes', function () {
    $user = User::factory()->withProfile()->create();

    $fakeResponse = [
        'is_recipe_related' => true,
        'ai_coach_note' => 'These recipes fit your remaining calorie budget.',
        'recipes' => [
            [
                'title' => 'Grilled Chicken Salad',
                'description' => 'Light and protein-rich.',
                'meal_type' => 'lunch',
                'servings' => 2,
                'prep_time_min' => 10,
                'cook_time_min' => 20,
                'cuisine' => 'Mediterranean',
                'difficulty' => 'easy',
                'calories_per_serving' => 450,
                'protein_g' => 42.0,
                'carbs_g' => 18.0,
                'fat_g' => 14.0,
                'why_recommended' => 'Hits your protein gap perfectly.',
                'ingredients' => [
                    ['amount' => '200g', 'name' => 'chicken breast'],
                ],
                'steps' => ['Grill chicken.', 'Serve on salad.'],
            ],
        ],
    ];

    RecipeChef::fake([$fakeResponse]);

    $this->actingAs($user)
        ->postJson(route('recipes.generate'), ['prompt' => 'Something light and high protein'])
        ->assertOk()
        ->assertJsonStructure(['ai_coach_note', 'recipes'])
        ->assertJsonPath('ai_coach_note', 'These recipes fit your remaining calorie budget.')
        ->assertJsonCount(1, 'recipes');
});

test('returns 422 when input is not recipe related', function () {
    $user = User::factory()->withProfile()->create();

    RecipeChef::fake([[
        'is_recipe_related' => false,
        'ai_coach_note' => '',
        'recipes' => [],
    ]]);

    $this->actingAs($user)
        ->postJson(route('recipes.generate'), ['prompt' => 'What is the capital of France?'])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Please describe a meal or ingredients. MacroBot only generates food recipes.');
});

test('returns 422 when ai agent throws an exception', function () {
    $user = User::factory()->withProfile()->create();

    RecipeChef::fake(fn () => throw new Exception('AI failed'));

    $this->actingAs($user)
        ->postJson(route('recipes.generate'), ['prompt' => 'Give me a healthy dinner'])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Failed to generate recipes. Please try again.');
});

test('meal type is optional', function () {
    $user = User::factory()->withProfile()->create();

    RecipeChef::fake([[
        'is_recipe_related' => true,
        'ai_coach_note' => 'Great options for you.',
        'recipes' => [],
    ]]);

    $this->actingAs($user)
        ->postJson(route('recipes.generate'), ['prompt' => 'Something with chicken and rice'])
        ->assertOk();
});

test('meal type must be valid when provided', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->postJson(route('recipes.generate'), ['prompt' => 'Something with chicken', 'meal_type' => 'brunch'])
        ->assertUnprocessable();
});
