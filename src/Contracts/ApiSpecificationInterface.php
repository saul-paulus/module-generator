<?php

declare(strict_types=1);

namespace Ixspx\ModuleGenerator\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ApiSpecificationInterface
{
    /**
     * Get the unique driver name (e.g. 'rest', 'jsonapi', 'problem-details').
     */
    public function getName(): string;

    /**
     * Get the primary HTTP Content-Type / Accept media type.
     */
    public function getMediaType(): string;

    /**
     * Format a success JSON response envelope.
     */
    public function formatSuccess(
        mixed $data = null,
        string $message = 'Request processed successfully',
        int $status = 200,
        array $meta = [],
        array $links = []
    ): JsonResponse;

    /**
     * Format an error or exception JSON response.
     */
    public function formatError(
        mixed $exception,
        string $message = 'The request could not be processed',
        int $status = 400,
        mixed $errors = null
    ): JsonResponse;

    /**
     * Format a paginated dataset response.
     */
    public function formatPaginated(
        mixed $paginator,
        string $message = 'Data retrieved successfully',
        int $status = 200
    ): JsonResponse;

    /**
     * Validate incoming HTTP request headers against specification standards.
     */
    public function validateRequestHeaders(Request $request): bool;
}
