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

        // Force Accept header to application/json
        // This middleware is applied to the 'api' group, so all requests here are API requests
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        // Ensure the response has JSON Content-Type if not already set
        if (!$response->headers->has('Content-Type')) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}
