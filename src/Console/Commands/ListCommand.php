<?php

namespace SoftigitalDev\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ListCommand extends Command
{
    protected $signature = 'softigital:list
                            {type? : Check a specific type (base, auth, google-auth)}';

    protected $description = 'Show what Softigital Core components are installed in this application';

    protected array $types = [
        'base' => [
            'label'   => 'Base Setup',
            'command' => 'softigital:install base',
            'files'   => [
                'routes/v1/api.php',
                'app/Utils/ApiResponse.php',
                'app/Http/Middleware/ForceJsonResponse.php',
                'app/Http/Middleware/OptionalSanctumAuth.php',
                'app/Providers/SoftigitalServiceProvider.php',
            ],
        ],
        'auth' => [
            'label'   => 'Auth',
            'command' => 'softigital:install auth',
            'files'   => [
                'app/Http/Controllers/Auth/AuthController.php',
                'app/Http/Requests/LoginRequest.php',
                'app/Http/Requests/RegisterRequest.php',
                'app/Services/AuthService.php',
                'routes/v1/auth.php',
            ],
        ],
        'google-auth' => [
            'label'   => 'Google Auth',
            'command' => 'softigital:install google-auth',
            'files'   => [
                'app/Http/Controllers/Auth/GoogleAuthController.php',
                'app/Http/Requests/GoogleLoginRequest.php',
                'config/google.php',
                'routes/v1/google.php',
            ],
        ],
    ];

    // ──────────────────────────────────────────────

    public function handle(): int
    {
        if ($this->option('help')) {
            $this->displayHelp();
            return self::SUCCESS;
        }

        $type = $this->argument('type');

        if ($type !== null && !array_key_exists($type, $this->types)) {
            $this->components->error("Unknown type '{$type}'. Available: base, auth, google-auth");
            return self::FAILURE;
        }

        $this->newLine();
        $this->line('<fg=cyan;options=bold>Softigital Core — Installation Status</>');
        $this->line('<fg=gray>' . str_repeat('─', 42) . '</>');
        $this->newLine();

        $toCheck = $type ? [$type => $this->types[$type]] : $this->types;

        foreach ($toCheck as $key => $config) {
            $this->printInstallType($config['label'], $config['files'], $config['command']);
        }

        return self::SUCCESS;
    }

    // ──────────────────────────────────────────────
    // Rendering
    // ──────────────────────────────────────────────

    protected function printInstallType(string $label, array $files, string $command): void
    {
        $present = array_filter($files, fn($f) => File::exists(base_path($f)));
        $total   = count($files);
        $found   = count($present);

        if ($found === $total) {
            $this->line("<fg=green>  ✓  {$label}</>");
        } elseif ($found > 0) {
            $this->line("<fg=yellow>  ~  {$label}</> <fg=gray>(partial — {$found}/{$total} files)</>");
        } else {
            $this->line("<fg=red>  ✗  {$label}</> <fg=gray>— not installed</>");
            $this->line("<fg=gray>       Run: php artisan {$command}</>");
        }

        if ($found > 0) {
            foreach ($files as $file) {
                $exists = File::exists(base_path($file));
                $icon   = $exists ? '<fg=green>✓</>' : '<fg=red>✗</>';
                $color  = $exists ? 'white' : 'gray';
                $this->line("       {$icon} <fg={$color}>{$file}</>");
            }
        }

        $this->newLine();
    }

    protected function displayHelp(): void
    {
        $this->newLine();
        $this->line('<fg=cyan;options=bold>softigital:list</>');
        $this->line('<fg=gray>Check which Softigital Core components are installed.</>');
        $this->newLine();

        $this->line('<fg=cyan>Usage:</>');
        $this->line('  php artisan softigital:list [type]');
        $this->newLine();

        $this->line('<fg=cyan>Available Types:</>');
        $this->line('  <fg=green>base</>         Base API setup (middleware, provider, ApiResponse, routes/v1)');
        $this->line('  <fg=green>auth</>         Authentication (AuthController, AuthService, requests, routes)');
        $this->line('  <fg=green>google-auth</>  Google OAuth (GoogleAuthController, config, migration, routes)');
        $this->newLine();

        $this->line('<fg=cyan>Examples:</>');
        $this->line('  php artisan softigital:list                  <fg=gray># check all</>');
        $this->line('  php artisan softigital:list auth             <fg=gray># check auth only</>');
        $this->line('  php artisan softigital:list google-auth      <fg=gray># check google-auth only</>');
        $this->newLine();
    }
}
