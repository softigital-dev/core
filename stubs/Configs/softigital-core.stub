<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which middleware should be automatically registered and applied
    | to your application's API routes.
    |
    */

    'middleware' => [

        /*
        |--------------------------------------------------------------------------
        | Force JSON Response
        |--------------------------------------------------------------------------
        |
        | Automatically forces all API requests to accept JSON responses by
        | setting the Accept header to 'application/json'. This ensures
        | consistent API behavior and prevents HTML error pages.
        |
        | When enabled, this middleware is automatically applied to all routes
        | starting with 'api/'.
        |
        */

        'force_json' => [
            'enabled' => true,
            'auto_apply' => true, // Automatically apply to API middleware group
        ],

        /*
        |--------------------------------------------------------------------------
        | Optional Sanctum Authentication
        |--------------------------------------------------------------------------
        |
        | Provides optional authentication for routes that can work with or
        | without an authenticated user. If a valid token is provided, the user
        | is authenticated; otherwise, the request continues as a guest.
        |
        | This is useful for endpoints that have different behavior for
        | authenticated vs. guest users, without requiring authentication.
        |
        | Usage: Apply via alias 'auth.optional' in your routes
        |
        */

        'optional_auth' => [
            'enabled' => true,
        ],

    ],

];
