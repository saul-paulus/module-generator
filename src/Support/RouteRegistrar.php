<?php

declare(strict_types=1);

namespace Ixspx\ModuleGenerator\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class RouteRegistrar
{
    public function __construct(protected Filesystem $files) {}

    /**
     * Register API routes for a generated module in routes/api.php
     */
    public function registerApiResource(string $moduleName, string $style = 'restful'): bool
    {
        $routeFile = base_path('routes/api.php');

        if (!$this->files->exists($routeFile)) {
            return false;
        }

        $controllerClass = "App\\Http\\Controllers\\{$moduleName}\\{$moduleName}Controller";
        $uri = Str::kebab(Str::pluralStudly($moduleName));

        if ($style === 'handler') {
            $routeBlock = "\nRoute::controller(\\{$controllerClass}::class)->group(function () {\n"
                . "    Route::get('/{$uri}', 'HandlerGetAll');\n"
                . "    Route::get('/{$uri}/{id}', 'HandlerGetById');\n"
                . "    Route::delete('/{$uri}/{id}', 'HandlerDeleteById');\n"
                . "});\n";
        } else {
            $routeBlock = "\nRoute::apiResource('{$uri}', \\{$controllerClass}::class);";
        }

        $content = $this->files->get($routeFile);

        if (str_contains($content, $controllerClass)) {
            return false; // Already registered
        }

        $this->files->append($routeFile, "\n{$routeBlock}\n");
        return true;
    }
}
