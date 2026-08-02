<?php

declare(strict_types=1);

namespace Ixspx\ModuleGenerator\Factories;

use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Ixspx\ModuleGenerator\Contracts\ApiSpecificationInterface;
use Ixspx\ModuleGenerator\Specifications\JsonApiSpecification;
use Ixspx\ModuleGenerator\Specifications\ProblemDetailsSpecification;
use Ixspx\ModuleGenerator\Specifications\RestApiSpecification;

class ApiSpecificationFactory
{
    /**
     * Custom driver extension callbacks.
     */
    protected array $customDrivers = [];

    public function __construct(protected Container $container) {}

    /**
     * Create or resolve an API specification driver instance.
     */
    public function make(?string $driver = null): ApiSpecificationInterface
    {
        $driver = $driver ?: (string) config('module-generator.api_specification', 'rest');

        if (isset($this->customDrivers[$driver])) {
            return ($this->customDrivers[$driver])($this->container);
        }

        return match ($driver) {
            'rest'            => $this->container->make(RestApiSpecification::class),
            'jsonapi'         => $this->container->make(JsonApiSpecification::class),
            'problem-details' => $this->container->make(ProblemDetailsSpecification::class),
            default           => class_exists($driver)
                ? $this->container->make($driver)
                : throw new InvalidArgumentException("Unsupported API specification driver [{$driver}]."),
        };
    }

    /**
     * Register a custom API specification driver extension callback.
     */
    public function extend(string $name, callable $callback): void
    {
        $this->customDrivers[$name] = $callback;
    }
}
