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
        $this->installFiles([
            'api-route.stub'              => base_path('routes/api.php'),
            'api-middleware.stub'         => app_path('Http/Middleware/ForceJsonResponse.php'),
            'api-response.stub'           => app_path('Support/ApiResponse.php'),
            'api-exception-registrar.stub' => app_path('Exceptions/ApiExceptionRegistrar.php'),
        ]);

        $this->info('API starter installed successfully.');

        return self::SUCCESS;
    }

    protected  function installFiles(array $map): void
    {
        foreach ($map as $stub => $destination) {
            if ($this->files->exists($destination) && !$this->option('force')) {
                $this->warn("Skipped: {$destination} already exists.");
                continue;
            }

            $stubFile = $this->stubPath($stub);

            if (!$this->files->exists($stubFile)) {
                $this->error("Stub not found: {$stub}");
                continue;
            }

            $this->files->ensureDirectoryExists(dirname($destination));
            $this->files->put($destination, $this->files->get($stubFile));

            $this->line("✔ Installed: {$destination}");
        }
    }

    protected function stubPath(string $file): string
    {
        return __DIR__ . "/../../Stubs/{$file}";
    }
}
