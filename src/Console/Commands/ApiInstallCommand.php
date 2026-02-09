<?php

declare(strict_types=1);


namespace Ixspx\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ApiInstallCommand extends Command
{
    protected $signature = 'make:api-install {--force}';
    protected $description = 'Install API starter (route, middleware, response helper)';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $this->installRoutes();
        $this->installMiddleware();
        $this->installResponseHelper();

        $this->info("API started installed successfully");

        return self::SUCCESS;
    }

    public function installRoutes()
    {
        $targetPath = base_path('routes/api.php');

        if ($this->files->exists($targetPath) && !$this->option('force')) {
            $this->warn('routes/api.php already exists, skipped.');
            return;
        }

        $this->files->copy(__DIR__ . '/../../routes/api.php', $targetPath);

        $this->line('✔ API routes installed');
    }

    public function installMiddleware(): void
    {
        $stubPath = __DIR__ . '/../../Stubs/';

        $files = [
            'api-middleware.stub' => app_path('Http/Middleware/ApiMiddleware.php'),
        ];

        foreach ($files as $stub => $destination) {
            if ($this->files->exists($destination)) {
                $this->warn("Skipped: {$destination} already exists.");
                continue;
            }

            $this->files->ensureDirectoryExists(dirname($destination));

            $content = $this->files->get($stubPath . $stub);
            $this->files->put($destination, $content);

            $this->line('✔ ApiMiddleware installed');
        }
    }

    public function installResponseHelper()
    {
        $stubPath = __DIR__ . '/../../Stubs/';

        $files = [
            'api-response.stub'  => app_path('Support/ApiResponse.php'),
        ];

        foreach ($files as $stub => $destination) {
            if ($this->files->exists($destination)) {
                $this->warn("Skipped: {$destination} already exists.");
                continue;
            }

            $this->files->ensureDirectoryExists(dirname($destination));

            $content = $this->files->get($stubPath . $stub);
            $this->files->put($destination, $content);

            $this->line('✔ ApiResponse installed');
        }
    }
}
