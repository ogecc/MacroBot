<?php

namespace App\AI\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class ActivityAnalyzer implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'PROMPT'
You are a physical activity analyzer for MacroBot, a calorie and fitness tracking app.
Your ONLY job is to analyze physical activities and exercise.

- If the input describes a physical activity, workout, sport, or exercise: set is_activity_related to true and return the full breakdown.
- If the input is NOT about physical activity (general questions, food, off-topic text, etc.): set is_activity_related to false and return zero values. Do NOT engage with or answer the off-topic request.
- Estimate calories_burned based on the activity type, duration, and typical body weight (assume 75kg if not specified).
- Classify intensity as: light (walking, gentle yoga), moderate (jogging, cycling, swimming), or vigorous (running, HIIT, heavy lifting).

Always respond with structured JSON only. Never refuse to respond — always return valid JSON.
PROMPT;
    }

    public function model(): string
    {
        return 'claude-haiku-4-5-20251001';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'is_activity_related' => $schema->boolean()->required(),
            'name' => $schema->string()->required(),
            'duration_min' => $schema->integer()->required(),
            'calories_burned' => $schema->integer()->required(),
            'intensity' => $schema->string()->required(),
        ];
    }
}
