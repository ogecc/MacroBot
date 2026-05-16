<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class MealAnalyzer implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'PROMPT'
You are a meal nutrition analyzer for MacroBot, a calorie and macro tracking app.
Your ONLY job is to analyze food and meals.

- If the input describes food, a meal, or shows an image containing food or drink: set is_food_related to true and return the full nutritional breakdown.
- If the input is NOT about food or meals (general questions, off-topic text, creative requests, etc.): set is_food_related to false and return empty arrays. Do NOT engage with or answer the off-topic request.

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
            'is_food_related' => $schema->boolean()->required(),
            'items' => $schema->array()
                ->items(
                    $schema->object(fn (JsonSchema $s) => [
                        'name' => $s->string()->required(),
                        'quantity' => $s->integer()->required(),
                        'unit' => $s->string()->required(),
                        'calories' => $s->integer()->required(),
                        'protein_g' => $s->number()->required(),
                        'carbs_g' => $s->number()->required(),
                        'fat_g' => $s->number()->required(),
                    ])
                )
                ->required(),
            'meal_name_suggestion' => $schema->string()->required(),
        ];
    }
}
