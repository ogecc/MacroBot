<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMealRequest;
use App\Http\Requests\UpdateMealRequest;
use App\Models\Meal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class MealController extends Controller
{
    public function store(StoreMealRequest $request): RedirectResponse
    {
        $meal = $request->user()->meals()->create($request->safe()->except('items'));
        $meal->items()->createMany($request->validated('items'));

        return to_route('dashboard')->with('toast', ['type' => 'success', 'message' => 'Meal logged!']);
    }

    public function update(UpdateMealRequest $request, Meal $meal): RedirectResponse
    {
        Gate::authorize('update', $meal);

        $meal->update($request->safe()->except('items'));
        $meal->items()->delete();
        $meal->items()->createMany($request->validated('items'));

        return to_route('dashboard')->with('toast', ['type' => 'success', 'message' => 'Meal updated!']);
    }

    public function destroy(Meal $meal): RedirectResponse
    {
        Gate::authorize('delete', $meal);

        $meal->delete();

        return to_route('dashboard')->with('toast', ['type' => 'success', 'message' => 'Meal deleted.']);
    }
}
