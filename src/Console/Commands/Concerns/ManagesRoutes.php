<?php

namespace SoftigitalDev\Core\Console\Commands\Concerns;

use Illuminate\Support\Facades\File;

trait ManagesRoutes
{
    /**
     * Ensure routes/v1/api.php exists.
     *
     * Creates the file from stub if absent. Does NOT move or modify
     * routes/api.php and does NOT touch bootstrap/app.php.
     * Route registration (api/v1 prefix) is handled at runtime by
     * the published SoftigitalServiceProvider.
     */
    protected function ensureApiRouteInV1(): void
    {
        $v1ApiPath = $this->appPath('routes/v1/api.php');

        if (File::exists($v1ApiPath)) {
            $this->components->info('routes/v1/api.php already exists');
            return;
        }

        $this->ensureDirectoryExists($v1ApiPath);

        File::copy($this->stubPath('Routes/v1-api.stub'), $v1ApiPath);
        $this->components->info('Created routes/v1/api.php');
    }

    /**
     * Add a require statement to routes/v1/api.php if not already present.
     */
    protected function addRouteRequire(string $routeFile): void
    {
        $apiPath = $this->appPath('routes/v1/api.php');
        $requireLine = "require __DIR__.'/{$routeFile}';";

        if (!File::exists($apiPath)) {
            $this->components->error('routes/v1/api.php not found');
            return;
        }

        $content = File::get($apiPath);

        if (str_contains($content, $requireLine)) {
            $this->components->info("Route require for {$routeFile} already exists");
            return;
        }

        $content = rtrim($content) . "\n\n" . $requireLine . "\n";
        File::put($apiPath, $content);

        $this->components->info("Added require for {$routeFile} in routes/v1/api.php");
    }
}
