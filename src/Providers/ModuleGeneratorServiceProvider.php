<?php

declare(strict_types=1);

namespace Ixspx\ModuleGenerator\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Ixspx\ModuleGenerator\Console\Commands\ApiInstallCommand;
use Ixspx\ModuleGenerator\Console\Commands\ApiResponseMakeCommand;
use Ixspx\ModuleGenerator\Console\Commands\ModuleGeneratorMakeCommand;

final class ModuleGeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/module-generator.php',
            'module-generator'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../config/module-generator.php'
                => config_path('module-generator.php'),
            ], 'module-generator-config');

            $this->commands([
                ModuleGeneratorMakeCommand::class,
                ApiResponseMakeCommand::class,
                ApiInstallCommand::class,
            ]);
        }
    }
}
