<?php

namespace Vanthao03596\LaravelCursorPaginate;

use Vanthao03596\LaravelCursorPaginate\Connectors\ConnectionFactory;
use Vanthao03596\LaravelPackageTools\Package;
use Vanthao03596\LaravelPackageTools\PackageServiceProvider;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class LaravelCursorPaginateServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-cursor-paginate')
            ->hasConfigFile();
    }

    public function packageBooted()
    {
        EloquentBuilder::mixin(new EloquentCursorPaginateMixin());
    }

    public  function bootingPackage()
    {
        CursorPaginator::currentCursorResolver(function ($cursorName = 'cursor') {
            return Cursor::fromEncoded($this->app['request']->input($cursorName));
        });

        CursorPaginator::queryStringResolver(function ($app) {
            return $app['request']->query();
        });

        $this->app->singleton('db.factory', function ($app) {
            return new ConnectionFactory($app);
        });
    }
}
