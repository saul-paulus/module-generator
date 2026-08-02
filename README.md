# Laravel Module Generator & Multi-Specification API Foundation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ixspx/module-generator.svg?style=flat-square)](https://packagist.org/packages/ixspx/module-generator)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.2%20%7C%7C%20%5E8.3-8892BF.svg?style=flat-square)](https://www.php.net/)
[![Laravel Framework](https://img.shields.io/badge/Laravel-%5E10.0%20%7C%7C%20%5E11.0%20%7C%7C%20%5E12.0%20%7C%7C%20%5E13.0-FF2D20.svg?style=flat-square)](https://laravel.com/)
[![License](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)](LICENSE)

`ixspx/module-generator` is an enterprise-grade Laravel developer tooling package designed to accelerate development by scaffolding cleanly layered application modules (Model, Repository Interface, Repository Implementation, Service, Controller, and Service Provider) while establishing a **driver-driven, multi-specification REST API foundation** supporting **Standard REST**, **JSON:API 1.1**, **RFC 7807 Problem Details**, and custom API drivers.

---

## Table of Contents

- [1. Project Overview](#1-project-overview)
- [2. Key Features](#2-key-features)
- [3. Architecture Overview](#3-architecture-overview)
- [4. API Specification Drivers](#4-api-specification-drivers)
- [5. Generated Structure](#5-generated-structure)
- [6. Design Principles](#6-design-principles)
- [7. Installation](#7-installation)
- [8. Configuration](#8-configuration)
- [9. Available Artisan Commands](#9-available-artisan-commands)
- [10. Extensibility & Custom Drivers](#10-extensibility--custom-drivers)
- [11. Usage Examples](#11-usage-examples)
- [12. Package Structure](#12-package-structure)
- [13. Best Practices](#13-best-practices)
- [14. Limitations & Solutions](#14-limitations--solutions)
- [15. License](#15-license)

---

## 1. Project Overview

### What is this package?
`ixspx/module-generator` is a dual-purpose Laravel package:
1. **Module Scaffolder (`make:mod`)**: Scaffolds modular domain layers adhering to a Service-Repository pattern, providing separation of database concerns, domain business logic, HTTP delivery, and dependency injection.
2. **Multi-Specification API Starter (`make:api-install` & `make:api-response`)**: Provisions a specification-aware API response engine, middleware to enforce JSON/JSON:API headers, and a centralized exception registrar. Switch between Standard REST, JSON:API 1.1, or Problem Details instantly via configuration.

---

## 2. Key Features

- 🏗 **Full-Stack Module Generator**: Scaffolds Model, Interface, Concrete Repository, Service, Controller, and Service Provider via `php artisan make:mod {Name}`.
- 🔌 **Driver-Driven API Architecture**: Switch between Standard REST, JSON:API 1.1, and RFC 7807 Problem Details simply by changing `config('module-generator.api_specification')` or `.env`.
- ⚡ **Auto-Registration of Providers & Routes**: Automatically registers scaffolded providers in `bootstrap/providers.php` and appends RESTful routes to `routes/api.php`.
- 🎨 **Publishable Stubs & Configuration**: Fully customizable code templates via `php artisan vendor:publish --tag=module-generator-stubs` and `module-generator-config`.
- 🔒 **Centralized Exception Handling**: Specification-aware exception mapping for database, validation, auth, and domain exceptions.

---

## 3. Architecture Overview

```
                        ┌────────────────────────────┐
                        │   config/module-generator  │
                        └─────────────┬──────────────┘
                                      │
                                      ▼
                        ┌────────────────────────────┐
                        │  ApiSpecificationFactory   │
                        └─────────────┬──────────────┘
                                      │
            ┌─────────────────────────┼─────────────────────────┐
            │                         │                         │
            ▼                         ▼                         ▼
┌───────────────────────┐ ┌───────────────────────┐ ┌───────────────────────┐
│ RestApiSpecification  │ │ JsonApiSpecification  │ │ProblemDetailsSpecifi… │
└───────────┬───────────┘ └───────────┬───────────┘ └───────────┬───────────┘
            │                         │                         │
            │  application/json       │ application/vnd.api+json│ application/problem+json
            ▼                         ▼                         ▼
┌───────────────────────────────────────────────────────────────────────────┐
│   Cross-Cutting Services: ApiResponse / ForceJsonResponse / Exception     │
└───────────────────────────────────────────────────────────────────────────┘
```

---

## 4. API Specification Drivers

### A. Standard REST API (Default)
- **Media Type**: `application/json`
```json
{
  "success": true,
  "responseCode": 200,
  "message": "Data retrieved successfully",
  "data": { "id": 1, "name": "John Doe" },
  "meta": { "count": 1 }
}
```

### B. JSON:API 1.1 Specification (`API_SPECIFICATION=jsonapi`)
- **Media Type**: `application/vnd.api+json`
```json
{
  "jsonapi": { "version": "1.1" },
  "data": {
    "type": "users",
    "id": "1",
    "attributes": { "name": "John Doe" }
  }
}
```

### C. RFC 7807 Problem Details (`API_SPECIFICATION=problem-details`)
- **Media Type**: `application/problem+json` for error responses
```json
{
  "type": "http://localhost/errors/validation-error",
  "title": "Validation error",
  "status": 422,
  "detail": "The email field is required.",
  "instance": "http://localhost/api/v1/users",
  "invalid-params": [
    { "name": "email", "reason": "The email field is required." }
  ]
}
```

---

## 5. Generated Structure

```
app/
├── Exceptions/
│   └── ApiExceptionRegistrar.php              # Global Exception Handler Registrar
├── Http/
│   ├── Controllers/
│   │   └── {ModuleName}/
│   │       └── {ModuleName}Controller.php     # RESTful Controller
│   └── Middleware/
│       └── ForceJsonResponse.php              # Specification-Aware Header Middleware
├── Models/
│   └── {ModuleName}/
│       └── {ModuleName}Model.php              # Eloquent Model
├── Providers/
│   └── {ModuleName}ServiceProvider.php        # Service Provider
├── Repositories/
│   ├── Interfaces/
│   │   └── {ModuleName}/
│   │       └── {ModuleName}Interface.php      # Repository Contract
│   └── Repository/
│       └── {ModuleName}/
│           └── {ModuleName}Repository.php     # Concrete Repository
├── Services/
│   └── {ModuleName}/
│       └── {ModuleName}Service.php            # Transactional Business Service
└── Support/
    └── ApiResponse.php                        # Specification-Aware Response Helper
```

---

## 6. Design Principles

- **SOLID & Open/Closed Principle (OCP)**: New API specifications can be added without altering package core.
- **Dependency Injection**: Resolves `ApiSpecificationInterface` dynamically via `ApiSpecificationFactory`.

---

## 7. Installation

```bash
composer require ixspx/module-generator
```

### Publish Configuration & Stubs

```bash
php artisan vendor:publish --tag=module-generator-config
php artisan vendor:publish --tag=module-generator-stubs
```

---

## 8. Configuration

```php
// config/module-generator.php
return [
    // Driver: 'rest', 'jsonapi', 'problem-details', or custom class name
    'api_specification' => env('API_SPECIFICATION', 'rest'),

    'jsonapi' => [
        'version'  => '1.1',
        'base_url' => env('APP_URL', 'http://localhost'),
    ],

    'problem_details' => [
        'type_base_url' => env('APP_URL', 'http://localhost') . '/errors',
    ],
];
```

---

## 9. Available Artisan Commands

| Command | Signature | Description | Key Options | Example Usage |
| :--- | :--- | :--- | :--- | :--- |
| **Module Generator** | `make:mod {name}` | Scaffolds complete 6-layer module structure. | `--table=`, `--table-prefix=`, `--style=`, `--no-provider`, `--no-route`, `--force` | `php artisan make:mod OrderPayment` |
| **API Installer** | `make:api-install` | Installs standard API foundation infrastructure. | `--force` | `php artisan make:api-install --force` |
| **API Response Helper** | `make:api-response` | Scaffolds `ApiResponse` support class. | None | `php artisan make:api-response` |

---

## 10. Extensibility & Custom Drivers

Extend the factory with custom API drivers in your `AppServiceProvider`:

```php
use Ixspx\ModuleGenerator\Contracts\ApiSpecificationInterface;
use Ixspx\ModuleGenerator\Factories\ApiSpecificationFactory;

public function boot(ApiSpecificationFactory $factory): void
{
    $factory->extend('company-api', function ($app) {
        return new CustomCompanyApiSpecification();
    });
}
```

---

## 11. Usage Examples

Switch to JSON:API 1.1 in `.env`:

```env
API_SPECIFICATION=jsonapi
```

Switch to RFC 7807 Problem Details in `.env`:

```env
API_SPECIFICATION=problem-details
```

---

## 12. Package Structure

```
.
├── config/
│   └── module-generator.php
├── src/
│   ├── Console/
│   │   └── Commands/
│   ├── Contracts/
│   │   └── ApiSpecificationInterface.php      # Driver Interface
│   ├── Factories/
│   │   └── ApiSpecificationFactory.php        # Driver Factory
│   ├── Specifications/
│   │   ├── JsonApiSpecification.php           # JSON:API 1.1 Driver
│   │   ├── ProblemDetailsSpecification.php    # RFC 7807 Driver
│   │   └── RestApiSpecification.php           # Standard REST Driver
│   ├── Support/
│   └── Traits/
└── tests/
    └── Feature/
        └── ApiSpecificationTest.php
```

---

## 13. Best Practices

1. **Keep Controllers Thin**: Controllers delegate data formatting to `ApiResponse::success()`, which automatically formats payloads according to the configured driver.
2. **Centralize Exception Mapping**: Throw domain exceptions inside services; `ApiExceptionRegistrar` converts them to the active specification driver format.

---

## 14. Limitations & Solutions

- **Multi-Specification Support**: Solved via `ApiSpecificationInterface`, `ApiSpecificationFactory`, and specification drivers (`rest`, `jsonapi`, `problem-details`).
- **Backward Compatibility**: Preserved with `'rest'` as default driver.

---

## 15. License

Licensed under the **MIT License**. See [LICENSE](LICENSE) for details.
