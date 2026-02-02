<?php


namespace Ixspx\MonuleGenerator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class ModuleGeneratorMakeCommand extends Command
{
    protected $signature = "make:mod {name: The name of the module generator}";
    protected $description = "Create a new module generator class";
    protected $type = "Module Generator";
    protected $files;

    public function __construct(FileSystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $path = app_path("Modules/{$name}");

        if ($this->files->exists($path)) {
            $this->error("Module {$name} already exists!");

            return self::FAILURE;
        }

        $this->makeDirectory($path);
        $this->generateFiles($name, $path);
        $this->info("{$name} created successfully.");

        return self::SUCCESS;
    }

    protected function makeDirectory(string $path): void
    {
        // Buat folder utama
        $dirs = [
            'Models',
            'Controllers',
            'Repositories',
            'Services',
            'Providers',
        ];

        foreach ($dirs as $dir) {
            $this->files->makeDirectory("{$path}/{$dir}", 0755, true);
        }
    }

    public function generateFiles(string $name, string $path): void
    {
        $stubPath = __DIR__ . '/../../Stubs/';

        // Buat file dasar
        $files = [
            'model.stub'                  => "Models/{$name}/{$name}Model.php",
            'repository.interface.stub'   => "Repositories/{$name}/{$name}RepositoryInterface.php",
            'repository.stub'             => "Repositories/{$name}/{$name}Repository.php",
            'service.stub'                => "Services/{$name}/{$name}Service.php",
            'Controllers.stub'            => "Controllers/{$name}/{$name}Controller.php",
            'Providers.stub'              => "{$name}ServiceProvider.php",
        ];

        foreach ($files as $stubFile => $targetFile) {
            $stub = $this->files->get($stubPath . $stubFile);
            $content = str_replace('{{name}}', $name, $stub);
            // Jika stub adalah model, ganti juga {{table_name}}
            if ($stubFile === 'model.stub') {
                $tableName = Str::snake(Str::pluralStudly($name));
                $content = str_replace('{{table_name}}', $tableName, $content);
            }
            $this->files->put("{$path}/{$targetFile}", $content);
        }
    }
}
