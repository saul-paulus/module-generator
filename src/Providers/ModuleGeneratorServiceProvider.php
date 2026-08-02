<?php

declare(strict_types=1);

namespace Ixspx\ModuleGenerator\Providers;

use Illuminate\Support\ServiceProvider;
use Ixspx\ModuleGenerator\Console\Commands\ApiInstallCommand;
use Ixspx\ModuleGenerator\Console\Commands\ApiResponseMakeCommand;
use Ixspx\ModuleGenerator\Console\Commands\ModuleGeneratorMakeCommand;
use Ixspx\ModuleGenerator\Factories\ApiSpecificationFactory;

final class ModuleGeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/module-generator.php', 'module-generator');

        // Singleton binding for ApiSpecificationFactory
        $this->app->singleton(ApiSpecificationFactory::class, function ($app) {
            return new ApiSpecificationFactory($app);
        });

        // Convention-Based Interface-to-Repository Auto-Binding Fallback
        $this->app->beforeResolving(function ($abstract, $parameters, $app) {
            if (is_string($abstract) && str_starts_with($abstract, 'App\\Repositories\\Interfaces\\')) {
                $concrete = str_replace(
                    ['App\\Repositories\\Interfaces\\', 'Interface'],
                    ['App\\Repositories\\Repository\\', 'Repository'],
                    $abstract
                );

                if (interface_exists($abstract) && class_exists($concrete) && !$app->bound($abstract)) {
                    $app->bind($abstract, $concrete);
                }
            }
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Stubs' => base_path('stubs/module-generator'),
            ], 'module-generator-stubs');

            $this->publishes([
                __DIR__ . '/../../config/module-generator.php' => config_path('module-generator.php'),
            ], 'module-generator-config');

            $this->commands([
                ModuleGeneratorMakeCommand::class,
                ApiResponseMakeCommand::class,
                ApiInstallCommand::class,
            ]);
        }
    }
}
