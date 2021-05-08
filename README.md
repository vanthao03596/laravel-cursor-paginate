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

Similar to `simplePaginate`, `cursorPaginate` displays "Next" and "Previous" links in your application's UI. You may use the `cursorPaginate` method like so:
```php
$users = DB::table('users')->orderBy('id')->cursorPaginate(15);
```

Similarly, you may use the `cursorPaginate` method to cursor paginate Eloquent models:

```php
$users = User::where('votes', '>', 100)->orderBy('id')->cursorPaginate(15);
````

## Cursor Paginator Instance Methods

Each cursor paginator instance provides additional pagination information via the following methods:

Method  |  Description
-------  |  -----------
`$paginator->count()`  |  Get the number of items for the current page.
`$paginator->cursor()`  |  Get the current cursor instance.
`$paginator->getOptions()`  |  Get the paginator options.
`$paginator->hasPages()`  |  Determine if there are enough items to split into multiple pages.
`$paginator->hasMorePages()`  |  Determine if there are more items in the data store.
`$paginator->getCursorName()`  |  Get the query string variable used to store the cursor.
`$paginator->items()`  |  Get the items for the current page.
`$paginator->nextCursor()`  |  Get the cursor instance for the next set of items.
`$paginator->nextPageUrl()`  |  Get the URL for the next page.
`$paginator->onFirstPage()`  |  Determine if the paginator is on the first page.
`$paginator->perPage()`  |  The number of items to be shown per page.
`$paginator->previousCursor()`  |  Get the cursor instance for the previous set of items.
`$paginator->previousPageUrl()`  |  Get the URL for the previous page.
`$paginator->setCursorName()`  |  Set the query string variable used to store the cursor.
`$paginator->url($cursor)`  |  Get the URL for a given cursor instance.

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
