<?php

namespace App\Http\Controllers;

use App\Ai\Agents\MealAnalyzer;
use App\Http\Requests\MealAnalysisRequest;
use Illuminate\Http\JsonResponse;
use Laravel\Ai\Exceptions\RateLimitedException;

class MealAnalysisController extends Controller
{
    public function __construct(private readonly MealAnalyzer $analyzer) {}

    public function store(MealAnalysisRequest $request): JsonResponse
    {
        $prompt = $this->buildPrompt($request->input('description'));
        $attachments = [];

        if ($request->hasFile('image')) {
            $attachments[] = $request->file('image');
        }

        try {
            $result = $this->analyzer->prompt($prompt, $attachments);
        } catch (\Throwable $e) {
            logger()->error('MealAnalyzer failed: ' . $e->getMessage());
            $message = $e instanceof RateLimitedException
                ? 'AI is busy right now. Please wait a moment and try again.'
                : 'Failed to analyze meal. Please try again.';
            abort(422, $message);
        }

        if (! ($result['is_food_related'] ?? true)) {
            abort(422, 'Please describe a meal or food item. MacroBot only analyses food.');
        }

        return response()->json($result);
    }

    private function buildPrompt(?string $description): string
    {
        $base = 'Analyze the meal and return the nutritional breakdown as structured JSON. Be as accurate as possible with calorie and macro estimates.';

        if ($description) {
            $base .= ' Meal description: ' . $description;
        }

        return $base;
    }
}
