<?php

namespace App\Http\Controllers;

use App\Http\Requests\OnboardingRequest;
use App\Services\CalorieGoalCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Onboarding/Edit', [
            'profile' => $request->user()?->userProfile,
        ]);
    }

    public function update(OnboardingRequest $request, CalorieGoalCalculator $calculator): RedirectResponse
    {
        $profile = $request->user()->userProfile()->firstOrNew();

        $profile->fill($request->validated());
        $profile->daily_calorie_goal = $calculator->calculate($profile);
        $profile->user_id = $request->user()->id;
        $profile->save();

        return to_route('dashboard')->with('toast', [
            'type' => 'success',
            'message' => 'Profile saved!',
        ]);
    }
}
