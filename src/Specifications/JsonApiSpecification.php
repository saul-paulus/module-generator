<?php

declare(strict_types=1);

namespace Ixspx\ModuleGenerator\Specifications;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use Ixspx\ModuleGenerator\Contracts\ApiSpecificationInterface;
use Throwable;

class JsonApiSpecification implements ApiSpecificationInterface
{
    public function getName(): string
    {
        return 'jsonapi';
    }

    public function getMediaType(): string
    {
        return 'application/vnd.api+json';
    }

    public function formatSuccess(
        mixed $data = null,
        string $message = 'Request processed successfully',
        int $status = 200,
        array $meta = [],
        array $links = []
    ): JsonResponse {
        $formattedData = $this->transformResourceData($data);

        $responsePayload = [
            'jsonapi' => ['version' => config('module-generator.jsonapi.version', '1.1')],
            'data'    => $formattedData,
        ];

        if (!empty($meta) || $message !== 'Request processed successfully') {
            $responsePayload['meta'] = array_merge(['message' => $message], $meta);
        }

        if (!empty($links)) {
            $responsePayload['links'] = $links;
        }

        return response()->json($responsePayload, $status, [
            'Content-Type' => $this->getMediaType(),
        ]);
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

        $errorObjects = [];

        if ($exception instanceof MessageBag) {
            foreach ($exception->toArray() as $field => $messages) {
                foreach ((array) $messages as $msg) {
                    $errorObjects[] = [
                        'status' => (string) $status,
                        'title'  => $message,
                        'detail' => $msg,
                        'source' => ['pointer' => "/data/attributes/{$field}"],
                    ];
                }
            }
        } elseif ($exception instanceof Throwable) {
            $errorObjects[] = [
                'status' => (string) $status,
                'title'  => $message,
                'detail' => $exception->getMessage(),
            ];
        } elseif (is_array($errors)) {
            foreach ($errors as $field => $msg) {
                $detail = is_array($msg) ? implode(', ', $msg) : (string) $msg;
                $errorObjects[] = [
                    'status' => (string) $status,
                    'title'  => $message,
                    'detail' => $detail,
                    'source' => ['pointer' => "/data/attributes/{$field}"],
                ];
            }
        } else {
            $errorObjects[] = [
                'status' => (string) $status,
                'title'  => $message,
                'detail' => (string) ($exception ?: 'An error occurred'),
            ];
        }

        return response()->json([
            'jsonapi' => ['version' => config('module-generator.jsonapi.version', '1.1')],
            'errors'  => $errorObjects,
        ], $status, [
            'Content-Type' => $this->getMediaType(),
        ]);
    }

    public function formatPaginated(
        mixed $paginator,
        string $message = 'Data retrieved successfully',
        int $status = 200
    ): JsonResponse {
        $items = method_exists($paginator, 'items') ? $paginator->items() : $paginator;
        $data = $this->transformResourceData($items);

        $meta = [];
        $links = [];

        if (is_object($paginator) && method_exists($paginator, 'currentPage')) {
            $meta = [
                'page' => [
                    'current'  => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total'    => method_exists($paginator, 'total') ? $paginator->total() : null,
                    'last'     => method_exists($paginator, 'lastPage') ? $paginator->lastPage() : null,
                ],
            ];

            $links = [
                'self'  => method_exists($paginator, 'url') ? $paginator->url($paginator->currentPage()) : null,
                'first' => method_exists($paginator, 'url') ? $paginator->url(1) : null,
                'last'  => method_exists($paginator, 'url') && method_exists($paginator, 'lastPage') ? $paginator->url($paginator->lastPage()) : null,
                'prev'  => method_exists($paginator, 'previousPageUrl') ? $paginator->previousPageUrl() : null,
                'next'  => method_exists($paginator, 'nextPageUrl') ? $paginator->nextPageUrl() : null,
            ];
        }

        return response()->json([
            'jsonapi' => ['version' => config('module-generator.jsonapi.version', '1.1')],
            'data'    => $data,
            'meta'    => array_merge(['message' => $message], $meta),
            'links'   => array_filter($links),
        ], $status, [
            'Content-Type' => $this->getMediaType(),
        ]);
    }

    public function validateRequestHeaders(Request $request): bool
    {
        $accept = $request->header('Accept');
        if ($accept && str_contains($accept, 'application/vnd.api+json')) {
            return true;
        }

        return false;
    }

    protected function transformResourceData(mixed $data): mixed
    {
        if ($data === null) {
            return null;
        }

        if (is_array($data) || $data instanceof \Traversable) {
            $transformed = [];
            foreach ($data as $key => $item) {
                $transformed[] = $this->transformSingleItem($item, $key);
            }
            return $transformed;
        }

        return $this->transformSingleItem($data);
    }

    protected function transformSingleItem(mixed $item, mixed $key = null): array
    {
        if (is_array($item)) {
            $id = (string) ($item['id'] ?? $key ?? 1);
            $type = $item['type'] ?? 'resources';
            unset($item['id'], $item['type']);

            return [
                'type'       => $type,
                'id'         => $id,
                'attributes' => $item,
            ];
        }

        if (is_object($item)) {
            $id = (string) ($item->id ?? $key ?? 1);
            $type = property_exists($item, 'type') ? $item->type : strtolower(class_basename($item));
            $attributes = method_exists($item, 'toArray') ? $item->toArray() : (array) $item;
            unset($attributes['id'], $attributes['type']);

            return [
                'type'       => $type,
                'id'         => $id,
                'attributes' => $attributes,
            ];
        }

        return [
            'type'       => 'items',
            'id'         => (string) ($key ?? 1),
            'attributes' => ['value' => $item],
        ];
    }
}
