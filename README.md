# Laravel Module Generator & API Foundation

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
- [13. Extensibility & Stub Publishing](#13-extensibility--stub-publishing)
- [14. Best Practices](#14-best-practices)
- [15. Limitations & Solutions](#15-limitations--solutions)
- [16. Future Improvements](#16-future-improvements)
- [17. Contributing](#17-contributing)
- [18. License](#18-license)

---

## 1. Project Overview

### What is this package?
`ixspx/module-generator` is a dual-purpose Laravel package:
1. **Module Scaffolder (`make:mod`)**: Scaffolds modular domain layers adhering to a Service-Repository pattern, providing separation of database concerns, domain business logic, HTTP delivery, and dependency injection.
2. **API Infrastructure Starter (`make:api-install` & `make:api-response`)**: Provisions a unified JSON response envelope, middleware to force JSON headers, and a centralized exception handler to translate framework, validation, domain, and database exceptions into consistent JSON payloads.

---

## 2. Key Features

- 🏗 **Full-Stack Module Generator**: Scaffolds Model, Interface, Concrete Repository, Service, Controller, and Service Provider via `php artisan make:mod {Name}`.
- ⚡ **Auto-Registration of Providers & Routes**: Automatically registers scaffolded providers in `bootstrap/providers.php` and appends RESTful routes to `routes/api.php`.
- 🔌 **Dynamic Interface-to-Repository Auto-Binding**: Features fallback container auto-binding so interfaces resolve to repositories even without dedicated per-module providers.
- 🎨 **Publishable Stubs & Configuration**: Fully customizable code templates via `php artisan vendor:publish --tag=module-generator-stubs` and `module-generator-config`.
- 🔒 **Centralized Exception Handling**: Intercepts database (`PDOException`, `QueryException`), routing (`NotFoundHttpException`), auth, validation, and domain exceptions into predictable JSON responses.
- 📦 **Unified JSON Response Envelopes**: `ApiResponse` helper guarantees standard `success`, `responseCode`, `message`, `data`, `meta`, `links`, and `errors` structural contracts.
- 🌐 **JSON Enforcement Middleware**: `ForceJsonResponse` appends `Accept: application/json` headers to incoming HTTP requests and safely converts non-JSON outputs.

---

## 3. Architecture Overview

### Generated Module Layering

```
[ HTTP Request ]
       │
       ▼
┌──────────────┐
│  Controller  │  (Http/Controllers/{Module}/{Module}Controller.php)
└──────┬───────┘  • RESTful Delivery Layer: Calls Service, returns ApiResponse envelope
       │
       ▼
┌──────────────┐
│   Service    │  (Services/{Module}/{ModuleService}.php)
└──────┬───────┘  • Business Domain Layer: Enforces rules, handles DB transactions
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

---

## 4. Generated Structure

```
app/
├── Exceptions/
│   └── ApiExceptionRegistrar.php              # Global Exception Handler Registrar
├── Http/
│   ├── Controllers/
│   │   └── {ModuleName}/
│   │       └── {ModuleName}Controller.php     # RESTful Controller
│   └── Middleware/
│       └── ForceJsonResponse.php              # Request/Response Header Enforcer
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
    └── ApiResponse.php                        # Standardized Response Builder
routes/
└── api.php                                    # API Route Definitions
```

---

## 5. Design Principles

- **SOLID Principles**: Single Responsibility (SRP), Open/Closed (OCP), Liskov Substitution (LSP), Interface Segregation (ISP), and Dependency Inversion (DIP).
- **Dependency Injection (DI)**: Injects interfaces resolved automatically by Laravel's IoC container.
- **Repository Pattern**: Abstracts database queries from domain services.
- **Layered Architecture**: Strict separation of HTTP, Business, and Persistence layers.

---

## 6. Installation

```bash
composer require ixspx/module-generator
```

### Publish Configuration & Stubs (Optional)

```bash
# Publish configuration file
php artisan vendor:publish --tag=module-generator-config

# Publish template stubs for custom modifications
php artisan vendor:publish --tag=module-generator-stubs
```

---

## 7. Configuration

Update `bootstrap/app.php` (Laravel 11+):

```php
use App\Exceptions\ApiExceptionRegistrar;
use App\Http\Middleware\ForceJsonResponse;

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
        $middleware->append(ForceJsonResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        ApiExceptionRegistrar::register($exceptions);
    })->create();
```

---

## 8. Available Artisan Commands

| Command | Signature | Description | Key Options | Example Usage |
| :--- | :--- | :--- | :--- | :--- |
| **Module Generator** | `make:mod {name}` | Scaffolds complete 6-layer module structure. | `--table=`, `--table-prefix=`, `--style=`, `--no-provider`, `--no-route`, `--force` | `php artisan make:mod OrderPayment` |
| **API Installer** | `make:api-install` | Installs standard API foundation infrastructure. | `--force` | `php artisan make:api-install --force` |
| **API Response Helper** | `make:api-response` | Scaffolds `ApiResponse` support class. | None | `php artisan make:api-response` |

---

## 9. Generated Files Breakdown

Running `php artisan make:mod OrderPayment`:
1. `app/Models/OrderPayment/OrderPaymentModel.php` (Model mapped to `order_payments` table).
2. `app/Repositories/Interfaces/OrderPayment/OrderPaymentInterface.php` (Repository Contract).
3. `app/Repositories/Repository/OrderPayment/OrderPaymentRepository.php` (Eloquent Implementation).
4. `app/Services/OrderPayment/OrderPaymentService.php` (Transactional Business Service).
5. `app/Http/Controllers/OrderPayment/OrderPaymentController.php` (RESTful API Controller with `index`, `show`, `store`, `update`, `destroy`).
6. `app/Providers/OrderPaymentServiceProvider.php` (Service Provider automatically registered in `bootstrap/providers.php`).
7. Automatic Route Insertion in `routes/api.php`: `Route::apiResource('order-payments', OrderPaymentController::class);`.

---

## 10. API Standard

### Success Response (`ApiResponse::success`)

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

### Error Response (`ApiResponse::throw`)

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

---

## 11. Usage Examples

### Custom Table Prefix & RESTful Controller

```bash
php artisan make:mod OrderPayment --table=payments --table-prefix=tbl_
```

### Legacy Handler Naming Style

```bash
php artisan make:mod OrderPayment --style=handler
```

---

## 12. Package Structure

```
.
├── config/
│   └── module-generator.php                   # Package Configuration
├── src/
│   ├── Console/
│   │   └── Commands/
│   │       ├── ApiInstallCommand.php
│   │       ├── ApiResponseMakeCommand.php
│   │       └── ModuleGeneratorMakeCommand.php
│   ├── Providers/
│   │   └── ModuleGeneratorServiceProvider.php
│   ├── Support/
│   │   ├── ProviderRegistrar.php
│   │   └── RouteRegistrar.php
│   ├── Traits/
│   │   └── ResolvesStubs.php
│   └── Stubs/
└── tests/
```

---

## 13. Extensibility & Stub Publishing

Publish stubs to customize your team's code templates:

```bash
php artisan vendor:publish --tag=module-generator-stubs
```

Modifications made to `.stub` files in `stubs/module-generator/` will automatically override internal package stubs.

---

## 14. Best Practices

1. **Keep Controllers Thin**: Controllers delegate business execution to Services.
2. **Encapsulate Mutations in Transactions**: Multi-table operations belong inside `Service` class transactions.
3. **Use Domain Exceptions**: Throw `DomainException` inside services to trigger automated `422` JSON responses.

---

## 15. Limitations & Solutions

- **Table Prefix Flexibility**: Solved via `config/module-generator.php` and `--table-prefix=` CLI option.
- **Provider Registration**: Solved via automatic `ProviderRegistrar` insertion and container fallback auto-binding.
- **RESTful Naming**: Solved via standard RESTful controller stubs with `--style=handler` legacy support.
- **Stub Publishing**: Solved via `php artisan vendor:publish --tag=module-generator-stubs`.
- **Route Generation**: Solved via automatic `RouteRegistrar` appending.

---

## 16. Future Improvements

- 🛠 Form Request Scaffolding (`--requests`).
- 📊 API Resource Scaffolding (`--resource`).
- 📦 DTO Scaffolding (`--dto`).
- 🧪 Automated Feature & Unit Test Scaffolding (`--test`).

---

## 17. Contributing

Contributions are welcome via GitHub Pull Requests. Ensure code complies with PSR-12 and passes unit tests.

---

## 18. License

Licensed under the **MIT License**. See [LICENSE](LICENSE) for details.
