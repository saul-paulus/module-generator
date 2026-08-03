<?php

declare(strict_types=1);

namespace Ixspx\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Ixspx\ModuleGenerator\Traits\ResolvesStubs;

class ApiInstallCommand extends Command
{
    use ResolvesStubs;

    protected $signature = 'make:api-install {--force : Overwrite existing API starter files}';
    protected $description = 'Install API starter (route, middleware, response helper)';

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
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

    protected function installFiles(array $map): void
    {
        foreach ($map as $stub => $destination) {
            if ($this->files->exists($destination) && !$this->option('force')) {
                $this->warn("Skipped: {$destination} already exists.");
                continue;
            }

            $stubFile = $this->getStubPath($stub);

            if (!$this->files->exists($stubFile)) {
                $this->error("Stub not found: {$stub}");
                continue;
            }

            $this->files->ensureDirectoryExists(dirname($destination));
            $this->files->put($destination, $this->files->get($stubFile));

            $this->line("✔ Installed: {$destination}");
        }
    }
}
