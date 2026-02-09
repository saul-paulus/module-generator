<?php

namespace Ixspx\ModuleGenerator\Tests;

use Ixspx\ModuleGenerator\Providers\ModuleGeneratorServiceProvider as ProvidersModuleGeneratorServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;


abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ProvidersModuleGeneratorServiceProvider::class,
        ];
    }
}
