<?php

declare(strict_types=1);

namespace Ixspx\ModuleGenerator\Support;

use Illuminate\Filesystem\Filesystem;

class ProviderRegistrar
{
    public function __construct(protected Filesystem $files) {}

    /**
     * Register a Service Provider class in bootstrap/providers.php or config/app.php
     */
    public function register(string $providerClass): bool
    {
        $bootstrapProviders = base_path('bootstrap/providers.php');

        if ($this->files->exists($bootstrapProviders)) {
            $content = $this->files->get($bootstrapProviders);

            if (str_contains($content, $providerClass)) {
                return false; // Already registered
            }

            $pattern = '/(return\s*\[)/';
            $replacement = "$1\n    {$providerClass}::class,";
            $updated = preg_replace($pattern, $replacement, $content, 1);

            if ($updated !== null && $updated !== $content) {
                $this->files->put($bootstrapProviders, $updated);
                return true;
            }
        }

        // Fallback for Laravel 10 config/app.php
        $configApp = config_path('app.php');
        if ($this->files->exists($configApp)) {
            $content = $this->files->get($configApp);

            if (str_contains($content, $providerClass)) {
                return false;
            }

            if (str_contains($content, "'providers' => ServiceProvider::defaultProviders()->merge([")) {
                $pattern = "/('providers' => ServiceProvider::defaultProviders\(\)->merge\(\[)/";
                $replacement = "$1\n        {$providerClass}::class,";
                $updated = preg_replace($pattern, $replacement, $content, 1);
                if ($updated !== null) {
                    $this->files->put($configApp, $updated);
                    return true;
                }
            }
        }

        return false;
    }
}
