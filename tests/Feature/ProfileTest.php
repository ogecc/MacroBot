<?php

use App\Models\User;

$validPayload = [
    'age' => 30,
    'gender' => 'female',
    'height_cm' => 165,
    'weight_kg' => 65.0,
    'goal' => 'lose',
    'target_weight_kg' => 60.0,
];

test('user can view their profile edit page', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->get(route('fitness.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Profile/Edit')
            ->has('profile')
        );
});

test('profile edit page contains current profile values', function () {
    $user = User::factory()->withProfile(['age' => 25, 'gender' => 'male'])->create();

    $this->actingAs($user)
        ->get(route('fitness.edit'))
        ->assertInertia(fn ($page) => $page
            ->where('profile.age', 25)
            ->where('profile.gender', 'male')
        );
});

test('user can update their profile', function () use (&$validPayload) {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->put(route('fitness.update'), $validPayload)
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('user_profiles', [
        'user_id' => $user->id,
        'age' => 30,
        'gender' => 'female',
        'height_cm' => 165,
    ]);
});

test('profile update recomputes daily_calorie_goal', function () use (&$validPayload) {
    $user = User::factory()->withProfile(['goal' => 'gain', 'daily_calorie_goal' => 9999])->create();

    $this->actingAs($user)
        ->put(route('fitness.update'), $validPayload);

    $user->userProfile->refresh();
    expect($user->userProfile->daily_calorie_goal)->not->toBe(9999);
});

test('profile update fails with invalid age', function () use (&$validPayload) {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->put(route('fitness.update'), array_merge($validPayload, ['age' => 5]))
        ->assertSessionHasErrors('age');
});

test('guest cannot access profile edit page', function () {
    $this->get(route('fitness.edit'))->assertRedirect(route('login'));
});
