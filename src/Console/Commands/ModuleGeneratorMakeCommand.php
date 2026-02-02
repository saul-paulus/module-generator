<?php


namespace Ixspx\MonuleGenerator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class ModuleGeneratorMakeCommand extends Command
{
    protected $signature = "make:mod {name: The name of the module generator}";
    protected $description = 'Generate module structure (model, repository, service, controller, provider)';
    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }


    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));

        if ($this->files->exists(app_path("Models/{$name}.php"))) {
            $this->error("Module {$name} already exists.");
            return self::FAILURE;
        }

        $this->makeDirectories();
        $this->generateFiles($name);

        $this->info("Module {$name} created successfully.");
        return self::SUCCESS;
    }

    protected function makeDirectories(): void
    {
        $directories = [
            app_path('Models'),
            app_path('Http/Controllers'),
            app_path('Repositories/Interfaces'),
            app_path('Repositories/Repository'),
            app_path("Services"),
            app_path('Providers'),
        ];

        foreach ($directories as $dir) {
            $this->files->ensureDirectoryExists($dir, 0755, true);
        }
    }

    protected function generateFiles(string $name): void
    {
        $stubPath = __DIR__ . '/../../Stubs/';

        $files = [
            'model.stub'                => app_path("Models/{$name}.php"),
            'repository.interface.stub' => app_path("Repositories/Interfaces/{$name}/{$name}RepositoryInterface.php"),
            'repository.stub'           => app_path("Repositories/Repository/{$name}/{$name}Repository.php"),
            'service.stub'              => app_path("Services/{$name}/{$name}Service.php"),
            'controller.stub'           => app_path("Http/Controllers/{$name}/{$name}Controller.php"),
            'provider.stub'             => app_path("Providers/{$name}ServiceProvider.php"),
        ];

        foreach ($files as $stub => $target) {
            $this->files->ensureDirectoryExists(dirname($target));

            $content = $this->files->get($stubPath . $stub);
            $content = str_replace('{{name}}', $name, $content);

            if ($stub === 'model.stub') {
                $content = str_replace(
                    '{{table_name}}',
                    Str::snake(Str::pluralStudly($name)),
                    $content
                );
            }

            $this->files->put($target, $content);
        }
    }
}
