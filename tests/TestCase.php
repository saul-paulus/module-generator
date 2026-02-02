<?php

namespace Ixspx\ModuleGenerator\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Ixspx\MonuleGenerator\Providers\ModulegeneratorServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ModulegeneratorServiceProvider::class,
        ];
    }
}
