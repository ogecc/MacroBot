<?php

namespace App\Http\Controllers;

use App\Ai\Agents\RecipeChef;
use App\Http\Requests\RecipeGenerationRequest;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Laravel\Ai\Exceptions\RateLimitedException;

class RecipeGenerationController extends Controller
{
    public function __construct(private readonly RecipeChef $chef) {}

    public function store(RecipeGenerationRequest $request): JsonResponse
    {
        set_time_limit(120);

        $user = $request->user();
        $today = now()->toDateString();

        $todayMealItems = $user->meals()
            ->with('items')
            ->whereDate('eaten_at', $today)
            ->get()
            ->flatMap->items;

        $caloriesConsumed = (int) $todayMealItems->sum('calories');
        $proteinConsumed = (float) $todayMealItems->sum('protein_g');
        $carbsConsumed = (float) $todayMealItems->sum('carbs_g');
        $fatConsumed = (float) $todayMealItems->sum('fat_g');

        $caloriesBurned = (int) ActivityLog::where('user_id', $user->id)
            ->whereDate('logged_at', $today)
            ->sum('calories_burned');

        $prompt = $this->buildPrompt(
            userPrompt: $request->input('prompt'),
            mealType: $request->input('meal_type'),
            dailyCalorieGoal: (int) $user->userProfile->daily_calorie_goal,
            caloriesConsumed: $caloriesConsumed,
            caloriesBurned: $caloriesBurned,
            proteinConsumed: $proteinConsumed,
            carbsConsumed: $carbsConsumed,
            fatConsumed: $fatConsumed,
            goal: $user->userProfile->goal instanceof \BackedEnum
                ? $user->userProfile->goal->value
                : (string) $user->userProfile->goal,
        );

        try {
            $result = $this->chef->prompt($prompt, timeout: 90);
        } catch (\Throwable $e) {
            logger()->error('RecipeChef failed: '.$e->getMessage());
            $message = $e instanceof RateLimitedException
                ? 'AI is busy right now. Please wait a moment and try again.'
                : 'Failed to generate recipes. Please try again.';
            abort(422, $message);
        }

        if (! ($result['is_recipe_related'] ?? true)) {
            abort(422, 'Please describe a meal or ingredients. MacroBot only generates food recipes.');
        }

        return response()->json([
            'ai_coach_note' => $result['ai_coach_note'],
            'recipes' => $result['recipes'],
        ]);
    }

    private function buildPrompt(
        string $userPrompt,
        ?string $mealType,
        int $dailyCalorieGoal,
        int $caloriesConsumed,
        int $caloriesBurned,
        float $proteinConsumed,
        float $carbsConsumed,
        float $fatConsumed,
        string $goal,
    ): string {
        $remaining = max(0, $dailyCalorieGoal + $caloriesBurned - $caloriesConsumed);
        $proteinGoal = (int) ($dailyCalorieGoal * 0.30 / 4);
        $carbsGoal = (int) ($dailyCalorieGoal * 0.45 / 4);
        $fatGoal = (int) ($dailyCalorieGoal * 0.25 / 9);
        $proteinRemaining = max(0, $proteinGoal - $proteinConsumed);
        $carbsRemaining = max(0, $carbsGoal - $carbsConsumed);
        $fatRemaining = max(0, $fatGoal - $fatConsumed);

        $context = <<<CONTEXT
User's nutrition context for today:
- Daily calorie goal: {$dailyCalorieGoal} kcal | User goal: {$goal}
- Calories consumed: {$caloriesConsumed} kcal | Burned: {$caloriesBurned} kcal | Remaining: {$remaining} kcal
- Protein remaining: {$proteinRemaining}g of ~{$proteinGoal}g target
- Carbs remaining: {$carbsRemaining}g of ~{$carbsGoal}g target
- Fat remaining: {$fatRemaining}g of ~{$fatGoal}g target
CONTEXT;

        $base = $context."\n\nUser request: Generate exactly 3 recipes. ".$userPrompt;

        if ($mealType) {
            $base .= ' The user wants recipes for: '.$mealType.'.';
        }

        return $base;
    }
}
