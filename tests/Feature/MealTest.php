<?php

use App\Models\Meal;
use App\Models\MealItem;
use App\Models\User;

$validItems = [
    ['name' => 'Chicken', 'quantity' => 150, 'unit' => 'g', 'calories' => 247, 'protein_g' => 46.5, 'carbs_g' => 0.0, 'fat_g' => 5.3],
    ['name' => 'Rice', 'quantity' => 100, 'unit' => 'g', 'calories' => 130, 'protein_g' => 2.7, 'carbs_g' => 28.0, 'fat_g' => 0.3],
];

test('user can create a meal with valid items', function () use (&$validItems) {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->post(route('meals.store'), [
            'name' => 'Lunch',
            'eaten_at' => now()->toDateTimeString(),
            'items' => $validItems,
        ])
        ->assertRedirect(route('dashboard'));

    expect(Meal::where('user_id', $user->id)->count())->toBe(1);
});

test('meal store fails if items array is empty', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->post(route('meals.store'), [
            'name' => 'Lunch',
            'eaten_at' => now()->toDateTimeString(),
            'items' => [],
        ])
        ->assertSessionHasErrors('items');
});

test('meal store saves all item macros correctly', function () use (&$validItems) {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->post(route('meals.store'), [
            'name' => 'Lunch',
            'eaten_at' => now()->toDateTimeString(),
            'items' => $validItems,
        ]);

    $meal = Meal::where('user_id', $user->id)->with('items')->first();
    expect($meal->items)->toHaveCount(2);
    expect((int) $meal->items->sum('calories'))->toBe(377);
});

test('user can update their own meal', function () use (&$validItems) {
    $user = User::factory()->withProfile()->create();
    $meal = Meal::factory()->for($user)->create(['name' => 'Breakfast']);

    $this->actingAs($user)
        ->put(route('meals.update', $meal), [
            'name' => 'Brunch',
            'eaten_at' => now()->toDateTimeString(),
            'items' => $validItems,
        ])
        ->assertRedirect(route('dashboard'));

    expect($meal->fresh()->name)->toBe('Brunch');
});

test('user cannot update another users meal', function () use (&$validItems) {
    $user = User::factory()->withProfile()->create();
    $other = User::factory()->withProfile()->create();
    $meal = Meal::factory()->for($other)->create();

    $this->actingAs($user)
        ->put(route('meals.update', $meal), [
            'name' => 'Hack',
            'eaten_at' => now()->toDateTimeString(),
            'items' => $validItems,
        ])
        ->assertForbidden();
});

test('user can delete their own meal', function () {
    $user = User::factory()->withProfile()->create();
    $meal = Meal::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('meals.destroy', $meal))
        ->assertRedirect(route('dashboard'));

    expect(Meal::find($meal->id))->toBeNull();
});

test('user cannot delete another users meal', function () {
    $user = User::factory()->withProfile()->create();
    $other = User::factory()->withProfile()->create();
    $meal = Meal::factory()->for($other)->create();

    $this->actingAs($user)
        ->delete(route('meals.destroy', $meal))
        ->assertForbidden();
});

test('deleting a meal cascades and deletes its items', function () {
    $user = User::factory()->withProfile()->create();
    $meal = Meal::factory()->for($user)->create();
    $meal->items()->create([
        'name' => 'Egg', 'quantity' => 1, 'unit' => 'piece',
        'calories' => 70, 'protein_g' => 6, 'carbs_g' => 0, 'fat_g' => 5,
    ]);

    $this->actingAs($user)->delete(route('meals.destroy', $meal));

    expect(MealItem::where('meal_id', $meal->id)->count())->toBe(0);
});
