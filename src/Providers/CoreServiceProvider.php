<?php

namespace SoftigitalDev\Core\Providers;

use Illuminate\Support\ServiceProvider;
use SoftigitalDev\Core\Console\Commands\InstallCommand;

class CoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        //
    }
}
