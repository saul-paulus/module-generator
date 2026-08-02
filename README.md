# Laravel Module Generator & API Foundation (`ixspx/module-generator`)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ixspx/module-generator.svg?style=flat-square)](https://packagist.org/packages/ixspx/module-generator)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.2%20%7C%7C%20%5E8.3-8892BF.svg?style=flat-square)](https://www.php.net/)
[![Laravel Framework](https://img.shields.io/badge/Laravel-%5E10.0%20%7C%7C%20%5E11.0%20%7C%7C%20%5E12.0%20%7C%7C%20%5E13.0-FF2D20.svg?style=flat-square)](https://laravel.com/)
[![License](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)](LICENSE)

`ixspx/module-generator` is an enterprise-grade Laravel developer tooling package designed to accelerate development by scaffolding cleanly layered application modules (Model, Repository Interface, Repository Implementation, Service, Controller, and Service Provider) while establishing a standardized, centralized REST API response and exception handling architecture.

---

## Table of Contents

- [1. Project Overview](#1-project-overview)
- [2. Key Features](#2-key-features)
- [3. Architecture Overview](#3-architecture-overview)
- [4. Generated Structure](#4-generated-structure)
- [5. Design Principles](#5-design-principles)
- [6. Installation](#6-installation)
- [7. Configuration](#7-configuration)
- [8. Available Artisan Commands](#8-available-artisan-commands)
- [9. Generated Files Breakdown](#9-generated-files-breakdown)
- [10. API Standard](#10-api-standard)
- [11. Usage Examples](#11-usage-examples)
- [12. Package Structure](#12-package-structure)
- [13. Extensibility](#13-extensibility)
- [14. Best Practices](#14-best-practices)
- [15. Limitations](#15-limitations)
- [16. Future Improvements](#16-future-improvements)
- [17. Contributing](#17-contributing)
- [18. License](#18-license)
- [Documentation Review Summary](#documentation-review-summary)

---

## 1. Project Overview

### What is this package?

`ixspx/module-generator` is a dual-purpose Laravel package:

1. **Module Scaffolder (`make:mod`)**: Generates modular domain layers adhering to a Service-Repository pattern, providing separation of database concerns, domain business logic, HTTP delivery, and dependency injection.
2. **API Infrastructure Starter (`make:api-install` & `make:api-response`)**: Provisions a unified JSON response envelope, middleware to force JSON headers, and a centralized exception handler to translate framework, validation, domain, and database exceptions into consistent JSON payloads.

### What problems does it solve?

- **Monolithic Controller Bloat**: Prevents controllers from containing database queries, transaction management, or raw business logic.
- **Inconsistent API Payloads**: Eliminates divergent API response formats across different endpoints and developers.
- **Scattered Exception Handling**: Replaces ad-hoc `try-catch` blocks inside controllers with a global, centralized exception registrar.
- **Boilerplate Fatigue**: Eliminates manual creation of Interfaces, Repositories, Services, and Providers for every new feature domain.

### Who should use it?

- **Enterprise Engineering Teams**: Teams building large-scale, multi-domain Laravel applications or microservices requiring strict architectural governance.
- **API Developers**: Engineers building SPA backends, mobile app APIs, or public web services that demand standardized REST JSON envelopes.
- **Clean Architecture Advocates**: Developers who value loose coupling, testability (mockable repositories), and HTTP-agnostic domain services.

### Why does it exist?

Standard Laravel defaults mix HTTP handling and Eloquent models closely. `ixspx/module-generator` bridges the gap between fast scaffolding and enterprise architectural discipline, enabling developers to bootstrap maintainable, decoupled domain modules in seconds.

---

## 2. Key Features

- 🏗 **Full-Stack Module Generator**: Command (`make:mod`) generates 6 domain artifacts per module (Model, Repository Interface, Concrete Repository, Service Layer, Controller, and Service Provider).
- 🔒 **Centralized Exception Handling**: `ApiExceptionRegistrar` intercepts database (`PDOException`, `QueryException`), routing (`NotFoundHttpException`), auth, validation (`ValidationException`), and domain (`DomainException`) errors into predictable JSON responses.
- 📦 **Unified JSON Response Envelopes**: `ApiResponse` helper guarantees standard `success`, `responseCode`, `message`, `data`, `meta`, `links`, and `errors` structural contracts.
- 🌐 **JSON Enforcement Middleware**: `ForceJsonResponse` appends `Accept: application/json` headers to incoming HTTP requests and safely converts non-JSON outputs.
- ⚡ **Automated Dependency Injection**: Scaffolds dedicated Service Providers (`App\Providers\{Module}ServiceProvider`) pre-configured to bind repository interfaces to concrete Eloquent implementations.
- 💼 **Transactional Business Layer**: Scaffolded services automatically encapsulate database operations inside `DB::transaction(...)` blocks.
- 🔌 **Zero Third-Party Database Lock-in**: Business services depend exclusively on repository interfaces (`{Module}Interface`), allowing effortless data-source swapping and unit-testing with mocks.

---

## 3. Architecture Overview

### Generated Module Layering

Modules generated via `php artisan make:mod {Name}` follow a strict unidirectional data flow:

```
[ HTTP Request ]
       │
       ▼
┌──────────────┐
│  Controller  │  (Http/Controllers/{Module}/{Module}Controller.php)
└──────┬───────┘  • Delivery Layer: Validates transport, calls Service, returns ApiResponse envelope
       │
       ▼
┌──────────────┐
│   Service    │  (Services/{Module}/{ModuleService}.php)
└──────┬───────┘  • Business Domain Layer: Enforces rules, handles DB transactions, HTTP-agnostic
       │
       ▼
┌──────────────┐
│  Interface   │  (Repositories/Interfaces/{Module}/{ModuleInterface}.php)
└──────┬───────┘  • Abstraction Contract: Defines data-access signatures
       │
       ▼
┌──────────────┐
│  Repository  │  (Repositories/Repository/{Module}/{ModuleRepository}.php)
└──────┬───────┘  • Persistence Layer: Executes Eloquent ORM operations
       │
       ▼
┌──────────────┐
│    Model     │  (Models/{Module}/{ModuleModel}.php)
└──────────────┘  • Database Mapping Layer: Eloquent schema definition
```

### API Request / Response & Exception Lifecycle

```
                           [ Incoming HTTP Request ]
                                       │
                                       ▼
                           ┌──────────────────────┐
                           │ ForceJsonResponse    │  Forces `Accept: application/json`
                           │ Middleware           │
                           └──────────┬───────────┘
                                      │
                                      ▼
                           ┌──────────────────────┐
                           │ Controller / Service │
                           └──────────┬───────────┘
                                      │
              ┌───────────────────────┴───────────────────────┐
              │                                               │
    [ Standard Execution ]                                 [ Exception Thrown ]
              │                                               │
              ▼                                               ▼
    ┌──────────────────┐                           ┌──────────────────────┐
    │  ApiResponse     │                           │ ApiExceptionRegistrar│
    │  ::success(...)  │                           └──────────┬───────────┘
    └─────────┬────────┘                                      │
              │                                               ▼
              │                                    ┌──────────────────────┐
              │                                    │  ApiResponse         │
              │                                    │  ::throw(...)        │
              │                                    └──────────┬───────────┘
              │                                               │
              └───────────────────────┬───────────────────────┘
                                      │
                                      ▼
                           [ Standardized JSON Response ]
```

---

## 4. Generated Structure

When scaffolding modules or setting up the API foundation, files are generated directly inside your host application's `app/` and `routes/` directories:

```
app/
├── Exceptions/
│   └── ApiExceptionRegistrar.php              # Global Exception Handler Registrar
├── Http/
│   ├── Controllers/
│   │   └── {ModuleName}/
│   │       └── {ModuleName}Controller.php     # HTTP Delivery Layer
│   └── Middleware/
│       └── ForceJsonResponse.php              # Request/Response Header Enforcer
├── Models/
│   └── {ModuleName}/
│       └── {ModuleName}Model.php              # Eloquent Entity Representation
├── Providers/
│   └── {ModuleName}ServiceProvider.php        # DI Container Binding Provider
├── Repositories/
│   ├── Interfaces/
│   │   └── {ModuleName}/
│   │       └── {ModuleName}Interface.php      # Repository Contract
│   └── Repository/
│       └── {ModuleName}/
│           └── {ModuleName}Repository.php     # Concrete Eloquent Repository
├── Services/
│   └── {ModuleName}/
│       └── {ModuleName}Service.php            # Domain Business Logic & Transactions
└── Support/
    └── ApiResponse.php                        # Standardized Response Builder
routes/
└── api.php                                    # API Route Stub File
```

### Class Responsibilities Matrix

| Generated Class             | Architectural Layer    | Primary Responsibility                                                                                                                |
| :-------------------------- | :--------------------- | :------------------------------------------------------------------------------------------------------------------------------------ |
| **`{Name}Controller`**      | Delivery (HTTP)        | Receives HTTP requests, calls domain service methods, constructs `ApiResponse::success()` payloads. Thin and predictable.             |
| **`{Name}Service`**         | Application / Business | Encapsulates domain logic, executes database operations within `DB::transaction()`, performs domain checks, throws domain exceptions. |
| **`{Name}Interface`**       | Data Abstraction       | Declares data access signatures (`getAll`, `findById`, `create`, `update`, `delete`, `findBy`, `paginate`).                           |
| **`{Name}Repository`**      | Persistence (Data)     | Implements `{Name}Interface` using Eloquent ORM queries on `{Name}Model`.                                                             |
| **`{Name}Model`**           | Domain / Data          | Extends `Illuminate\Database\Eloquent\Model`. Maps to the underlying database table and fillable properties.                          |
| **`{Name}ServiceProvider`** | Infrastructure         | Binds `{Name}Interface` to `{Name}Repository` in the Laravel Service Container.                                                       |
| **`ApiResponse`**           | Cross-Cutting Support  | Static utility formatting standard success envelopes and logging error stack traces.                                                  |
| **`ForceJsonResponse`**     | HTTP Middleware        | Guarantees clients receive JSON even if headers were omitted in HTTP requests.                                                        |
| **`ApiExceptionRegistrar`** | Exception Handling     | Intercepts framework/system exceptions and renders formatted `ApiResponse::throw()` responses.                                        |

---

## 5. Design Principles

This package enforces established software engineering design patterns:

- **SOLID Principles**:
  - **Single Responsibility (SRP)**: Controllers handle transport; Services handle business rules; Repositories handle database persistence.
  - **Open/Closed (OCP)**: Data access behavior can be extended by swapping repository implementations without altering domain services.
  - **Liskov Substitution (LSP)**: Services interact exclusively with repository contracts (`{Name}Interface`), allowing any compliant storage implementation to be substituted.
  - **Interface Segregation (ISP)**: Interfaces provide focused data-access signatures tailored to domain needs.
  - **Dependency Inversion (DIP)**: High-level business services do not depend on low-level Eloquent models directly; both depend on repository abstractions.
- **Dependency Injection (DI)**: All dependencies are automatically injected via constructor parameters resolved by Laravel's IoC container.
- **Repository Pattern**: Abstracts data query logic from application business logic.
- **Layered Architecture**: Strict separation between Presentation (Controller), Domain (Service), and Persistence (Repository/Model).
- **Clean Code & DRY**: Standardizes CRUD logic and exception mapping across all application modules.
- **KISS (Keep It Simple, Stupid)**: Lightweight code scaffolding without complex runtime overhead or mandatory abstract base classes.

---

## 6. Installation

### Requirements

- **PHP**: `^8.2` or `^8.3`
- **Laravel**: `^10.0`, `^11.0`, `^12.0`, or `^13.0`

### Step 1: Install Package via Composer

Execute the following command in your Laravel project root:

```bash
composer require ixspx/module-generator
```

### Step 2: Verify Package Auto-Discovery

The package registers its Service Provider (`Ixspx\ModuleGenerator\Providers\ModuleGeneratorServiceProvider`) automatically via package manifest auto-discovery.

If auto-discovery is disabled in your `composer.json`, manually add the provider to `bootstrap/providers.php` (Laravel 11+) or `config/app.php` (Laravel 10):

```php
// bootstrap/providers.php
return [
    // Other Service Providers...
    Ixspx\ModuleGenerator\Providers\ModuleGeneratorServiceProvider::class,
];
```

---

## 7. Configuration

### Step 1: Install API Foundation

Run the installation command to generate API middleware, response helpers, and exception registrars:

```bash
php artisan make:api-install
```

_(Use `--force` to overwrite any existing foundation files)._

### Step 2: Register Middleware & Exception Handling (Laravel 11+)

Update `bootstrap/app.php` to append the `ForceJsonResponse` middleware and register global API exception handling:

```php
<?php

use App\Exceptions\ApiExceptionRegistrar;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function ($router) {
            Route::prefix('api/v1')
                ->group(base_path('routes/api.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Force all requests to return JSON responses
        $middleware->append(ForceJsonResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Register global API exception-to-JSON renderer
        ApiExceptionRegistrar::register($exceptions);
    })->create();
```

---

## 8. Available Artisan Commands

| Command                 | Signature                    | Description                                      | Key Generated Files                                                                       | Example Usage                          |
| :---------------------- | :--------------------------- | :----------------------------------------------- | :---------------------------------------------------------------------------------------- | :------------------------------------- |
| **Module Generator**    | `make:mod {name}`            | Generates a complete 6-layer module structure.   | Model, Interface, Repository, Service, Controller, Provider                               | `php artisan make:mod OrderPayment`    |
| **API Installer**       | `make:api-install {--force}` | Installs standard API foundation infrastructure. | `routes/api.php`, `ForceJsonResponse.php`, `ApiResponse.php`, `ApiExceptionRegistrar.php` | `php artisan make:api-install --force` |
| **API Response Helper** | `make:api-response`          | Scaffolds only the `ApiResponse` support class.  | `app/Support/ApiResponse.php`                                                             | `php artisan make:api-response`        |

---

## 9. Generated Files Breakdown

### 1. `make:mod {name}`

When running `php artisan make:mod OrderPayment`:

1. **`app/Models/OrderPayment/OrderPaymentModel.php`**: Eloquent model initialized with `$table = 'tbl_OrderPayment'` and `$fillable` array.
2. **`app/Repositories/Interfaces/OrderPayment/OrderPaymentInterface.php`**: Contract interface defining standard CRUD signatures.
3. **`app/Repositories/Repository/OrderPayment/OrderPaymentRepository.php`**: Concrete repository injecting `OrderPaymentModel` and implementing `OrderPaymentInterface`.
4. **`app/Services/OrderPayment/OrderPaymentService.php`**: Domain service injecting `OrderPaymentInterface`, wrapping mutations inside `DB::transaction()`, and providing business validation stubs (`validateCreate`, `validateUpdate`, `validateDelete`).
5. **`app/Http/Controllers/OrderPayment/OrderPaymentController.php`**: API controller injecting `OrderPaymentService` with action handlers (`HandlerGetAll`, `HandlerGetById`, `HandlerDeleteById`) using `ApiResponse::success()`.
6. **`app/Providers/OrderPaymentServiceProvider.php`**: Service Provider binding `OrderPaymentInterface` to `OrderPaymentRepository` in `$this->app->bind(...)`.

### 2. `make:api-install`

1. **`app/Support/ApiResponse.php`**: Standardized JSON envelope helper.
2. **`app/Http/Middleware/ForceJsonResponse.php`**: HTTP middleware enforcing JSON request/response headers.
3. **`app/Exceptions/ApiExceptionRegistrar.php`**: Centralized exception handler mapping framework/database errors to JSON.
4. **`routes/api.php`**: API routes template file.

---

## 10. API Standard

### Success Response Format (`ApiResponse::success`)

**HTTP Status**: `200 OK`, `201 Created`, etc.

```json
{
  "success": true,
  "responseCode": 200,
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 1,
      "order_number": "ORD-2026-001",
      "amount": 150.0,
      "status": "PAID"
    }
  ],
  "meta": {
    "count": 1
  },
  "links": null
}
```

### Error Response Format (`ApiResponse::throw`)

**HTTP Status**: `400 Bad Request`, `401 Unauthenticated`, `403 Forbidden`, `404 Not Found`, `422 Unprocessable Content`, `500 Internal Error`.

```json
{
  "success": false,
  "responseCode": 422,
  "message": "Validation error",
  "errors": {
    "amount": ["The amount field is required."]
  }
}
```

### Exception Mapping Reference

`ApiExceptionRegistrar` automatically translates exceptions into status codes and human-readable messages:

| Exception Class                                        | Translated Message                             | HTTP Status |
| :----------------------------------------------------- | :--------------------------------------------- | :---------- |
| `AuthenticationException`                              | `Unauthenticated`                              | **401**     |
| `AuthorizationException` / `AccessDeniedHttpException` | `Forbidden` / `Access denied`                  | **403**     |
| `NotFoundHttpException` / `ModelNotFoundException`     | `Route not found` / `Data not found`           | **404**     |
| `MethodNotAllowedHttpException`                        | `Method not allowed`                           | **405**     |
| `BadRequestHttpException`                              | `Bad request`                                  | **400**     |
| `ThrottleRequestsException`                            | `Too many requests`                            | **429**     |
| `ValidationException`                                  | `Validation error`                             | **422**     |
| `DomainException`                                      | `$e->getMessage()`                             | **422**     |
| `QueryException` / `PDOException`                      | `Database error` / `Database connection error` | **500**     |
| `Throwable` (Unhandled Fallback)                       | `Internal server error`                        | **500**     |

---

## 11. Usage Examples

### End-to-End Workflow: Scaffolding an `OrderPayment` Module

#### Step 1: Run Module Generator Command

```bash
php artisan make:mod OrderPayment
```

#### Step 2: Register Generated Module Provider

Add the generated provider to `bootstrap/providers.php`:

```php
// bootstrap/providers.php
return [
    // ...
    App\Providers\OrderPaymentServiceProvider::class,
];
```

#### Step 3: Define Routes in `routes/api.php`

```php
use App\Http\Controllers\OrderPayment\OrderPaymentController;
use Illuminate\Support\Facades\Route;

Route::controller(OrderPaymentController::class)->group(function () {
    Route::get('/payments', 'HandlerGetAll');
    Route::get('/payments/{id}', 'HandlerGetById');
    Route::delete('/payments/{id}', 'HandlerDeleteById');
});
```

#### Step 4: Customize Business Service (`app/Services/OrderPayment/OrderPaymentService.php`)

```php
namespace App\Services\OrderPayment;

use DomainException;
use Illuminate\Database\Eloquent\Model;

class OrderPaymentService
{
    // ... repository injected automatically via constructor

    protected function validateCreate(array $data): void
    {
        if (empty($data['amount']) || $data['amount'] <= 0) {
            throw new DomainException('Payment amount must be greater than zero.');
        }
    }
}
```

#### Step 5: Execute HTTP Requests

Send a GET request to `/api/v1/payments`:

```bash
curl -X GET http://localhost:8000/api/v1/payments \
  -H "Accept: application/json"
```

---

## 12. Package Structure

```
.
├── LICENSE                                    # MIT License
├── README.md                                  # Package Documentation
├── SECURITY.md                                # Security Reporting Policy
├── composer.json                              # Composer Configuration & Dependencies
├── src/
│   ├── Console/
│   │   └── Commands/
│   │       ├── ApiInstallCommand.php          # `make:api-install` implementation
│   │       ├── ApiResponseMakeCommand.php      # `make:api-response` implementation
│   │       └── ModuleGeneratorMakeCommand.php # `make:mod` implementation
│   ├── Providers/
│   │   └── ModuleGeneratorServiceProvider.php # Package Service Provider
│   └── Stubs/                                 # Generator Code Templates
│       ├── api-exception-registrar.stub
│       ├── api-middleware.stub
│       ├── api-response.stub
│       ├── api-route.stub
│       ├── controller.stub
│       ├── model.stub
│       ├── provider.stub
│       ├── repository.interface.stub
│       ├── repository.stub
│       └── service.stub
└── tests/                                     # Test Suite (Orchestra Testbench & PHPUnit)
    ├── Feature/
    │   ├── ApiInstallTest.php
    │   ├── ApiResponseTest.php
    │   └── ModuleGeneratorTest.php
    └── TestCase.php
```

---

## 13. Extensibility

### Extending Repositories

Add custom data queries to `{Name}Interface` and implement them in `{Name}Repository`:

```php
// Repositories/Interfaces/OrderPayment/OrderPaymentInterface.php
public function findByTransactionId(string $transactionId): ?Model;

// Repositories/Repository/OrderPayment/OrderPaymentRepository.php
public function findByTransactionId(string $transactionId): ?Model
{
    return $this->orderPaymentModel->where('transaction_id', $transactionId)->first();
}
```

### Adding Domain Exception Mappings

Extend `app/Exceptions/ApiExceptionRegistrar.php` to handle custom domain exceptions:

```php
use App\Exceptions\PaymentGatewayException;

// Inside ApiExceptionRegistrar::register match expression:
$e instanceof PaymentGatewayException => $e->getMessage(), // Message
$e instanceof PaymentGatewayException => 502,                // Status Code
```

---

## 14. Best Practices

1. **Keep Controllers Thin**: Controllers should only pass HTTP request inputs to services and return `ApiResponse::success()`.
2. **Encapsulate Mutations in Transactions**: Perform multi-table updates inside `OrderPaymentService` within `DB::transaction(...)`.
3. **Use Domain Exceptions**: Throw `DomainException` inside services when business rules fail. `ApiExceptionRegistrar` will automatically catch them and convert them to HTTP `422` JSON responses.
4. **Mock Repositories in Unit Tests**: Inject mock implementations of `{Name}Interface` when testing services to run tests without database overhead.

---

## 15. Limitations

Based on empirical code inspection of the current implementation:

1. **Hardcoded Table Prefix**: `model.stub` hardcodes `protected $table = 'tbl_{{name}}';`. The command string replacement for `{{table_name}}` in `ModuleGeneratorMakeCommand.php` does not match the stub token.
2. **Manual Module Provider Registration Required**: Generated module service providers (`App\Providers\{Name}ServiceProvider`) must be manually registered in `bootstrap/providers.php`.
3. **Opinionated Handler Naming**: Generated controllers scaffold method names like `HandlerGetAll`, `HandlerGetById`, and `HandlerDeleteById` instead of standard RESTful action names (`index`, `show`, `destroy`).
4. **No Stub Publishing Support**: Stubs are currently packaged internally within `src/Stubs/` and cannot be customized via standard Laravel `vendor:publish` commands.
5. **No Route Generator**: `make:mod` generates controllers and providers, but does not append routes directly to `routes/api.php`.

---

## 16. Future Improvements

- 🛠 **Custom Stub Publishing**: Support `php artisan vendor:publish --tag=module-generator-stubs` to allow developers to customize scaffolding stubs.
- 🚦 **Interactive CLI Scaffolding**: Add interactive prompts for table names, soft deletes, and custom controller actions.
- ⚡ **Auto-Registration of Module Providers**: Automatically register newly generated module providers in `bootstrap/providers.php`.
- 🛣 **Automated Route Scaffolding**: Optionally append RESTful route groups to `routes/api.php` upon module generation.
- 🧪 **Test Scaffolding**: Support generating feature and unit test stubs (`{Name}Test.php`) alongside module generation.

---

## 17. Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository on GitHub.
2. Create a feature branch (`git checkout -b feature/amazing-feature`).
3. Commit your changes (`git commit -m 'Add amazing feature'`).
4. Push to the branch (`git push origin feature/amazing-feature`).
5. Open a Pull Request.

Please ensure your code complies with PSR-12 formatting rules and includes passing PHPUnit tests.

---

## 18. License

This package is open-source software licensed under the **MIT License**. See the [LICENSE](LICENSE) file for more information.
