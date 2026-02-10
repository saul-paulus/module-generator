# Module-Generator

[![PHP Version](https://img.shields.io/badge/PHP-%3E=8.1-brightgreen)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

### _Module-Generator in Laravel_

Laravel Module Generator with layered architecture: Model, Repository, Service, Controller, Provider.
Generate a complete module structure in Laravel, including:

- Model
- Repositories/Repository + Interface
- Service
- Controller
- Service Provider

## 🏗 Architecture Overview

Generated modules follow a layered service–repository architecture:

```
ModuleName/
├── Models/
│   └── ModuleName.php
│
├── Repositories/
│   ├── Interfaces/
│   │   └── ModuleName/
│   │       └── ModuleNameRepositoryInterface.php
│   └── Repository/
│       └── ModuleName/
│           └── ModuleNameRepository.php
│
├── Services/
│   └── ModuleName/
│       └── ModuleNameService.php
│
├── Http/
│   └── Controllers/
│       └── ModuleName/
│           └── ModuleNameController.php
│
└── Providers/
    └── ModuleNameServiceProvider.php
```

## 🧠 Architectural Principles

Each layer has a single responsibility:

1. Model
   Represents the database table (Eloquent ORM).

2. Repository + Interface
   Encapsulates all database access logic.

3. Service
   Contains business rules and application logic.

4. Controller
   Handles HTTP requests and responses.

5. Service Provider
   Binds interfaces to concrete implementations.

This approach results in testable, decoupled, and scalable code.

## Requirements

- PHP >= 8.1
- Laravel 9, 10, 11, 12

## Installation

Install via Composer:

```
composer require ixspx/module-generator
```

## ⚙ Manual Provider Registration (Optional)

If package discovery is disabled, register the provider manually in bootstrap/providers.php:

```
'providers' => [
    // Other service providers...
    Ixspx\ModuleGenerator\Providers\ModuleGeneratorServiceProvider::class,
]
```

## 🛠 Usage

Generate a Module

```
  php artisan make:mod {{nameModule}}
```

Example:

```
php artisan make:mod OrderPayment
```

This will generate the full module structure using the layered architecture described above.

Generate API Response Helper

```
php artisan make:api-response
```

## 📄 License

This project is open-source software licensed under the MIT License.
See the see [LICENSE](LICENSE)
