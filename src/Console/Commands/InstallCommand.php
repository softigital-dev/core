<?php

namespace SoftigitalDev\Core\Console\Commands;

use Illuminate\Console\Command;
use SoftigitalDev\Core\Console\Commands\Concerns\ManagesComposer;
use SoftigitalDev\Core\Console\Commands\Concerns\ManagesFiles;
use SoftigitalDev\Core\Console\Commands\Concerns\ManagesMigrations;
use SoftigitalDev\Core\Console\Commands\Concerns\ManagesRoutes;

class InstallCommand extends Command
{
    use ManagesComposer;
    use ManagesFiles;
    use ManagesMigrations;
    use ManagesRoutes;

    protected $signature = 'softigital:install
                            {type? : Installation type (base, auth, google-auth)}
                            {--force : Overwrite existing files}
                            {--skip-migration : Skip publishing and running migrations}
                            {--skip-composer : Skip composer package installation}';

    protected $description = 'Install Softigital Core components (Base setup, Auth, Google Auth, and more)';

    // ──────────────────────────────────────────────
    // File mappings: stub => destination
    // ──────────────────────────────────────────────

    /** Files shared by all installation types. */
    protected array $sharedFiles = [
        'Utils/ApiResponse.stub' => 'app/Utils/ApiResponse.php',
    ];

    /** Files specific to the auth installation. */
    protected array $authFiles = [
        'Http/Controllers/AuthController.stub' => 'app/Http/Controllers/Auth/AuthController.php',
        'Http/Requests/LoginRequest.stub'      => 'app/Http/Requests/LoginRequest.php',
        'Http/Requests/RegisterRequest.stub'    => 'app/Http/Requests/RegisterRequest.php',
        'Services/AuthService.stub'            => 'app/Services/AuthService.php',
        'Routes/auth.stub'                     => 'routes/v1/auth.php',
    ];

    /** Files specific to the google-auth installation. */
    protected array $googleAuthFiles = [
        'Http/Controllers/GoogleAuthController.stub' => 'app/Http/Controllers/Auth/GoogleAuthController.php',
        'Http/Requests/GoogleLoginRequest.stub'      => 'app/Http/Requests/GoogleLoginRequest.php',
        'Configs/google.stub'                        => 'config/google.php',
        'Routes/google.stub'                         => 'routes/v1/google.php',
    ];

    // ──────────────────────────────────────────────

    public function handle(): int
    {
        // Show help if --help or -h is passed, or no type provided
        if ($this->option('help') || !$this->argument('type')) {
            $this->displayHelp();
            return self::SUCCESS;
        }

        return match ($this->argument('type')) {
            'base'        => $this->installBase(),
            'auth'        => $this->installAuth(),
            'google-auth' => $this->installGoogleAuth(),
            default       => $this->invalidType(),
        };
    }

    // ──────────────────────────────────────────────
    // Installers
    // ──────────────────────────────────────────────

    protected function installBase(): int
    {
        $this->components->info('Installing Base Setup...');
        $this->newLine();

        $this->ensureApiRouteInV1();
        $this->publishFileMap($this->sharedFiles);

        $this->newLine();
        $this->components->info('Base setup installed successfully!');
        $this->newLine();
        $this->components->info('What was configured:');
        $this->line('  • routes/v1/api.php structure created');
        $this->line('  • bootstrap/app.php updated for v1 routing');
        $this->line('  • ApiResponse utility installed');

        return self::SUCCESS;
    }

    protected function installAuth(): int
    {
        $this->components->info('Installing Auth...');
        $this->newLine();

        $this->ensureApiRouteInV1();
        $this->publishFileMap($this->sharedFiles);
        $this->publishFileMap($this->authFiles);
        $this->addRouteRequire('auth.php');

        $this->newLine();
        $this->components->info('Auth installed successfully!');

        return self::SUCCESS;
    }

    protected function installGoogleAuth(): int
    {
        $this->components->info('Installing Google Auth...');
        $this->newLine();

        // 1. Composer dependency
        if (!$this->option('skip-composer') && !$this->isPackageInstalled('google/apiclient')) {
            if (!$this->confirm("Google Auth requires 'google/apiclient'. Install it now?", true)) {
                $this->components->error('Installation aborted — google/apiclient is required.');
                return self::FAILURE;
            }

            if (!$this->requirePackage('google/apiclient')) {
                return self::FAILURE;
            }
        }

        // 2. Route structure
        $this->ensureApiRouteInV1();

        // 3. Shared + google-auth files
        $this->publishFileMap($this->sharedFiles);
        $this->publishFileMap($this->googleAuthFiles);

        // 4. Migration
        if (!$this->option('skip-migration')) {
            $published = $this->publishMigration(
                'Migrations/2025_09_14_115712_add_google_id_to_users_table.stub',
                'add_google_id_to_users_table',
            );

            if ($published) {
                $this->runMigrations();
            }
        }

        // 5. Wire route
        $this->addRouteRequire('google.php');

        // 6. Done
        $this->newLine();
        $this->components->info('Google Auth installed successfully!');
        $this->newLine();
        $this->components->warn('Next steps — add to your .env file:');
        $this->line('  GOOGLE_CLIENT_ID=your-client-id');
        $this->line('  GOOGLE_CLIENT_SECRET=your-client-secret');
        $this->line('  GOOGLE_REDIRECT_URI=your-redirect-uri');

        return self::SUCCESS;
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Publish an array of stub => destination mappings.
     */
    protected function publishFileMap(array $files): void
    {
        foreach ($files as $stub => $destination) {
            $this->publishStub($stub, $destination);
        }
    }

    protected function displayHelp(): void
    {
        $this->components->info('Softigital Core Installation Command');
        $this->newLine();

        $this->line('<fg=cyan>Usage:</>');
        $this->line('  php artisan softigital:install <type> [options]');
        $this->newLine();

        $this->line('<fg=cyan>Available Installation Types:</>');
        $this->line('  <fg=green>base</>          Install base API setup');
        $this->line('                 • Creates routes/v1/api.php structure');
        $this->line('                 • Updates bootstrap/app.php for v1 routing');
        $this->line('                 • Installs ApiResponse utility');
        $this->newLine();
        $this->line('  <fg=green>auth</>          Install authentication system');
        $this->line('                 • AuthController (login, register, me endpoints)');
        $this->line('                 • AuthService (handles authentication logic)');
        $this->line('                 • Request validation classes');
        $this->line('                 • API routes with Sanctum token support');
        $this->line('                 • Includes ApiResponse utility');
        $this->newLine();
        $this->line('  <fg=green>google-auth</>   Install Google OAuth authentication');
        $this->line('                 • GoogleAuthController (Google ID token verification)');
        $this->line('                 • Google configuration file');
        $this->line('                 • Google routes');
        $this->line('                 • Migration for google_id column');
        $this->line('                 • Requires: google/apiclient package');
        $this->newLine();

        $this->line('<fg=cyan>Options:</>');
        $this->line('  <fg=yellow>--force</>           Overwrite existing files without prompting');
        $this->line('  <fg=yellow>--skip-migration</>  Skip publishing and running migrations (google-auth)');
        $this->line('  <fg=yellow>--skip-composer</>   Skip automatic composer package installation');
        $this->line('  <fg=yellow>--help, -h</>        Display this help message');
        $this->newLine();

        $this->line('<fg=cyan>Examples:</>');
        $this->line('  <fg=gray># Install base API setup</>');
        $this->line('  php artisan softigital:install base');
        $this->newLine();
        $this->line('  <fg=gray># Install basic authentication</>');
        $this->line('  php artisan softigital:install auth');
        $this->newLine();
        $this->line('  <fg=gray># Install Google OAuth authentication</>');
        $this->line('  php artisan softigital:install google-auth');
        $this->newLine();
        $this->line('  <fg=gray># Force overwrite existing files</>');
        $this->line('  php artisan softigital:install auth --force');
        $this->newLine();
        $this->line('  <fg=gray># Install without running migrations</>');
        $this->line('  php artisan softigital:install google-auth --skip-migration');
        $this->newLine();

        $this->line('<fg=cyan>What Gets Installed:</>');
        $this->line('  • Creates routes/v1/api.php structure');
        $this->line('  • Updates bootstrap/app.php with v1 routing');
        $this->line('  • Publishes controllers, services, and request classes');
        $this->line('  • Configures route files with proper middleware');
        $this->line('  • Installs required composer packages (when needed)');
        $this->newLine();

        $this->line('<fg=cyan>Requirements:</>');
        $this->line('  • Laravel 11 or higher');
        $this->line('  • Laravel Sanctum (for token-based auth)');
        $this->newLine();

        $this->line('<fg=gray>For more information, visit:</>');
        $this->line('<fg=gray>https://github.com/softigital-dev/core</>');
    }

    protected function invalidType(): int
    {
        $this->components->error(
            "Invalid type: '{$this->argument('type')}'. Available types: base, auth, google-auth",
        );
        $this->newLine();
        $this->line('Run <fg=yellow>php artisan softigital:install --help</> for more information.');

        return self::FAILURE;
    }
}
