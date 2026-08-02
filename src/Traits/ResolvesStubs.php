<?php

declare(strict_types=1);

namespace Ixspx\ModuleGenerator\Traits;

trait ResolvesStubs
{
    /**
     * Get stub file path, checking published stubs in base_path first.
     */
    protected function getStubPath(string $stubName): string
    {
        if (function_exists('base_path')) {
            $publishedStub = base_path("stubs/module-generator/{$stubName}");

            if (file_exists($publishedStub)) {
                return $publishedStub;
            }
        }

        return __DIR__ . "/../Stubs/{$stubName}";
    }
}
