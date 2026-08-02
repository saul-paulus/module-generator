<?php

declare(strict_types=1);

namespace Ixspx\ModuleGenerator\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ixspx\ModuleGenerator\Factories\ApiSpecificationFactory;
use Symfony\Component\HttpFoundation\Response;

final class ForceJsonResponse
{
    /**
     * Handle an incoming request and enforce specification-aware JSON headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var ApiSpecificationFactory $factory */
        $factory = app(ApiSpecificationFactory::class);
        $specification = $factory->make();
        $mediaType = $specification->getMediaType();

        // Force Accept header if not explicitly set
        if (!$request->headers->has('Accept')) {
            $request->headers->set('Accept', $mediaType);
        }

        $response = $next($request);

        // If already JsonResponse, ensure Content-Type header matches specification
        if ($response instanceof JsonResponse) {
            if (!$response->headers->has('Content-Type')) {
                $response->headers->set('Content-Type', $mediaType);
            }
            return $response;
        }

        // Convert non-JSON response safely
        return response()->json(
            $response->getContent(),
            $response->getStatusCode(),
            array_merge($response->headers->all(), ['Content-Type' => $mediaType])
        );
    }
}
