<?php

use App\Models\Recipe;
use App\Models\User;

test('recipe index requires authentication', function () {
    $this->get(route('recipes.index'))
        ->assertRedirect(route('login'));
});

test('user can view recipe index', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->get(route('recipes.index'))
        ->assertOk();
});

test('recipe index only shows authenticated users recipes', function () {
    $user = User::factory()->withProfile()->create();
    $other = User::factory()->create();

    Recipe::factory()->create(['user_id' => $user->id, 'title' => 'My Recipe']);
    Recipe::factory()->create(['user_id' => $other->id, 'title' => 'Their Recipe']);

    $this->actingAs($user)
        ->get(route('recipes.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('recipes.data', 1)
            ->where('recipes.data.0.title', 'My Recipe')
        );
});

test('user can save a recipe', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->postJson(route('recipes.store'), [
            'title' => 'Grilled Chicken Salad',
            'prompt' => 'Something light and high protein',
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
            'why_recommended' => 'Fits your calorie gap.',
            'ingredients' => [['amount' => '200g', 'name' => 'chicken breast']],
            'steps' => ['Grill chicken.', 'Serve on salad.'],
        ])
        ->assertRedirect();

    expect(Recipe::where('user_id', $user->id)->count())->toBe(1);
});

test('user can view their own saved recipe', function () {
    $user = User::factory()->withProfile()->create();
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('recipes.show', $recipe))
        ->assertOk();
});

test('user cannot view another users recipe', function () {
    $user = User::factory()->withProfile()->create();
    $other = User::factory()->create();
    $recipe = Recipe::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user)
        ->get(route('recipes.show', $recipe))
        ->assertNotFound();
});

test('user can delete their own recipe', function () {
    $user = User::factory()->withProfile()->create();
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->deleteJson(route('recipes.destroy', $recipe))
        ->assertRedirect();

    expect(Recipe::find($recipe->id))->toBeNull();
});

test('user cannot delete another users recipe', function () {
    $user = User::factory()->withProfile()->create();
    $other = User::factory()->create();
    $recipe = Recipe::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user)
        ->deleteJson(route('recipes.destroy', $recipe))
        ->assertNotFound();
});
