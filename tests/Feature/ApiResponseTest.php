<?php

namespace Ixspx\ModuleGenerator\Tests\Feature;

use Illuminate\Support\Facades\File;
use Ixspx\ModuleGenerator\Tests\TestCase;

class ApiResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        File::delete(app_path('Support/ApiResponse.php'));
    }

    public function test_it_generates_api_response_helper_classes(): void
    {
        $this->artisan('make:api-response')
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Support/ApiResponse.php'));
    }

    public function test_it_does_not_overwrite_existing_api_response_classes(): void
    {
        $this->artisan('make:api-response');

        $this->artisan('make:api-response')
            ->expectsOutput('Skipped: ' . app_path('Support/ApiResponse.php') . ' already exists.')
            ->assertExitCode(0);
    }
}
