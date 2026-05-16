<?php

use App\Models\User;
use App\Models\UserProfile;

$validPayload = [
    'age' => 28,
    'gender' => 'male',
    'height_cm' => 178,
    'weight_kg' => 75.5,
    'activity_level' => 'moderately_active',
    'goal' => 'maintain',
    'target_weight_kg' => null,
];

test('guest is redirected to login when visiting onboarding', function () {
    $this->get(route('onboarding.edit'))->assertRedirect(route('login'));
});

test('user without profile is redirected to onboarding when visiting dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('onboarding.edit'));
});

test('user with profile can access dashboard without being redirected', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('onboarding page renders for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('onboarding.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Onboarding/Edit'));
});

test('onboarding can be submitted with valid data', function () use (&$validPayload) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.update'), $validPayload)
        ->assertRedirect(route('dashboard'));

    expect(UserProfile::where('user_id', $user->id)->exists())->toBeTrue();
});

test('onboarding stores daily_calorie_goal on submission', function () use (&$validPayload) {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('onboarding.update'), $validPayload);

    $profile = UserProfile::where('user_id', $user->id)->first();
    expect($profile->daily_calorie_goal)->toBeGreaterThan(0);
});

test('onboarding resubmission updates existing profile instead of creating duplicate', function () use (&$validPayload) {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)->post(route('onboarding.update'), $validPayload);

    expect(UserProfile::where('user_id', $user->id)->count())->toBe(1);
});

test('onboarding fails with age below 13', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.update'), ['age' => 10, 'gender' => 'male', 'height_cm' => 170, 'weight_kg' => 60, 'activity_level' => 'sedentary', 'goal' => 'maintain'])
        ->assertSessionHasErrors('age');
});

test('onboarding fails with age above 120', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.update'), ['age' => 130, 'gender' => 'male', 'height_cm' => 170, 'weight_kg' => 60, 'activity_level' => 'sedentary', 'goal' => 'maintain'])
        ->assertSessionHasErrors('age');
});

test('onboarding fails with invalid activity level', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.update'), ['age' => 25, 'gender' => 'male', 'height_cm' => 170, 'weight_kg' => 60, 'activity_level' => 'super_active', 'goal' => 'maintain'])
        ->assertSessionHasErrors('activity_level');
});
