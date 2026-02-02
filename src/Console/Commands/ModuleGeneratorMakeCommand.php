<?php


namespace Ixspx\MonuleGenerator\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModuleGeneratorMakeCommand extends GeneratorCommand
{
    protected $signature = "make:mod {name: The name of the module generator}";
    protected $description = "Create a new module generator class";
    protected $type = "Module Generator";

    protected function getStub()
    {
        $readonly = Str::contains(
            haystack: PHP_VERSION,
            needles: '8.5.0',
        );

        $file = $readonly
            ? 'module-generator-readonly.stub'
            : 'module-generator.stub';


        return __DIR__ . '/stubs/' . $file;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Console\Commands';
    }
}
