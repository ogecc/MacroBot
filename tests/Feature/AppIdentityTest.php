<?php

use App\Models\User;

test('welcome page loads successfully', function () {
    $this->get(route('home'))->assertOk();
});

test('welcome page is the Welcome Inertia component', function () {
    $this->get(route('home'))->assertInertia(fn ($page) => $page->component('Welcome'));
});

test('app name is MacroBot', function () {
    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('name', 'MacroBot'));
});

test('dashboard shares MacroBot as app name', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where('name', 'MacroBot'));
});
