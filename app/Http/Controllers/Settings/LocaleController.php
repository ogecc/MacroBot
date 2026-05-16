<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    private const SUPPORTED = ['en', 'es', 'de', 'fr', 'sr'];

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'locale' => ['required', 'string', 'in:' . implode(',', self::SUPPORTED)],
        ]);

        $request->user()->userProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['locale' => $request->input('locale')],
        );

        return back();
    }
}
