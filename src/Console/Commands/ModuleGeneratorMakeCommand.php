<?php

declare(strict_types=1);

namespace Ixspx\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Ixspx\ModuleGenerator\Support\ProviderRegistrar;
use Ixspx\ModuleGenerator\Support\RouteRegistrar;
use Ixspx\ModuleGenerator\Traits\ResolvesStubs;

class ModuleGeneratorMakeCommand extends Command
{
    use ResolvesStubs;

    protected $signature = 'make:mod {name : The name of the module}
                            {--table= : Custom database table name}
                            {--table-prefix= : Custom database table prefix}
                            {--style= : Controller action naming style (restful, handler)}
                            {--no-provider : Do not generate or register a Service Provider}
                            {--no-route : Do not append routes to routes/api.php}
                            {--force : Overwrite existing module files}';

    protected $description = 'Generate module structure (model, repository, service, controller, provider)';

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        // Normalize each path segment to StudlyCase, preserving the slash separator.
        // e.g. "denda/esyCash" → "Denda/Esycash", "User" → "User"
        $modulePath = implode('/', array_map(
            fn (string $segment) => Str::studly($segment),
            explode('/', str_replace('\\', '/', $this->argument('name')))
        ));

        // Leaf segment is the class/file name (e.g. "Esycash" or "User").
        $segments  = explode('/', $modulePath);
        $className = end($segments);

        // PHP namespace uses backslashes (e.g. "Denda\Esycash").
        $phpNamespace = implode('\\', $segments);

        $targetModelFile = app_path("Models/{$modulePath}/{$className}Model.php");

        if ($this->files->exists($targetModelFile) && !$this->option('force')) {
            $this->error("Module {$modulePath} already exists.");
            return self::FAILURE;
        }

        $tableName = $this->option('table')
            ?: Str::snake(Str::pluralStudly($className));

        $tablePrefix = $this->option('table-prefix')
            ?? config('module-generator.table_prefix', '');

        $style = $this->option('style')
            ?? config('module-generator.controller_style', 'restful');

        $replacements = [
            '{{name}}'          => $className,
            '{{namespace}}'     => $phpNamespace,
            '{{nameVariable}}'  => lcfirst($className),
            '{{table_name}}'    => $tableName,
            '{{table_prefix}}'  => $tablePrefix,
        ];

        $this->makeDirectories();
        $this->generateFiles($modulePath, $className, $replacements, $style);

        // Auto-Register Provider unless --no-provider flag is set
        if (!$this->option('no-provider') && config('module-generator.auto_register_provider', true)) {
            $providerClass = "App\\Providers\\{$className}ServiceProvider";
            $registrar = new ProviderRegistrar($this->files);
            if ($registrar->register($providerClass)) {
                $this->info("✔ Registered provider: {$providerClass}");
            }
        }

        // Auto-Register Routes unless --no-route flag is set
        if (!$this->option('no-route') && config('module-generator.auto_register_route', true)) {
            $routeRegistrar = new RouteRegistrar($this->files);
            if ($routeRegistrar->registerApiResource($phpNamespace, $className, $style)) {
                $this->info("✔ Appended routes for {$className} to routes/api.php");
            }
        }

        $this->info("Module {$modulePath} created successfully.");
        return self::SUCCESS;
    }

    protected function makeDirectories(): void
    {
        $directories = [
            app_path('Models'),
            app_path('Http/Controllers'),
            app_path('Repositories/Interfaces'),
            app_path('Repositories/Repository'),
            app_path('Services'),
            app_path('Providers'),
        ];

        foreach ($directories as $dir) {
            $this->files->ensureDirectoryExists($dir, 0755, true);
        }
    }

    protected function generateFiles(string $modulePath, string $className, array $replacements, string $style): void
    {
        $controllerStub = ($style === 'handler') ? 'controller.handler.stub' : 'controller.stub';

        $files = [
            'model.stub'                => app_path("Models/{$modulePath}/{$className}Model.php"),
            'repository.interface.stub' => app_path("Repositories/Interfaces/{$modulePath}/{$className}Interface.php"),
            'repository.stub'           => app_path("Repositories/Repository/{$modulePath}/{$className}Repository.php"),
            'service.stub'              => app_path("Services/{$modulePath}/{$className}Service.php"),
            $controllerStub             => app_path("Http/Controllers/{$modulePath}/{$className}Controller.php"),
        ];

        if (!$this->option('no-provider')) {
            $files['provider.stub'] = app_path("Providers/{$className}ServiceProvider.php");
        }

        foreach ($files as $stub => $target) {
            $this->files->ensureDirectoryExists(dirname($target));

            $stubPath = $this->getStubPath($stub);
            if (!$this->files->exists($stubPath)) {
                $this->error("Stub not found: {$stubPath}");
                continue;
            }

            $content = $this->files->get($stubPath);

            $content = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $content
            );

            $this->files->put($target, $content);
            $this->line("✔ Installed: {$target}");
        }
    }
}
