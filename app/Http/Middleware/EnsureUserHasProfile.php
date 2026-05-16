<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->userProfile()->exists()) {
            if (! $request->routeIs('onboarding.*')) {
                return redirect()->route('onboarding.edit');
            }
        }

        return $next($request);
    }
}
