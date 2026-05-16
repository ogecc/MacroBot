<?php

use App\AI\Agents\ActivityAnalyzer;
use App\Models\User;

test('endpoint requires authentication', function () {
    $this->postJson(route('activities.analyze'))
        ->assertUnauthorized();
});

test('returns 422 if neither image nor description provided', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->postJson(route('activities.analyze'), [])
        ->assertUnprocessable();
});

test('returns 422 when description is too short', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->postJson(route('activities.analyze'), ['description' => 'hi'])
        ->assertUnprocessable();
});

test('returns structured json with valid description', function () {
    $user = User::factory()->withProfile()->create();

    $fakeResponse = [
        'is_activity_related' => true,
        'name' => 'Running',
        'duration_min' => 30,
        'calories_burned' => 300,
        'intensity' => 'moderate',
    ];

    ActivityAnalyzer::fake([$fakeResponse]);

    $this->actingAs($user)
        ->postJson(route('activities.analyze'), ['description' => 'Ran 5km in 30 minutes'])
        ->assertOk()
        ->assertJsonStructure(['name', 'duration_min', 'calories_burned', 'intensity'])
        ->assertJsonPath('name', 'Running');
});

test('returns 422 when input is not activity related', function () {
    $user = User::factory()->withProfile()->create();

    ActivityAnalyzer::fake([[
        'is_activity_related' => false,
        'name' => '',
        'duration_min' => 0,
        'calories_burned' => 0,
        'intensity' => 'light',
    ]]);

    $this->actingAs($user)
        ->postJson(route('activities.analyze'), ['description' => 'Write me a poem'])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Please describe a physical activity. MacroBot only analyses exercise.');
});

test('returns 422 when ai agent throws an exception', function () {
    $user = User::factory()->withProfile()->create();

    ActivityAnalyzer::fake(fn () => throw new Exception('AI failed'));

    $this->actingAs($user)
        ->postJson(route('activities.analyze'), ['description' => 'Went for a run'])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Failed to analyze activity. Please try again.');
});
