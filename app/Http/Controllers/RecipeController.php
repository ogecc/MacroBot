<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecipeController extends Controller
{
    public function index(Request $request): Response
    {
        $recipes = $request->user()
            ->recipes()
            ->latest()
            ->paginate(10);

        return Inertia::render('Recipes/Index', [
            'recipes' => $recipes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'prompt' => ['required', 'string', 'max:1000'],
            'meal_type' => ['nullable', 'string', 'max:50'],
            'servings' => ['required', 'integer', 'min:1', 'max:100'],
            'prep_time_min' => ['required', 'integer', 'min:0', 'max:1440'],
            'cook_time_min' => ['required', 'integer', 'min:0', 'max:1440'],
            'cuisine' => ['nullable', 'string', 'max:50'],
            'difficulty' => ['required', 'string', 'max:50'],
            'calories_per_serving' => ['required', 'integer', 'min:0', 'max:99999'],
            'protein_g' => ['required', 'numeric', 'min:0', 'max:9999'],
            'carbs_g' => ['required', 'numeric', 'min:0', 'max:9999'],
            'fat_g' => ['required', 'numeric', 'min:0', 'max:9999'],
            'why_recommended' => ['nullable', 'string', 'max:500'],
            'ingredients' => ['required', 'array', 'min:1', 'max:100'],
            'ingredients.*.amount' => ['required', 'string', 'max:100'],
            'ingredients.*.name' => ['required', 'string', 'max:200'],
            'steps' => ['required', 'array', 'min:1', 'max:50'],
            'steps.*' => ['required', 'string', 'max:2000'],
        ]);

        $request->user()->recipes()->create($validated);

        return to_route('recipes.index')->with('toast', ['type' => 'success', 'message' => 'Recipe saved!']);
    }

    public function show(Recipe $recipe): Response
    {
        return Inertia::render('Recipes/Show', [
            'recipe' => $recipe,
        ]);
    }

    public function eat(Request $request, Recipe $recipe): RedirectResponse
    {
        $meal = $request->user()->meals()->create([
            'name' => $recipe->title,
            'eaten_at' => now(),
        ]);

        $meal->items()->create([
            'name' => $recipe->title,
            'quantity' => 1,
            'unit' => 'serving',
            'calories' => $recipe->calories_per_serving,
            'protein_g' => $recipe->protein_g,
            'carbs_g' => $recipe->carbs_g,
            'fat_g' => $recipe->fat_g,
        ]);

        return to_route('dashboard')->with('toast', ['type' => 'success', 'message' => $recipe->title.' logged!']);
    }

    public function destroy(Recipe $recipe): RedirectResponse
    {
        $recipe->delete();

        return to_route('recipes.index')->with('toast', ['type' => 'success', 'message' => 'Recipe deleted.']);
    }
}
