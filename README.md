# Laravel Cursor Paginate for laravel 6,7

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vanthao03596/laravel-cursor-paginate.svg?style=flat-square)](https://packagist.org/packages/vanthao03596/laravel-cursor-paginate)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/vanthao03596/laravel-cursor-paginate/run-tests?label=tests)](https://github.com/vanthao03596/laravel-cursor-paginate/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/vanthao03596/laravel-cursor-paginate/Check%20&%20fix%20styling?label=code%20style)](https://github.com/vanthao03596/laravel-cursor-paginate/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/vanthao03596/laravel-cursor-paginate.svg?style=flat-square)](https://packagist.org/packages/vanthao03596/laravel-cursor-paginate)

## Installation

You can install the package via composer:

```bash
composer require vanthao03596/laravel-cursor-paginate
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Vanthao03596\LaravelCursorPaginate\LaravelCursorPaginateServiceProvider" --tag="laravel-cursor-paginate-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Vanthao03596\LaravelCursorPaginate\LaravelCursorPaginateServiceProvider" --tag="laravel-cursor-paginate-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php

```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [phamthao](https://github.com/vanthao03596)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
