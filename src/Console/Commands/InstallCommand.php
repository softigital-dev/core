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
                            {type : Installation type (auth, google-auth)}
                            {--force : Overwrite existing files}
                            {--skip-migration : Skip publishing and running migrations}
                            {--skip-composer : Skip composer package installation}';

    protected $description = 'Install Softigital Core components';

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
        return match ($this->argument('type')) {
            'auth'        => $this->installAuth(),
            'google-auth' => $this->installGoogleAuth(),
            default       => $this->invalidType(),
        };
    }

    // ──────────────────────────────────────────────
    // Installers
    // ──────────────────────────────────────────────

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

    protected function invalidType(): int
    {
        $this->components->error(
            "Invalid type: '{$this->argument('type')}'. Available types: auth, google-auth",
        );

        return self::FAILURE;
    }
}
