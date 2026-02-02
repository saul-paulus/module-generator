<?php

declare(strict_types=1);

namespace Ixspx\MonuleGenerator\Providers;

use Illuminate\Support\ServiceProvider;
use Ixspx\ModuleGenerator\Console\Commands\ModuleGeneratorMakeCommand;

final class ModulegeneratorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ModuleGeneratorMakeCommand::class
            ]);
        }
    }
}
