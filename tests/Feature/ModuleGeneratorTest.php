<?php

namespace Ixspx\ModuleGenerator\Tests\Feature;

use Illuminate\Support\Facades\File;
use Ixspx\ModuleGenerator\Tests\TestCase;

class ModuleGeneratorTest extends TestCase
{
    protected string $module = 'TestUser';

    protected function setUp(): void
    {
        parent::setUp();

        File::delete(app_path("Models/{$this->module}.php"));
        File::delete(app_path("Http/Controllers/{$this->module}/{$this->module}Controller.php"));
        File::delete(app_path("Providers/{$this->module}ServiceProvider.php"));

        File::delete(app_path("Repositories/Interfaces/{$this->module}/{$this->module}RepositoryInterface.php"));
        File::delete(app_path("Repositories/Repository/{$this->module}/{$this->module}Repository.php"));
        File::deleteDirectory(app_path("Services/{$this->module}/{$this->module}Service.php"));
    }

    public function test_it_generates_full_module_structure(): void
    {
        $this->artisan('make:mod', ['name' => $this->module])
            ->assertExitCode(0);

        $this->assertFileExists(app_path("Models/{$this->module}.php"));
        $this->assertFileExists(app_path("Http/Controllers/{$this->module}/{$this->module}Controller.php"));
        $this->assertFileExists(app_path("Providers/{$this->module}ServiceProvider.php"));

        $this->assertFileExists(
            app_path("Repositories/Interfaces/{$this->module}/{$this->module}RepositoryInterface.php")
        );

        $this->assertFileExists(
            app_path("Repositories/Repository/{$this->module}/{$this->module}Repository.php")
        );

        $this->assertFileExists(
            app_path("Services/{$this->module}/{$this->module}Service.php")
        );
    }

    public function test_it_does_not_overwrite_existing_module(): void
    {
        $this->artisan('make:mod', ['name' => $this->module]);

        $this->artisan('make:mod', ['name' => $this->module])
            ->expectsOutput("Module {$this->module} already exists.")
            ->assertExitCode(1);
    }
}
