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

## Architecture

This package generates Laravel modules following a **layered service–repository architecture**, which separates concerns clearly:

```
  ModuleName/
  ├─ Models/
  │ └─ ModuleName.php # Eloquent model
  ├─ Repositories/
  │ ├─ Interfaces/ModuleName/ModuleNameRepositoryInterface.php
  │ └─ Repository/ModuleName/ModuleNameRepository.php
  ├─ Services/
  │ ├─ ModuleName/ModuleNameService.php
  ├─ Http/Controllers/
  │ └─ ModuleName/ModuleNameController.php
  └─ Providers/
  └─ ModuleNameServiceProvider.php
```

### How it works

1. **Model**: Represents database table
2. **Repository + Interface**: Handles database operations
3. **Service**: Business logic layer
4. **Controller**: Handles HTTP requests and responses
5. **ServiceProvider**: Binds interfaces to implementations

This separation allows **clean, maintainable, and testable code**.

## Requirements

- PHP >= 8.1
- Laravel 9, 10, 11, 12

## Installation

Install via Composer:

```
composer require ixspx/module-generator
```

## Usage

- After installing the package:
  1. If your Laravel version supports **package discovery**, the service provider is automatically registered.
  2. Otherwise, add the following line to the `providers` array in `config/app.php`:

```
'providers' => [
    // Other service providers...
    Ixspx\ModuleGenerator\Providers\ModuleGeneratorServiceProvider::class,
],
```

- You are now ready to generate modules:
  php artisan make:mod {{nameModule}}
  Example: php artisan make:mod OrderPayment

## License

MIT © 2025 Saul Paulus (Ixspx)
