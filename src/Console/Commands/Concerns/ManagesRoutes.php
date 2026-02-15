<?php

namespace SoftigitalDev\Core\Console\Commands\Concerns;

use Illuminate\Support\Facades\File;

trait ManagesRoutes
{
    /**
     * Ensure routes/v1/api.php exists.
     *
     * Checks in order:
     * 1. routes/v1/api.php already exists → done
     * 2. routes/api.php exists → move it to routes/v1/
     * 3. Neither exists → run install:api, then move
     *
     * Always updates bootstrap/app.php afterward.
     */
    protected function ensureApiRouteInV1(): void
    {
        $v1ApiPath = $this->appPath('routes/v1/api.php');
        $rootApiPath = $this->appPath('routes/api.php');

        if (File::exists($v1ApiPath)) {
            $this->components->info('routes/v1/api.php already exists');
            $this->updateBootstrapApp();
            return;
        }

        $this->ensureDirectoryExists($v1ApiPath);

        if (File::exists($rootApiPath)) {
            File::move($rootApiPath, $v1ApiPath);
            $this->components->info('Moved routes/api.php → routes/v1/api.php');
        } else {
            $this->components->info('Installing API routes via install:api...');
            $this->callSilently('install:api');

            // After install:api, api.php is at routes/api.php
            if (File::exists($rootApiPath)) {
                File::move($rootApiPath, $v1ApiPath);
                $this->components->info('Moved routes/api.php → routes/v1/api.php');
            } else {
                $this->components->error('Failed to create routes/api.php via install:api');
                return;
            }
        }

        $this->updateBootstrapApp();
    }

    /**
     * Update bootstrap/app.php to use routes/v1/api.php via a then: closure.
     * Removes the old api: line and injects the v1 routing.
     * Adds the Route facade import if missing.
     */
    protected function updateBootstrapApp(): void
    {
        $bootstrapPath = $this->appPath('bootstrap/app.php');

        if (!File::exists($bootstrapPath)) {
            $this->components->error('bootstrap/app.php not found');
            return;
        }

        $content = File::get($bootstrapPath);

        // Already configured
        if (str_contains($content, "routes/v1/api.php")) {
            $this->components->info('bootstrap/app.php already configured for v1 routing');
            return;
        }

        // Add Route facade import if missing
        if (!str_contains($content, 'use Illuminate\Support\Facades\Route')) {
            $content = str_replace(
                'use Illuminate\Foundation\Application;',
                "use Illuminate\Foundation\Application;\nuse Illuminate\Support\Facades\Route;",
                $content,
            );
        }

        // Remove old api: line (handles various formats)
        // Pattern matches: api: __DIR__.'/../routes/api.php' or api: __DIR__.'/../routes/api.php',
        $content = preg_replace(
            '/\s*api\s*:\s*__DIR__\s*\.\s*[\'"][^\'"]*api\.php[\'"]\s*,?\s*\n?/',
            '',
            $content,
        );

        // Also handle the 'using' style if present
        $content = preg_replace(
            '/\s*->using\s*\(\s*[\'"]api[\'"]\s*,\s*[^)]+\)\s*,?\s*\n?/',
            '',
            $content,
        );

        // Inject the then: closure into withRouting()
        $thenClosure = <<<'PHP'
        then: function () {
                Route::prefix('api/v1')
                    ->middleware('api')
                    ->group(base_path('routes/v1/api.php'));
            },
PHP;

        // Find the closing of withRouting and inject before it
        if (str_contains($content, 'then:')) {
            $this->components->info('bootstrap/app.php already has a then: closure');
            return;
        }

        // Insert then: before the closing ) of withRouting
        // Match the last parameter line before the closing )
        $content = preg_replace_callback(
            '/(->withRouting\()(.*?)(^\s*\))/ms',
            function ($matches) use ($thenClosure) {
                $opening = $matches[1];
                $params = rtrim($matches[2]);
                $closing = $matches[3];

                // Ensure trailing comma on last param
                if (!str_ends_with(rtrim($params), ',')) {
                    $params = rtrim($params) . ',';
                }

                return $opening . $params . "\n        " . $thenClosure . "\n    " . trim($closing);
            },
            $content,
        );

        File::put($bootstrapPath, $content);
        $this->components->info('Updated bootstrap/app.php with v1 routing');
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

        // Append the require at the end
        $content = rtrim($content) . "\n\n" . $requireLine . "\n";
        File::put($apiPath, $content);

        $this->components->info("Added require for {$routeFile} in routes/v1/api.php");
    }
}
