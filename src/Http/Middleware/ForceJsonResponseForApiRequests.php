<?php

namespace SoftigitalDev\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponseForApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if middleware is enabled in config
        if (!config('softigital-core.middleware.force_json.enabled', true)) {
            return $next($request);
        }

        if (Str::startsWith($request->path(), 'api')) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
