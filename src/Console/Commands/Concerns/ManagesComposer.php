<?php

namespace SoftigitalDev\Core\Console\Commands\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

trait ManagesComposer
{
    /**
     * Check if a composer package is installed in the target application.
     * Checks both require and require-dev sections.
     */
    protected function isPackageInstalled(string $package): bool
    {
        $composerPath = $this->appPath('composer.json');

        if (!File::exists($composerPath)) {
            return false;
        }

        $composer = json_decode(File::get($composerPath), true);

        return isset($composer['require'][$package])
            || isset($composer['require-dev'][$package]);
    }

    /**
     * Require a composer package in the target application.
     * Returns true on success, false on failure.
     */
    protected function requirePackage(string $package): bool
    {
        $this->components->info("Installing {$package}...");
        $this->newLine();

        $result = Process::path($this->appPath(''))
            ->tty()
            ->run("composer require {$package}");

        $this->newLine();

        if ($result->successful()) {
            $this->components->info("{$package} installed successfully");
            return true;
        }

        $this->components->error("Failed to install {$package}");
        $this->line($result->errorOutput());
        return false;
    }
}
