<?php

namespace SoftigitalDev\Core\Console\Commands\Create;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use SoftigitalDev\Core\Console\Commands\Concerns\ManagesFiles;
use SoftigitalDev\Core\Console\Commands\Concerns\ManagesRoutes;

class MakeCrudCommand extends Command
{
    use ManagesFiles;
    use ManagesRoutes;

    protected $signature = 'make:crud 
                            {name : The name of the model (e.g., Post, BlogPost)}
                            {--api-prefix=v1 : The API version prefix for routes}
                            {--force : Overwrite existing files}';

    protected $description = 'Generate CRUD (Model, Migration, Service, Controller, Requests, Resource, Routes) for a given model';

    protected string $modelName;
    protected string $modelNamePlural;
    protected string $modelVariable;
    protected string $modelVariablePlural;
    protected string $routeName;

    public function handle(): int
    {
        $this->resolveNames();

        $this->components->info("Generating CRUD for: {$this->modelName}");
        $this->newLine();

        $this->generateModel();
        $this->generateMigration();
        $this->generateService();
        $this->generateController();
        $this->generateRequest('Store');
        $this->generateRequest('Update');
        $this->generateResource();
        $this->generateRoutes();

        $this->newLine();
        $this->displaySummary();

        return self::SUCCESS;
    }

    // ──────────────────────────────────────────────
    // Name resolution
    // ──────────────────────────────────────────────

    protected function resolveNames(): void
    {
        $this->modelName           = Str::studly($this->argument('name'));
        $this->modelNamePlural     = Str::plural($this->modelName);
        $this->modelVariable       = Str::camel($this->modelName);
        $this->modelVariablePlural = Str::camel($this->modelNamePlural);
        $this->routeName           = Str::snake($this->modelNamePlural, '-');
    }

    // ──────────────────────────────────────────────
    // Generators
    // ──────────────────────────────────────────────

    protected function generateModel(): void
    {
        $this->publishCrudStub(
            stub: 'Model',
            destination: "app/Models/{$this->modelName}.php",
            label: 'Model',
        );
    }

    protected function generateMigration(): void
    {
        $tableName     = Str::snake($this->modelNamePlural);
        $migrationName = "create_{$tableName}_table";

        if (!$this->option('force')) {
            $existing = glob($this->appPath("database/migrations/*_{$migrationName}.php"));
            if (!empty($existing)) {
                $this->components->warn("Migration for {$tableName} already exists (use --force to overwrite)");
                return;
            }
        }

        $timestamp = date('Y_m_d_His');

        $this->publishCrudStub(
            stub: 'Migration',
            destination: "database/migrations/{$timestamp}_{$migrationName}.php",
            label: 'Migration',
            extraTokens: ['TABLE_NAME' => $tableName],
        );
    }

    protected function generateService(): void
    {
        $this->publishCrudStub(
            stub: 'Service',
            destination: "app/Services/{$this->modelName}Service.php",
            label: 'Service',
        );
    }

    protected function generateController(): void
    {
        $this->publishCrudStub(
            stub: 'Controller',
            destination: "app/Http/Controllers/{$this->modelName}Controller.php",
            label: 'Controller',
        );
    }

    protected function generateRequest(string $action): void
    {
        $this->publishCrudStub(
            stub: 'Request',
            destination: "app/Http/Requests/{$action}{$this->modelName}Request.php",
            label: "{$action} Request",
            extraTokens: ['REQUEST_NAME' => "{$action}{$this->modelName}Request"],
        );
    }

    protected function generateResource(): void
    {
        $this->publishCrudStub(
            stub: 'Resource',
            destination: "app/Http/Resources/{$this->modelName}Resource.php",
            label: 'Resource',
        );
    }

    protected function generateRoutes(): void
    {
        $apiPrefix     = $this->option('api-prefix');
        $routeFileName = Str::snake($this->modelNamePlural);

        $published = $this->publishCrudStub(
            stub: 'Routes',
            destination: "routes/{$apiPrefix}/{$routeFileName}.php",
            label: 'Routes',
        );

        if ($published) {
            $this->addRouteRequire("{$routeFileName}.php");
        }
    }

    // ──────────────────────────────────────────────
    // Stub handling
    // ──────────────────────────────────────────────

    /**
     * Read a CRUD stub, replace tokens, and write to destination.
     * Returns true if published, false if skipped.
     */
    protected function publishCrudStub(string $stub, string $destination, string $label, array $extraTokens = []): bool
    {
        $destPath = $this->appPath($destination);

        if (File::exists($destPath) && !$this->option('force')) {
            $this->components->warn("{$label} already exists (use --force to overwrite)");
            return false;
        }

        $this->ensureDirectoryExists($destPath);

        $content = File::get($this->stubPath("Crud/{$stub}.stub"));
        $content = $this->replaceTokens($content, $extraTokens);

        File::put($destPath, $content);
        $this->publishedFiles[] = $destination;
        $this->components->info("✓ {$label} created");

        return true;
    }

    protected function replaceTokens(string $content, array $extraTokens = []): string
    {
        $tokens = array_merge([
            'MODEL_NAME'          => $this->modelName,
            'MODEL_NAME_PLURAL'   => $this->modelNamePlural,
            'MODEL_VARIABLE'      => $this->modelVariable,
            'MODEL_VARIABLE_PLURAL' => $this->modelVariablePlural,
            'ROUTE_NAME'          => $this->routeName,
            'SERVICE_NAME'        => "{$this->modelName}Service",
            'CONTROLLER_NAME'     => "{$this->modelName}Controller",
            'RESOURCE_NAME'       => "{$this->modelName}Resource",
            'STORE_REQUEST_NAME'  => "Store{$this->modelName}Request",
            'UPDATE_REQUEST_NAME' => "Update{$this->modelName}Request",
        ], $extraTokens);

        foreach ($tokens as $key => $value) {
            $content = str_replace("{{ {$key} }}", $value, $content);
        }

        return $content;
    }

    // ──────────────────────────────────────────────
    // Summary
    // ──────────────────────────────────────────────

    protected function displaySummary(): void
    {
        $this->components->info("CRUD generation completed!");
        $this->newLine();

        if (!empty($this->publishedFiles)) {
            $this->components->info("Generated files:");
            foreach ($this->publishedFiles as $file) {
                $this->line("  • {$file}");
            }
        }

        $this->newLine();
        $this->components->info("Next steps:");
        $this->line("  1. Define your model fillable fields and relationships");
        $this->line("  2. Add columns to the migration and run: php artisan migrate");
        $this->line("  3. Define validation rules in the request files");
        $this->line("  4. Customize the resource file to shape your API responses");
        $this->line("  5. Register the route file in your route service provider");
    }
}
