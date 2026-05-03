<?php

namespace SoftigitalDev\Core\Console\Commands\Concerns;

use Illuminate\Support\Facades\File;

trait ManagesProviders
{
    /**
     * Add a provider class to bootstrap/providers.php if not already present.
     * Idempotent — safe to call multiple times with the same class.
     */
    protected function addProviderToBootstrap(string $providerClass): void
    {
        $bootstrapPath = $this->appPath('bootstrap/providers.php');

        if (!File::exists($bootstrapPath)) {
            $this->components->error('bootstrap/providers.php not found — is this a Laravel 11+ application?');
            return;
        }

        $normalised = ltrim($providerClass, '\\');

        $content = File::get($bootstrapPath);

        if (str_contains($content, $normalised)) {
            $this->components->warn("{$normalised} already registered in bootstrap/providers.php");
            return;
        }

        $content = preg_replace(
            '/^(\];)$/m',
            "    {$normalised}::class,\n];",
            $content,
        );

        File::put($bootstrapPath, $content);
        $this->components->info("Registered {$normalised} in bootstrap/providers.php");
    }
}
