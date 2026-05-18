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
            'prompt' => ['required', 'string'],
            'meal_type' => ['nullable', 'string', 'max:50'],
            'servings' => ['required', 'integer', 'min:1'],
            'prep_time_min' => ['required', 'integer', 'min:0'],
            'cook_time_min' => ['required', 'integer', 'min:0'],
            'cuisine' => ['nullable', 'string', 'max:50'],
            'difficulty' => ['required', 'string', 'max:50'],
            'calories_per_serving' => ['required', 'integer', 'min:0'],
            'protein_g' => ['required', 'numeric', 'min:0'],
            'carbs_g' => ['required', 'numeric', 'min:0'],
            'fat_g' => ['required', 'numeric', 'min:0'],
            'why_recommended' => ['nullable', 'string'],
            'ingredients' => ['required', 'array', 'min:1'],
            'ingredients.*.amount' => ['required', 'string'],
            'ingredients.*.name' => ['required', 'string'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*' => ['required', 'string'],
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

    public function eat(Recipe $recipe): RedirectResponse
    {
        $meal = $recipe->user->meals()->create([
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
