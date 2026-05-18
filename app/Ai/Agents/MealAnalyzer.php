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
- If the input is NOT about food or meals (general questions, off-topic text, creative requests, etc.): set is_food_related to false, return empty arrays, and set ai_observation to an empty string. Do NOT engage with or answer the off-topic request.

For ai_observation: write one or two natural, conversational sentences describing what you can see in the image or what was described — e.g. "I can see a plate with grilled chicken breast (~150g), white rice (~100g), and steamed broccoli (~80g)." If input is text-only, briefly summarise what you understood. Keep it short and friendly.

CRITICAL LANGUAGE RULE: Detect the language of the user's input and respond entirely in that language. Food item names, units, meal_name_suggestion, and ai_observation must all be in the user's language. Never mix languages.
Always respond with structured JSON only. Never refuse to respond — always return valid JSON.
PROMPT;
    }

    public function model(): string
    {
        return 'claude-sonnet-4-6';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'is_food_related' => $schema->boolean()->required(),
            'ai_observation' => $schema->string()->required(),
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
