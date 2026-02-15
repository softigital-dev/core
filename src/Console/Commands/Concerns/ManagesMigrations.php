<?php

namespace SoftigitalDev\Core\Console\Commands\Concerns;

use Illuminate\Support\Facades\File;

trait ManagesMigrations
{
    /**
     * Publish a migration stub with a fresh timestamp.
     * Skips if a migration with the same name already exists.
     *
     * Returns true if published, false if skipped.
     */
    protected function publishMigration(string $stub, string $migrationName): bool
    {
        if ($this->migrationExists($migrationName)) {
            $this->components->warn("Skipped migration {$migrationName} (already exists)");
            return false;
        }

        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$migrationName}.php";
        $destination = "database/migrations/{$filename}";
        $destPath = $this->appPath($destination);

        $this->ensureDirectoryExists($destPath);
        File::copy($this->stubPath($stub), $destPath);

        $this->components->info("Published migration {$filename}");
        return true;
    }

    /**
     * Run pending migrations.
     */
    protected function runMigrations(): void
    {
        $this->components->info('Running migrations...');
        $this->call('migrate');
    }

    /**
     * Check if a migration with the given name already exists.
     */
    protected function migrationExists(string $migrationName): bool
    {
        $migrationsPath = $this->appPath('database/migrations');

        if (!File::isDirectory($migrationsPath)) {
            return false;
        }

        $files = File::glob("{$migrationsPath}/*_{$migrationName}.php");

        return !empty($files);
    }
}
