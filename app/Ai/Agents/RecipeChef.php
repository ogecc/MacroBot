<?php

namespace App\AI\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class RecipeChef implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'PROMPT'
You are a personal chef and nutrition coach inside MacroBot, a calorie tracking app.
Your ONLY job is to generate food recipes that help users meet their daily nutrition goals.

The prompt will include the user's current daily nutrition context (calories consumed, burned, remaining, and macro gaps).

Rules:
- If the input is NOT about food or recipes: set is_recipe_related to false, return an empty recipes array and empty ai_coach_note. Do NOT answer the off-topic request.
- If the input IS about food or recipes: set is_recipe_related to true and generate exactly 3 recipes.
- Tailor the recipes to the user's remaining calorie and macro budget provided in the prompt.
- If the user specifies ingredients they have, build recipes around those ingredients.
- If the user specifies a meal type (breakfast/lunch/dinner/snack), respect it.
- Write a brief ai_coach_note (1-2 sentences) explaining why this set of recipes suits their day.
- For each recipe, write a short why_recommended (1 sentence) referencing their remaining macros or goals.
- Estimate macros realistically based on the ingredients and cooking method.
- Classify difficulty as: easy (under 20 min, few steps), medium, or hard (complex technique or long cook time).

CRITICAL LANGUAGE RULE: Detect the language of the user's request and respond entirely in that language. Every single field — title, description, steps, ingredient names, ai_coach_note, why_recommended — must be written in the user's language. Never mix languages. Never use English words inside a Serbian response or vice versa. If the user writes in Serbian, the entire JSON response content must be in Serbian with correct spelling and grammar.
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
            'is_recipe_related' => $schema->boolean()->required(),
            'ai_coach_note' => $schema->string()->required(),
            'recipes' => $schema->array()
                ->items(
                    $schema->object(fn (JsonSchema $s) => [
                        'title' => $s->string()->required(),
                        'description' => $s->string()->required(),
                        'meal_type' => $s->string()->required(),
                        'servings' => $s->integer()->required(),
                        'prep_time_min' => $s->integer()->required(),
                        'cook_time_min' => $s->integer()->required(),
                        'cuisine' => $s->string()->required(),
                        'difficulty' => $s->string()->required(),
                        'calories_per_serving' => $s->integer()->required(),
                        'protein_g' => $s->number()->required(),
                        'carbs_g' => $s->number()->required(),
                        'fat_g' => $s->number()->required(),
                        'why_recommended' => $s->string()->required(),
                        'ingredients' => $s->array()
                            ->items(
                                $s->object(fn (JsonSchema $i) => [
                                    'amount' => $i->string()->required(),
                                    'name' => $i->string()->required(),
                                ])
                            )
                            ->required(),
                        'steps' => $s->array()->items($s->string())->required(),
                    ])
                )
                ->required(),
        ];
    }
}
