<?php

use App\Models\ActivityLog;
use App\Models\User;

test('user can log an activity', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->postJson(route('activities.store'), [
            'name' => 'Running',
            'description' => 'Ran 5km',
            'duration_min' => 30,
            'calories_burned' => 300,
            'intensity' => 'moderate',
        ])
        ->assertRedirect();

    expect(ActivityLog::where('user_id', $user->id)->count())->toBe(1);
});

test('user can delete their own activity', function () {
    $user = User::factory()->withProfile()->create();
    $activity = ActivityLog::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->deleteJson(route('activities.destroy', $activity))
        ->assertRedirect();

    expect(ActivityLog::find($activity->id))->toBeNull();
});

test('user cannot delete another users activity', function () {
    $user = User::factory()->withProfile()->create();
    $other = User::factory()->withProfile()->create();
    $activity = ActivityLog::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user)
        ->deleteJson(route('activities.destroy', $activity))
        ->assertNotFound();
});

test('today activity calories are summed in dashboard', function () {
    $user = User::factory()->withProfile()->create();

    ActivityLog::factory()->create(['user_id' => $user->id, 'calories_burned' => 200, 'logged_at' => now()]);
    ActivityLog::factory()->create(['user_id' => $user->id, 'calories_burned' => 150, 'logged_at' => now()]);
    ActivityLog::factory()->create(['user_id' => $user->id, 'calories_burned' => 100, 'logged_at' => now()->subDay()]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('todayActivityCalories', 350));
});

test('dashboard only includes todays activity calories', function () {
    $user = User::factory()->withProfile()->create();

    ActivityLog::factory()->create(['user_id' => $user->id, 'calories_burned' => 500, 'logged_at' => now()->subDay()]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('todayActivityCalories', 0));
});
