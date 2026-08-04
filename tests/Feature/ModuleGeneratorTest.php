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

    // ─── Single-level namespace assertions ──────────────────────────────────

    public function test_single_level_model_namespace_is_correct(): void
    {
        $this->artisan('make:mod', ['name' => $this->module, '--no-route' => true])
            ->assertExitCode(0);

        $content = file_get_contents(app_path("Models/{$this->module}/{$this->module}Model.php"));
        $this->assertStringContainsString("namespace App\\Models\\{$this->module};", $content);
        $this->assertStringContainsString("class {$this->module}Model extends Model", $content);
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

// ─── Two-level nested namespace: Denda/Esycash ──────────────────────────────

class NestedTwoLevelModuleGeneratorTest extends TestCase
{
    protected string $modulePath  = 'Denda/Esycash';
    protected string $className   = 'Esycash';
    protected string $phpNs       = 'Denda\\Esycash';

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanupNestedFiles();
    }

    public function test_nested_two_level_files_are_created_without_path_duplication(): void
    {
        $this->artisan('make:mod', ['name' => $this->modulePath, '--no-route' => true, '--no-provider' => true])
            ->assertExitCode(0);

        // Files must be at the correct (non-duplicated) path
        $this->assertFileExists(app_path("Models/Denda/Esycash/EsycashModel.php"));
        $this->assertFileExists(app_path("Http/Controllers/Denda/Esycash/EsycashController.php"));
        $this->assertFileExists(app_path("Repositories/Interfaces/Denda/Esycash/EsycashInterface.php"));
        $this->assertFileExists(app_path("Repositories/Repository/Denda/Esycash/EsycashRepository.php"));
        $this->assertFileExists(app_path("Services/Denda/Esycash/EsycashService.php"));

        // The duplicated path must NOT exist
        $this->assertFileDoesNotExist(app_path("Models/Denda/Esycash/Denda/EsycashModel.php"));
    }

    public function test_nested_two_level_model_has_correct_namespace_and_class(): void
    {
        $this->artisan('make:mod', ['name' => $this->modulePath, '--no-route' => true, '--no-provider' => true])
            ->assertExitCode(0);

        $content = file_get_contents(app_path("Models/Denda/Esycash/EsycashModel.php"));

        $this->assertStringContainsString('namespace App\\Models\\Denda\\Esycash;', $content);
        $this->assertStringContainsString('class EsycashModel extends Model', $content);
    }

    public function test_nested_two_level_controller_has_correct_namespace_and_class(): void
    {
        $this->artisan('make:mod', ['name' => $this->modulePath, '--no-route' => true, '--no-provider' => true])
            ->assertExitCode(0);

        $content = file_get_contents(app_path("Http/Controllers/Denda/Esycash/EsycashController.php"));

        $this->assertStringContainsString('namespace App\\Http\\Controllers\\Denda\\Esycash;', $content);
        $this->assertStringContainsString('class EsycashController extends Controller', $content);
        $this->assertStringContainsString('use App\\Services\\Denda\\Esycash\\EsycashService;', $content);
    }

    public function test_nested_two_level_service_has_correct_namespace(): void
    {
        $this->artisan('make:mod', ['name' => $this->modulePath, '--no-route' => true, '--no-provider' => true])
            ->assertExitCode(0);

        $content = file_get_contents(app_path("Services/Denda/Esycash/EsycashService.php"));

        $this->assertStringContainsString('namespace App\\Services\\Denda\\Esycash;', $content);
        $this->assertStringContainsString('use App\\Repositories\\Interfaces\\Denda\\Esycash\\EsycashInterface;', $content);
        $this->assertStringContainsString('class EsycashService', $content);
    }

    public function test_nested_two_level_repository_has_correct_namespace(): void
    {
        $this->artisan('make:mod', ['name' => $this->modulePath, '--no-route' => true, '--no-provider' => true])
            ->assertExitCode(0);

        $content = file_get_contents(app_path("Repositories/Repository/Denda/Esycash/EsycashRepository.php"));

        $this->assertStringContainsString('namespace App\\Repositories\\Repository\\Denda\\Esycash;', $content);
        $this->assertStringContainsString('use App\\Models\\Denda\\Esycash\\EsycashModel;', $content);
        $this->assertStringContainsString('class EsycashRepository implements EsycashInterface', $content);
    }

    public function test_nested_two_level_table_name_uses_leaf_class(): void
    {
        $this->artisan('make:mod', ['name' => $this->modulePath, '--no-route' => true, '--no-provider' => true])
            ->assertExitCode(0);

        $content = file_get_contents(app_path("Models/Denda/Esycash/EsycashModel.php"));

        // Table name is derived from leaf ($className = 'Esycash') → 'esycashes'
        $this->assertStringContainsString("protected string \$table = 'esycashes';", $content);
    }

    public function test_nested_two_level_duplicate_check_works(): void
    {
        $this->artisan('make:mod', ['name' => $this->modulePath, '--no-route' => true, '--no-provider' => true]);

        $this->artisan('make:mod', ['name' => $this->modulePath, '--no-route' => true, '--no-provider' => true])
            ->expectsOutput('Module Denda/Esycash already exists.')
            ->assertExitCode(1);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanupNestedFiles();
    }

    protected function cleanupNestedFiles(): void
    {
        File::deleteDirectory(app_path('Models/Denda'));
        File::deleteDirectory(app_path('Http/Controllers/Denda'));
        File::deleteDirectory(app_path('Repositories/Interfaces/Denda'));
        File::deleteDirectory(app_path('Repositories/Repository/Denda'));
        File::deleteDirectory(app_path('Services/Denda'));
    }
}

// ─── Three-level nested namespace: Finance/Payroll/Salary ───────────────────

class NestedThreeLevelModuleGeneratorTest extends TestCase
{
    protected string $modulePath = 'Finance/Payroll/Salary';
    protected string $className  = 'Salary';

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanupNestedFiles();
    }

    public function test_nested_three_level_files_are_created_without_path_duplication(): void
    {
        $this->artisan('make:mod', ['name' => $this->modulePath, '--no-route' => true, '--no-provider' => true])
            ->assertExitCode(0);

        $this->assertFileExists(app_path("Models/Finance/Payroll/Salary/SalaryModel.php"));
        $this->assertFileExists(app_path("Http/Controllers/Finance/Payroll/Salary/SalaryController.php"));
        $this->assertFileExists(app_path("Repositories/Interfaces/Finance/Payroll/Salary/SalaryInterface.php"));
        $this->assertFileExists(app_path("Repositories/Repository/Finance/Payroll/Salary/SalaryRepository.php"));
        $this->assertFileExists(app_path("Services/Finance/Payroll/Salary/SalaryService.php"));

        // No duplicated path segments
        $this->assertFileDoesNotExist(app_path("Models/Finance/Payroll/Salary/Finance/SalaryModel.php"));
    }

    public function test_nested_three_level_model_has_correct_namespace_and_class(): void
    {
        $this->artisan('make:mod', ['name' => $this->modulePath, '--no-route' => true, '--no-provider' => true])
            ->assertExitCode(0);

        $content = file_get_contents(app_path("Models/Finance/Payroll/Salary/SalaryModel.php"));

        $this->assertStringContainsString('namespace App\\Models\\Finance\\Payroll\\Salary;', $content);
        $this->assertStringContainsString('class SalaryModel extends Model', $content);
    }

    public function test_nested_three_level_controller_has_correct_namespace(): void
    {
        $this->artisan('make:mod', ['name' => $this->modulePath, '--no-route' => true, '--no-provider' => true])
            ->assertExitCode(0);

        $content = file_get_contents(app_path("Http/Controllers/Finance/Payroll/Salary/SalaryController.php"));

        $this->assertStringContainsString('namespace App\\Http\\Controllers\\Finance\\Payroll\\Salary;', $content);
        $this->assertStringContainsString('class SalaryController extends Controller', $content);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanupNestedFiles();
    }

    protected function cleanupNestedFiles(): void
    {
        File::deleteDirectory(app_path('Models/Finance'));
        File::deleteDirectory(app_path('Http/Controllers/Finance'));
        File::deleteDirectory(app_path('Repositories/Interfaces/Finance'));
        File::deleteDirectory(app_path('Repositories/Repository/Finance'));
        File::deleteDirectory(app_path('Services/Finance'));
    }
}
