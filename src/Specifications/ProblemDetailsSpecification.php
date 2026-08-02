<?php

declare(strict_types=1);

namespace Ixspx\ModuleGenerator\Specifications;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use Ixspx\ModuleGenerator\Contracts\ApiSpecificationInterface;
use Throwable;

class ProblemDetailsSpecification implements ApiSpecificationInterface
{
    public function getName(): string
    {
        return 'problem-details';
    }

    public function getMediaType(): string
    {
        return 'application/problem+json';
    }

    public function formatSuccess(
        mixed $data = null,
        string $message = 'Request processed successfully',
        int $status = 200,
        array $meta = [],
        array $links = []
    ): JsonResponse {
        return response()->json([
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
            'meta'    => $meta ?: null,
            'links'   => $links ?: null,
        ], $status, ['Content-Type' => 'application/json']);
    }

    public function formatError(
        mixed $exception,
        string $message = 'The request could not be processed',
        int $status = 400,
        mixed $errors = null
    ): JsonResponse {
        if ($exception instanceof Throwable) {
            Log::error($exception->getMessage());
        }

        $typeBaseUrl = config('module-generator.problem_details.type_base_url', 'http://localhost/errors');
        $typeSlug = strtolower(str_replace(' ', '-', $message));
        $typeUrl = "{$typeBaseUrl}/{$typeSlug}";

        $invalidParams = [];

        if ($exception instanceof MessageBag) {
            foreach ($exception->toArray() as $field => $messages) {
                foreach ((array) $messages as $msg) {
                    $invalidParams[] = [
                        'name'   => $field,
                        'reason' => $msg,
                    ];
                }
            }
        } elseif (is_array($errors)) {
            foreach ($errors as $field => $msg) {
                $detail = is_array($msg) ? implode(', ', $msg) : (string) $msg;
                $invalidParams[] = [
                    'name'   => (string) $field,
                    'reason' => $detail,
                ];
            }
        }

        $detail = $exception instanceof Throwable ? $exception->getMessage() : $message;

        $payload = [
            'type'     => $typeUrl,
            'title'    => $message,
            'status'   => $status,
            'detail'   => $detail,
            'instance' => request()->fullUrl(),
        ];

        if (!empty($invalidParams)) {
            $payload['invalid-params'] = $invalidParams;
        }

        return response()->json($payload, $status, [
            'Content-Type' => $this->getMediaType(),
        ]);
    }

    public function formatPaginated(
        mixed $paginator,
        string $message = 'Data retrieved successfully',
        int $status = 200
    ): JsonResponse {
        $data = method_exists($paginator, 'items') ? $paginator->items() : $paginator;
        $meta = [];
        $links = [];

        if (is_object($paginator) && method_exists($paginator, 'currentPage')) {
            $meta = [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => method_exists($paginator, 'total') ? $paginator->total() : null,
                'last_page'    => method_exists($paginator, 'lastPage') ? $paginator->lastPage() : null,
            ];

            $links = [
                'first' => method_exists($paginator, 'url') ? $paginator->url(1) : null,
                'last'  => method_exists($paginator, 'url') && method_exists($paginator, 'lastPage') ? $paginator->url($paginator->lastPage()) : null,
                'prev'  => method_exists($paginator, 'previousPageUrl') ? $paginator->previousPageUrl() : null,
                'next'  => method_exists($paginator, 'nextPageUrl') ? $paginator->nextPageUrl() : null,
            ];
        }

        return $this->formatSuccess($data, $message, $status, $meta, $links);
    }

    public function validateRequestHeaders(Request $request): bool
    {
        return true;
    }
}
