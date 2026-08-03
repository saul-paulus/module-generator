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
        $this->cleanupModuleFiles();
    }

    public function test_it_generates_full_module_structure(): void
    {
        $this->artisan('make:mod', ['name' => $this->module, '--no-route' => true])
            ->assertExitCode(0);

        $this->assertFileExists(app_path("Models/{$this->module}/{$this->module}Model.php"));
        $this->assertFileExists(app_path("Http/Controllers/{$this->module}/{$this->module}Controller.php"));
        $this->assertFileExists(app_path("Providers/{$this->module}ServiceProvider.php"));

        $this->assertFileExists(
            app_path("Repositories/Interfaces/{$this->module}/{$this->module}Interface.php")
        );

        $this->assertFileExists(
            app_path("Repositories/Repository/{$this->module}/{$this->module}Repository.php")
        );

        $this->assertFileExists(
            app_path("Services/{$this->module}/{$this->module}Service.php")
        );
    }

    public function test_it_generates_model_with_correct_table_name(): void
    {
        $this->artisan('make:mod', ['name' => $this->module, '--no-route' => true])
            ->assertExitCode(0);

        $modelPath = app_path("Models/{$this->module}/{$this->module}Model.php");
        $content = file_get_contents($modelPath);

        $this->assertStringContainsString("protected string \$table = 'test_users';", $content);
    }

    public function test_it_supports_custom_table_prefix_and_name(): void
    {
        $this->artisan('make:mod', [
            'name' => $this->module,
            '--table' => 'custom_users',
            '--table-prefix' => 'tbl_',
            '--no-route' => true,
        ])->assertExitCode(0);

        $modelPath = app_path("Models/{$this->module}/{$this->module}Model.php");
        $content = file_get_contents($modelPath);

        $this->assertStringContainsString("protected string \$table = 'tbl_custom_users';", $content);
    }

    public function test_it_generates_restful_controller_actions_by_default(): void
    {
        $this->artisan('make:mod', ['name' => $this->module, '--no-route' => true])
            ->assertExitCode(0);

        $controllerPath = app_path("Http/Controllers/{$this->module}/{$this->module}Controller.php");
        $content = file_get_contents($controllerPath);

        $this->assertStringContainsString('public function index()', $content);
        $this->assertStringContainsString('public function show(', $content);
        $this->assertStringContainsString('public function store(', $content);
        $this->assertStringContainsString('public function update(', $content);
        $this->assertStringContainsString('public function destroy(', $content);
    }

    public function test_it_supports_legacy_handler_controller_style(): void
    {
        $this->artisan('make:mod', [
            'name' => $this->module,
            '--style' => 'handler',
            '--no-route' => true,
        ])->assertExitCode(0);

        $controllerPath = app_path("Http/Controllers/{$this->module}/{$this->module}Controller.php");
        $content = file_get_contents($controllerPath);

        $this->assertStringContainsString('public function HandlerGetAll()', $content);
        $this->assertStringContainsString('public function HandlerGetById(', $content);
    }

    public function test_it_does_not_overwrite_existing_module_without_force(): void
    {
        $this->artisan('make:mod', ['name' => $this->module, '--no-route' => true]);

        $this->artisan('make:mod', ['name' => $this->module, '--no-route' => true])
            ->expectsOutput("Module {$this->module} already exists.")
            ->assertExitCode(1);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanupModuleFiles();
    }

    protected function cleanupModuleFiles(): void
    {
        File::deleteDirectory(app_path("Models/{$this->module}"));
        File::deleteDirectory(app_path("Http/Controllers/{$this->module}"));
        File::delete(app_path("Providers/{$this->module}ServiceProvider.php"));

        File::deleteDirectory(app_path("Repositories/Interfaces/{$this->module}"));
        File::deleteDirectory(app_path("Repositories/Repository/{$this->module}"));
        File::deleteDirectory(app_path("Services/{$this->module}"));

        $providerLine = "App\\Providers\\{$this->module}ServiceProvider::class,";
        foreach ([base_path('bootstrap/providers.php'), config_path('app.php')] as $path) {
            if (File::exists($path)) {
                $content = File::get($path);
                if (str_contains($content, $providerLine)) {
                    File::put($path, str_replace($providerLine, '', $content));
                }
            }
        }
    }
}
