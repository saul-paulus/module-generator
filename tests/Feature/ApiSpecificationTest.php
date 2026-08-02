<?php

namespace Ixspx\ModuleGenerator\Tests\Feature;

use Illuminate\Support\MessageBag;
use Ixspx\ModuleGenerator\Contracts\ApiSpecificationInterface;
use Ixspx\ModuleGenerator\Factories\ApiSpecificationFactory;
use Ixspx\ModuleGenerator\Specifications\JsonApiSpecification;
use Ixspx\ModuleGenerator\Specifications\ProblemDetailsSpecification;
use Ixspx\ModuleGenerator\Specifications\RestApiSpecification;
use Ixspx\ModuleGenerator\Support\ApiResponse;
use Ixspx\ModuleGenerator\Tests\TestCase;

class ApiSpecificationTest extends TestCase
{
    public function test_default_driver_is_rest(): void
    {
        /** @var ApiSpecificationFactory $factory */
        $factory = app(ApiSpecificationFactory::class);
        $driver = $factory->make();

        $this->assertInstanceOf(RestApiSpecification::class, $driver);
        $this->assertEquals('rest', $driver->getName());
        $this->assertEquals('application/json', $driver->getMediaType());
    }

    public function test_rest_api_specification_formatting(): void
    {
        config(['module-generator.api_specification' => 'rest']);

        $response = \App\Support\ApiResponse::success(['id' => 1, 'name' => 'John']);
        $payload = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($payload['success']);
        $this->assertEquals(200, $payload['responseCode']);
        $this->assertEquals(['id' => 1, 'name' => 'John'], $payload['data']);
    }

    public function test_json_api_specification_formatting(): void
    {
        config(['module-generator.api_specification' => 'jsonapi']);

        /** @var ApiSpecificationFactory $factory */
        $factory = app(ApiSpecificationFactory::class);
        $driver = $factory->make();

        $this->assertInstanceOf(JsonApiSpecification::class, $driver);
        $this->assertEquals('application/vnd.api+json', $driver->getMediaType());

        $response = \App\Support\ApiResponse::success(['id' => 10, 'name' => 'Jane']);
        $payload = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/vnd.api+json', $response->headers->get('Content-Type'));
        $this->assertEquals('1.1', $payload['jsonapi']['version']);
        $this->assertEquals('10', $payload['data']['id']);
        $this->assertEquals(['name' => 'Jane'], $payload['data']['attributes']);
    }

    public function test_json_api_error_formatting(): void
    {
        config(['module-generator.api_specification' => 'jsonapi']);

        $errors = new MessageBag(['email' => ['The email field is required.']]);
        $response = \App\Support\ApiResponse::throw($errors, 'Validation error', 422);
        $payload = json_decode($response->getContent(), true);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('application/vnd.api+json', $response->headers->get('Content-Type'));
        $this->assertEquals('422', $payload['errors'][0]['status']);
        $this->assertEquals('The email field is required.', $payload['errors'][0]['detail']);
        $this->assertEquals('/data/attributes/email', $payload['errors'][0]['source']['pointer']);
    }

    public function test_problem_details_specification_formatting(): void
    {
        config(['module-generator.api_specification' => 'problem-details']);

        /** @var ApiSpecificationFactory $factory */
        $factory = app(ApiSpecificationFactory::class);
        $driver = $factory->make();

        $this->assertInstanceOf(ProblemDetailsSpecification::class, $driver);
        $this->assertEquals('application/problem+json', $driver->getMediaType());

        $errors = new MessageBag(['email' => ['The email field is required.']]);
        $response = \App\Support\ApiResponse::throw($errors, 'Validation error', 422);
        $payload = json_decode($response->getContent(), true);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->headers->get('Content-Type'));
        $this->assertEquals('Validation error', $payload['title']);
        $this->assertEquals(422, $payload['status']);
        $this->assertEquals('email', $payload['invalid-params'][0]['name']);
    }

    public function test_custom_driver_extension(): void
    {
        /** @var ApiSpecificationFactory $factory */
        $factory = app(ApiSpecificationFactory::class);

        $factory->extend('custom-test', function () {
            return new class implements ApiSpecificationInterface {
                public function getName(): string { return 'custom-test'; }
                public function getMediaType(): string { return 'application/custom+json'; }
                public function formatSuccess(mixed $data = null, string $message = '...', int $status = 200, array $meta = [], array $links = []): \Illuminate\Http\JsonResponse {
                    return response()->json(['custom_success' => true, 'payload' => $data], $status);
                }
                public function formatError(mixed $exception, string $message = '...', int $status = 400, mixed $errors = null): \Illuminate\Http\JsonResponse {
                    return response()->json(['custom_error' => true], $status);
                }
                public function formatPaginated(mixed $paginator, string $message = '...', int $status = 200): \Illuminate\Http\JsonResponse {
                    return response()->json(['custom_page' => true], $status);
                }
                public function validateRequestHeaders(\Illuminate\Http\Request $request): bool { return true; }
            };
        });

        config(['module-generator.api_specification' => 'custom-test']);

        $response = \App\Support\ApiResponse::success(['foo' => 'bar']);
        $payload = json_decode($response->getContent(), true);

        $this->assertTrue($payload['custom_success']);
        $this->assertEquals(['foo' => 'bar'], $payload['payload']);
    }
}
