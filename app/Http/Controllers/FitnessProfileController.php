<?php

namespace App\Http\Controllers;

use App\Http\Requests\OnboardingRequest;
use App\Services\CalorieGoalCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FitnessProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'profile' => $request->user()->userProfile,
        ]);
    }

    public function update(OnboardingRequest $request, CalorieGoalCalculator $calculator): RedirectResponse
    {
        $profile = $request->user()->userProfile;

        $profile->fill($request->validated());
        $profile->daily_calorie_goal = $calculator->calculate($profile);
        $profile->save();

        return to_route('fitness.edit')->with('toast', ['type' => 'success', 'message' => 'Profile updated!']);
    }

    public function updateWeight(Request $request, CalorieGoalCalculator $calculator): JsonResponse
    {
        $request->validate(['weight_kg' => ['required', 'numeric', 'min:1', 'max:999.99']]);

        $profile = $request->user()->userProfile;
        $profile->weight_kg = round((float) $request->input('weight_kg'), 1);
        $profile->daily_calorie_goal = $calculator->calculate($profile);
        $profile->save();

        return response()->json(['weight_kg' => (float) $profile->weight_kg]);
    }
}
