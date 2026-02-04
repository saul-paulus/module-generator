<?php


declare(strict_types=1);


namespace Ixspx\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallApiJwtCommand extends Command
{
    protected $signature = "install:api-jwt";
    protected $description = 'Install API JWT authentication using tymon/jwt-auth';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $this->info("Starting API JWT authentication setup...");

        $this->installJwtPackage();
        $this->publishJwtConfig();
        $this->generateJwtSecret();
        $this->publishRoutes();
        $this->showAuthConfigInstruction();

        $this->info("API JWT authentication setup completed successfully.");

        return self::SUCCESS;
    }

    /**
     * Install tymon/jwt-auth package
     */
    protected function installJwtPackage(): void
    {
        $this->info('Installing tymon/jwt-auth package...');

        shell_exec('composer require tymon/jwt-auth');

        $this->info('JWT package installed.');
    }

    /**
     * Publish jwt.php config from tymon/jwt-auth
     */
    protected function publishJwtConfig(): void
    {
        if ($this->files->exists(config_path('jwt.php'))) {
            $this->warn('jwt.php already exists, skipping publish.');
            return;
        }

        $this->call('vendor:publish', [
            '--provider' => 'Tymon\JWTAuth\Providers\LaravelServiceProvider',
            '--tag'      => 'jwt-config',
        ]);

        $this->info('Published jwt.php configuration.');
    }
    /**
     * Generate JWT secret key
     */
    protected function generateJwtSecret(): void
    {
        $this->call('jwt:secret');
        $this->info('JWT secret generated.');
    }

    /**
     * Publish API routes stub
     */
    protected function publishRoutes(): void
    {
        $target = base_path('routes/api.php');
        $stub   = __DIR__ . '/../../Stubs/api-jwt/api-jwt-routes.stub';

        if ($this->files->exists($target)) {
            $this->warn('routes/api.php already exists, skipping route publish.');
            return;
        }

        $this->files->copy($stub, $target);

        $this->info('routes/api.php created with JWT routes.');
    }

    /**
     * Show manual auth.php instruction
     */
    protected function showAuthConfigInstruction(): void
    {
        $this->warn('Please update config/auth.php:');
        $this->line("
            'guards' => [
                'api' => [
                    'driver' => 'jwt',
                    'provider' => 'users',
                ],
            ],
        ");
    }
}
