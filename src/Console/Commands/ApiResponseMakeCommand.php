<?php

declare(strict_types=1);


namespace Ixspx\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ApiResponseMakeCommand extends Command
{
    protected $signature = 'make:api-response';
    protected $description = 'Generate API Response helper classes';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $stubPath = __DIR__ . '/../../Stubs/';

        $files = [
            'api-response.stub'  => app_path('Support/ApiResponse.php'),
            'api-responder.stub' => app_path('Support/ApiResponder.php'),
        ];

        foreach ($files as $stub => $destination) {
            if ($this->files->exists($destination)) {
                $this->warn("Skipped: {$destination} already exists.");
                continue;
            }

            $this->files->ensureDirectoryExists(dirname($destination));

            $content = $this->files->get($stubPath . $stub);
            $this->files->put($destination, $content);

            $this->info("Created: {$destination}");
        }

        return self::SUCCESS;
    }
}
