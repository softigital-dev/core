<?php

namespace SoftigitalDev\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OptionalSanctumAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Check if middleware is enabled in config
        if (!config('softigital-core.middleware.optional_auth.enabled', true)) {
            return $next($request);
        }

        if ($request->bearerToken()) {
            try {
                auth()->guard('sanctum')->user(); // sets user if token valid
            } catch (\Exception $e) {
                // token invalid, ignore
            }
        }

        return $next($request);
    }
}
