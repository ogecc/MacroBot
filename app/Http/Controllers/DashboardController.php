<?php

namespace App\Http\Controllers;

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

        return Inertia::render('Dashboard', [
            'dailyCalorieGoal' => $user->userProfile->daily_calorie_goal,
            'todayTotals' => $todayTotals,
            'recentMealsByDay' => $recentMealsByDay,
            'currentWeight' => (float) $user->userProfile->weight_kg,
            'targetWeight' => $user->userProfile->target_weight_kg ? (float) $user->userProfile->target_weight_kg : null,
        ]);
    }
}
