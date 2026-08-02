<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Ixspx\ModuleGenerator\Factories\ApiSpecificationFactory;

class ApiResponse
{
    /**
     * Format a success JSON response envelope using the active specification driver.
     */
    public static function success(
        mixed $data = null,
        string $message = 'The request has been processed successfully',
        int $status = 200,
        array $meta = [],
        array $links = []
    ): JsonResponse {
        /** @var ApiSpecificationFactory $factory */
        $factory = app(ApiSpecificationFactory::class);
        $specification = $factory->make();

        return $specification->formatSuccess($data, $message, $status, $meta, $links);
    }

    /**
     * Format an error or exception JSON response envelope using the active specification driver.
     */
    public static function throw(
        mixed $exception,
        string $message = 'The request could not be processed',
        int $status = 400,
        mixed $errors = null
    ): JsonResponse {
        /** @var ApiSpecificationFactory $factory */
        $factory = app(ApiSpecificationFactory::class);
        $specification = $factory->make();

        return $specification->formatError($exception, $message, $status, $errors);
    }

    /**
     * Format a paginated dataset response envelope using the active specification driver.
     */
    public static function paginate(
        mixed $paginator,
        string $message = 'Data retrieved successfully',
        int $status = 200
    ): JsonResponse {
        /** @var ApiSpecificationFactory $factory */
        $factory = app(ApiSpecificationFactory::class);
        $specification = $factory->make();

        return $specification->formatPaginated($paginator, $message, $status);
    }
}
