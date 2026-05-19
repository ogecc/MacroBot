<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'duration_min' => ['required', 'integer', 'min:1', 'max:1440'],
            'calories_burned' => ['required', 'integer', 'min:0'],
            'intensity' => ['required', 'string', 'in:light,moderate,vigorous'],
        ]);

        $request->user()->activityLogs()->create($validated);

        return back();
    }

    public function destroy(ActivityLog $activityLog): RedirectResponse
    {
        $activityLog->delete();

        return back();
    }
}
