<?php

namespace SoftigitalDev\Core\Console\Commands\Create;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service 
                            {name : The name of the service class}
                            {--model= : Generate a service with model dependency injection}
                            {--repository : Create a repository pattern service}';

    protected $description = 'Create a new service class in app/Services';

    public function handle(): int
    {
        $name = $this->argument('name');
        
        // Replace dots and backslashes with forward slashes for consistency
        $name = str_replace(['\\', '.'], '/', $name);
        
        // Split into path and class name
        $parts = explode('/', $name);
        $className = Str::studly(array_pop($parts));
        $subDirectory = implode('/', array_map(fn($part) => Str::studly($part), $parts));
        
        // Ensure name ends with "Service"
        if (!Str::endsWith($className, 'Service')) {
            $className .= 'Service';
        }

        // Build full path
        $directory = $subDirectory ? "Services/{$subDirectory}" : 'Services';
        $path = app_path("{$directory}/{$className}.php");

        if (File::exists($path)) {
            $this->components->error("Service {$className} already exists!");
            return self::FAILURE;
        }

        // Ensure Services directory exists (with subdirectories)
        File::ensureDirectoryExists(app_path($directory));

        // Generate stub content
        $stub = $this->generateStub($className, $subDirectory);

        File::put($path, $stub);

        $this->components->info("Service {$className} created successfully!");
        $this->line("Location: app/{$directory}/{$className}.php");

        // Show usage example
        $namespace = $subDirectory ? "\\{$subDirectory}" : '';
        $this->newLine();
        $this->components->info("Usage example:");
        $this->line("  use App\\Services{$namespace}\\{$className};");
        $this->line("");
        $this->line("  public function __construct(");
        $this->line("      protected {$className} \$service");
        $this->line("  ) {}");

        return self::SUCCESS;
    }

    /**
     * Generate the service class stub content.
     */
    protected function generateStub(string $name, string $subDirectory = ''): string
    {
        $model = $this->option('model');
        $isRepository = $this->option('repository');

        $namespace = 'App\\Services';
        if ($subDirectory) {
            $namespace .= '\\' . str_replace('/', '\\', $subDirectory);
        }

        $stub = "<?php\n\nnamespace {$namespace};\n\n";

        if ($model) {
            $modelClass = Str::studly($model);
            $stub .= "use App\\Models\\{$modelClass};\n";
        }

        $stub .= "\nclass {$name}\n{\n";

        if ($model) {
            $modelClass = Str::studly($model);
            $modelVariable = Str::camel($modelClass);
            
            if ($isRepository) {
                $stub .= "    /**\n";
                $stub .= "     * Get all {$modelVariable}s.\n";
                $stub .= "     */\n";
                $stub .= "    public function getAll()\n";
                $stub .= "    {\n";
                $stub .= "        return {$modelClass}::all();\n";
                $stub .= "    }\n\n";

                $stub .= "    /**\n";
                $stub .= "     * Find a {$modelVariable} by ID.\n";
                $stub .= "     */\n";
                $stub .= "    public function findById(\$id)\n";
                $stub .= "    {\n";
                $stub .= "        return {$modelClass}::findOrFail(\$id);\n";
                $stub .= "    }\n\n";

                $stub .= "    /**\n";
                $stub .= "     * Create a new {$modelVariable}.\n";
                $stub .= "     */\n";
                $stub .= "    public function create(array \$data)\n";
                $stub .= "    {\n";
                $stub .= "        return {$modelClass}::create(\$data);\n";
                $stub .= "    }\n\n";

                $stub .= "    /**\n";
                $stub .= "     * Update a {$modelVariable}.\n";
                $stub .= "     */\n";
                $stub .= "    public function update(\$id, array \$data)\n";
                $stub .= "    {\n";
                $stub .= "        \${$modelVariable} = \$this->findById(\$id);\n";
                $stub .= "        \${$modelVariable}->update(\$data);\n";
                $stub .= "        return \${$modelVariable};\n";
                $stub .= "    }\n\n";

                $stub .= "    /**\n";
                $stub .= "     * Delete a {$modelVariable}.\n";
                $stub .= "     */\n";
                $stub .= "    public function delete(\$id)\n";
                $stub .= "    {\n";
                $stub .= "        \${$modelVariable} = \$this->findById(\$id);\n";
                $stub .= "        return \${$modelVariable}->delete();\n";
                $stub .= "    }\n";
            } else {
                $stub .= "    /**\n";
                $stub .= "     * Handle {$modelVariable} business logic.\n";
                $stub .= "     */\n";
                $stub .= "    public function handle()\n";
                $stub .= "    {\n";
                $stub .= "        // Your business logic here\n";
                $stub .= "    }\n";
            }
        } else {
            $stub .= "    /**\n";
            $stub .= "     * Handle service business logic.\n";
            $stub .= "     */\n";
            $stub .= "    public function handle()\n";
            $stub .= "    {\n";
            $stub .= "        // Your business logic here\n";
            $stub .= "    }\n";
        }

        $stub .= "}\n";

        return $stub;
    }
}
