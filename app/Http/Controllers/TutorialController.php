<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TutorialController extends Controller
{
    public function dismiss(Request $request): JsonResponse
    {
        $request->user()->userProfile->update(['tutorial_seen' => true]);

        return response()->json(['ok' => true]);
    }
}
