<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;

class ModuleGeneratorTest extends TestCase
{
    protected string $module = 'TestUser';

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure the module does not exist before each test
        File::delete(app_path("Models/{$this->module}/{$this->module}.php"));
        File::delete(app_path("Http/Controllers/{$this->module}/{$this->module}Controller.php"));
        File::delete(app_path("Providers/{$this->module}ServiceProvider.php"));
        File::deleteDirectory(app_path("Repositories/Interfaces/{$this->module}"));
        File::deleteDirectory(app_path("Repositories/Repository/{$this->module}"));
        File::deleteDirectory(app_path("Services/{$this->module}"));
    }

    public function test_it_generates_full_module_structure(): void
    {
        $this->artisan('make:mod', ['name' => $this->module])
            ->assertExitCode(0);

        // Model
        $this->assertFileExists(
            app_path("Models/{$this->module}/{$this->module}.php")
        );

        // Controller
        $this->assertFileExists(
            app_path("Http/Controllers/{$this->module}/{$this->module}Controller.php")
        );

        // Provider
        $this->assertFileExists(
            app_path("Providers/{$this->module}ServiceProvider.php")
        );

        // Repository Interface
        $this->assertFileExists(
            app_path("Repositories/Interfaces/{$this->module}/{$this->module}Interface.php")
        );

        // Repository Implementation
        $this->assertFileExists(
            app_path("Repositories/Repository/{$this->module}/{$this->module}Repository.php")
        );

        // Service
        $this->assertFileExists(
            app_path("Services/{$this->module}/{$this->module}Service.php")
        );
    }

    public function test_it_does_not_overwrite_existing_module(): void
    {
        // First run
        $this->artisan('make:mod', ['name' => $this->module]);

        // Second run should fail
        $this->artisan('make:mod', ['name' => $this->module])
            ->expectsOutput("Module {$this->module} already exists.")
            ->assertExitCode(1);
    }
}
