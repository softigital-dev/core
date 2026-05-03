<?php

namespace SoftigitalDev\Core\Providers;

use Illuminate\Support\ServiceProvider;
use SoftigitalDev\Core\Console\Commands\Create\CreateRouteCommand;
use SoftigitalDev\Core\Console\Commands\Create\MakeCrudCommand;
use SoftigitalDev\Core\Console\Commands\Create\MakeServiceCommand;
use SoftigitalDev\Core\Console\Commands\InstallCommand;

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
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/softigital-core.php',
            'softigital-core'
        );
    }
}
