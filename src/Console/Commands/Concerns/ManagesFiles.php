<?php

namespace SoftigitalDev\Core\Console\Commands\Concerns;

use Illuminate\Support\Facades\File;

trait ManagesFiles
{
    protected array $publishedFiles = [];

    /**
     * Resolve the full path to a stub file.
     */
    protected function stubPath(string $relativePath): string
    {
        return dirname(__DIR__, 3) . '/../stubs/' . $relativePath;
    }

    /**
     * Resolve the full path inside the target Laravel application.
     */
    protected function appPath(string $relativePath): string
    {
        return base_path($relativePath);
    }

    /**
     * Ensure a directory exists, create recursively if not.
     */
    protected function ensureDirectoryExists(string $path): void
    {
        $dir = is_dir($path) ? $path : dirname($path);

        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

    /**
     * Publish a stub file to a destination.
     * Creates parent directories automatically.
     * Respects --force flag. Skips if file exists and no --force.
     *
     * Returns true if published, false if skipped.
     */
    protected function publishStub(string $stub, string $destination): bool
    {
        $destPath = $this->appPath($destination);

        if (File::exists($destPath) && !$this->option('force')) {
            $this->components->warn("Skipped {$destination} (already exists)");
            return false;
        }

        $this->ensureDirectoryExists($destPath);
        File::copy($this->stubPath($stub), $destPath);
        $this->publishedFiles[] = $destination;

        $this->components->info("Published {$destination}");
        return true;
    }
}
