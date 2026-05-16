<?php

namespace App\Http\Controllers;

use App\AI\Agents\ActivityAnalyzer;
use App\Http\Requests\ActivityAnalysisRequest;
use Illuminate\Http\JsonResponse;
use Laravel\Ai\Exceptions\RateLimitedException;

class ActivityAnalysisController extends Controller
{
    public function __construct(private readonly ActivityAnalyzer $analyzer) {}

    public function store(ActivityAnalysisRequest $request): JsonResponse
    {
        $prompt = $this->buildPrompt($request->input('description'));

        try {
            $result = $this->analyzer->prompt($prompt);
        } catch (\Throwable $e) {
            logger()->error('ActivityAnalyzer failed: '.$e->getMessage());
            $message = $e instanceof RateLimitedException
                ? 'AI is busy right now. Please wait a moment and try again.'
                : 'Failed to analyze activity. Please try again.';
            abort(422, $message);
        }

        if (! ($result['is_activity_related'] ?? true)) {
            abort(422, 'Please describe a physical activity. MacroBot only analyses exercise.');
        }

        return response()->json($result);
    }

    private function buildPrompt(?string $description): string
    {
        $base = 'Analyze the physical activity and return the breakdown as structured JSON. Be as accurate as possible with calorie burn estimates.';

        if ($description) {
            $base .= ' Activity description: '.$description;
        }

        return $base;
    }
}
