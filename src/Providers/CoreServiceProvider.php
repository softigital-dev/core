<?php

namespace SoftigitalDev\Core\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use SoftigitalDev\Core\Console\Commands\Create\CreateRouteCommand;
use SoftigitalDev\Core\Console\Commands\Create\MakeCrudCommand;
use SoftigitalDev\Core\Console\Commands\Create\MakeServiceCommand;
use SoftigitalDev\Core\Console\Commands\InstallCommand;
use SoftigitalDev\Core\Http\Middleware\ForceJsonResponseForApiRequests;
use SoftigitalDev\Core\Http\Middleware\OptionalSanctumAuth;

class CoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Publish config file
        $this->publishes([
            __DIR__ . '/../../config/softigital-core.php' => config_path('softigital-core.php'),
        ], 'softigital-config');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                CreateRouteCommand::class,
                MakeServiceCommand::class,
                MakeCrudCommand::class,
            ]);
        }

        // Register middleware
        $this->registerMiddleware();
    }

    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/softigital-core.php',
            'softigital-core'
        );
    }

    /**
     * Register package middleware.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);

        // Always register the alias so routes don't break
        // The middleware itself checks config internally
        $router->aliasMiddleware('auth.optional', OptionalSanctumAuth::class);

        // Auto-apply ForceJsonResponse to API middleware group
        // Only if both enabled AND auto_apply are true
        if (config('softigital-core.middleware.force_json.enabled', true)
            && config('softigital-core.middleware.force_json.auto_apply', true)) {
            $router->pushMiddlewareToGroup('api', ForceJsonResponseForApiRequests::class);
        }
    }
}
