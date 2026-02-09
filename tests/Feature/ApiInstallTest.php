<?php

namespace Ixspx\ModuleGenerator\Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Ixspx\ModuleGenerator\Tests\TestCase;

class ApiInstallTest extends TestCase
{
    protected Filesystem $files;

    protected function setUp(): void
    {
        parent::setUp();
        $this->files = new Filesystem();
    }

    /** @test */
    public function module_generator_config_is_available(): void
    {
        $this->assertTrue(
            is_array(config('module-generator.middleware'))
        );
    }
    /** @test */
    public function it_installs_all_api_starter_files(): void
    {
        $this->files->ensureDirectoryExists(base_path('routes'));

        $this->artisan('make:api-install')
            ->assertExitCode(0);

        $this->assertFileExists(base_path('routes/api.php'));
        $this->assertFileExists(app_path('Http/Middleware/ForceJsonResponse.php'));
        $this->assertFileExists(app_path('Support/ApiResponse.php'));
        $this->assertFileExists(app_path('Exceptions/ApiExceptionRegistrar.php'));
    }

    /** @test */
    public function it_overwrites_files_when_force_option_is_used(): void
    {
        $path = app_path('Support/ApiResponse.php');

        $this->files->put($path, 'OLD CONTENT');

        $this->artisan('make:api-install --force')
            ->assertExitCode(0);

        $this->assertNotEquals(
            'OLD CONTENT',
            $this->files->get($path)
        );
    }

    /** @test */
    public function it_skips_file_when_its_stub_is_missing(): void
    {
        $this->files->ensureDirectoryExists(base_path('routes'));

        // Hapus satu stub
        rename(
            __DIR__ . '/../../src/Stubs/api-route.stub',
            __DIR__ . '/../../src/Stubs/api-route.stub.bak'
        );

        $this->artisan('make:api-install')->assertExitCode(0);

        // api.php tidak dibuat
        $this->assertFileDoesNotExist(base_path('routes/api.php'));

        // stub lain tetap dibuat
        $this->assertFileExists(app_path('Support/ApiResponse.php'));

        // restore
        rename(
            __DIR__ . '/../../src/Stubs/api-route.stub.bak',
            __DIR__ . '/../../src/Stubs/api-route.stub'
        );
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        $this->files->delete([
            base_path('routes/api.php'),
            app_path('Http/Middleware/ForceJsonResponse.php'),
            app_path('Support/ApiResponse.php'),
            app_path('Exceptions/ApiExceptionRegistrar.php'),
        ]);
    }
}
