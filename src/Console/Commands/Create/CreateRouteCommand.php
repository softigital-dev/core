<?php

namespace SoftigitalDev\Core\Console\Commands\Create;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateRouteCommand extends Command
{
    protected $signature = 'make:route 
                            {name : The name of the route file}
                            {--controller= : Specify a controller to scaffold routes for}
                            {--resource : Create resourceful routes}
                            {--api : Create API resource routes}';

    protected $description = 'Create a new route file in routes/v1/ and register it in api.php';

    public function handle(): int
    {
        $name = Str::snake($this->argument('name'));
        $filePath = base_path("routes/v1/{$name}.php");
        $apiFile = base_path('routes/v1/api.php');

        // Prevent overwriting existing route file
        if (File::exists($filePath)) {
            $this->components->error("Route file {$name}.php already exists!");
            return self::FAILURE;
        }

        // Ensure the directory exists
        File::ensureDirectoryExists(base_path('routes/v1'));

        // Generate stub content
        $stub = $this->generateStub($name);

        File::put($filePath, $stub);
        $this->components->info("Route file created: routes/v1/{$name}.php");

        // Register in api.php
        $this->registerInApiFile($apiFile, $name);

        $this->newLine();
        $this->components->info("Route file created and registered successfully!");
        
        return self::SUCCESS;
    }

    /**
     * Generate the route file stub content.
     */
    protected function generateStub(string $name): string
    {
        $controller = $this->option('controller');
        $isResource = $this->option('resource');
        $isApi = $this->option('api');

        $stub = "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n";

        if ($controller) {
            $controllerClass = Str::studly($controller);
            $stub .= "use App\\Http\\Controllers\\{$controllerClass};\n";
        }

        $stub .= "\n// {$name} routes\n";

        if ($controller && ($isResource || $isApi)) {
            $controllerClass = Str::studly($controller);
            $resourceName = Str::kebab(Str::plural($name));
            
            if ($isApi) {
                $stub .= "Route::apiResource('{$resourceName}', {$controllerClass}::class);\n";
            } else {
                $stub .= "Route::resource('{$resourceName}', {$controllerClass}::class);\n";
            }
        } elseif ($controller) {
            $controllerClass = Str::studly($controller);
            $stub .= "// Route::get('/{$name}', [{$controllerClass}::class, 'index']);\n";
            $stub .= "// Route::post('/{$name}', [{$controllerClass}::class, 'store']);\n";
            $stub .= "// Route::get('/{$name}/{{id}}', [{$controllerClass}::class, 'show']);\n";
            $stub .= "// Route::put('/{$name}/{{id}}', [{$controllerClass}::class, 'update']);\n";
            $stub .= "// Route::delete('/{$name}/{{id}}', [{$controllerClass}::class, 'destroy']);\n";
        } else {
            $stub .= "// Route::get('/{$name}', function () {\n";
            $stub .= "//     return response()->json(['message' => 'Hello from {$name}']);\n";
            $stub .= "// });\n";
        }

        return $stub;
    }

    /**
     * Register the route file in routes/v1/api.php.
     */
    protected function registerInApiFile(string $apiFile, string $name): void
    {
        $requireLine = "require __DIR__.'/{$name}.php';";

        // Ensure routes/v1/api.php exists
        if (!File::exists($apiFile)) {
            $initial = "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n";
            File::put($apiFile, $initial);
        }

        $apiContent = File::get($apiFile);

        // Check if already registered
        if (str_contains($apiContent, $requireLine) || str_contains($apiContent, "require __DIR__ . '/{$name}.php'")) {
            $this->components->warn("Route already registered in routes/v1/api.php");
            return;
        }

        // Append the require at the end
        $apiContent = rtrim($apiContent) . "\n\n" . $requireLine . "\n";
        File::put($apiFile, $apiContent);

        $this->components->info("Route registered in routes/v1/api.php");
    }
}
