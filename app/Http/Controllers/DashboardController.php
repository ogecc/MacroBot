<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $today = now()->toDateString();

        $recentMeals = $user->meals()
            ->with('items')
            ->where('eaten_at', '>=', now()->subDays(6)->startOfDay())
            ->orderByDesc('eaten_at')
            ->get();

        $todayItems = $recentMeals
            ->filter(fn ($meal) => $meal->eaten_at->toDateString() === $today)
            ->flatMap->items;

        $todayTotals = [
            'calories' => (int) $todayItems->sum('calories'),
            'protein_g' => (float) $todayItems->sum('protein_g'),
            'carbs_g' => (float) $todayItems->sum('carbs_g'),
            'fat_g' => (float) $todayItems->sum('fat_g'),
        ];

        $recentMealsByDay = $recentMeals
            ->groupBy(fn ($meal) => $meal->eaten_at->toDateString())
            ->map(fn ($meals, $date) => [
                'date' => $date,
                'total_calories' => (int) $meals->flatMap->items->sum('calories'),
                'meals' => $meals->values(),
            ])
            ->values();

        $todayActivities = ActivityLog::where('user_id', $user->id)
            ->whereDate('logged_at', $today)
            ->orderBy('logged_at')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'duration_min' => $a->duration_min,
                'calories_burned' => $a->calories_burned,
                'intensity' => $a->intensity,
            ]);

        $todayActivityCalories = $todayActivities->sum('calories_burned');

        $firstWeightLog = $user->weightLogs()->orderBy('logged_at')->first();

        $mealDates = $user->meals()
            ->orderByDesc('eaten_at')
            ->pluck('eaten_at')
            ->map(fn ($dt) => Carbon::parse($dt)->toDateString())
            ->unique()
            ->values();

        $streak = 0;
        $checkDate = now()->startOfDay();

        if (! $mealDates->contains($checkDate->toDateString())) {
            $checkDate = $checkDate->subDay();
        }

        foreach ($mealDates as $date) {
            if ($date === $checkDate->toDateString()) {
                $streak++;
                $checkDate = $checkDate->subDay();
            } else {
                break;
            }
        }

        return Inertia::render('Dashboard', [
            'dailyCalorieGoal' => $user->userProfile->daily_calorie_goal,
            'goal' => $user->userProfile->goal,
            'goalAdjustment' => $user->userProfile->goal_adjustment,
            'todayTotals' => $todayTotals,
            'recentMealsByDay' => $recentMealsByDay,
            'currentWeight' => (float) $user->userProfile->weight_kg,
            'startWeight' => $firstWeightLog ? (float) $firstWeightLog->weight_kg : (float) $user->userProfile->weight_kg,
            'targetWeight' => $user->userProfile->target_weight_kg ? (float) $user->userProfile->target_weight_kg : null,
            'todayActivities' => $todayActivities,
            'todayActivityCalories' => (int) $todayActivityCalories,
            'voiceEnabled' => (bool) env('VOICE_TRANSCRIPTION_ENABLED', false),
            'streak' => $streak,
            'tutorialSeen' => (bool) $user->userProfile->tutorial_seen,
        ]);
    }
}
