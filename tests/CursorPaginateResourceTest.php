<?php

namespace Vanthao03596\LaravelCursorPaginate\Tests;

use Illuminate\Support\Facades\Route;
use Vanthao03596\LaravelCursorPaginate\Cursor;
use Vanthao03596\LaravelCursorPaginate\CursorPaginator;
use Orchestra\Testbench\TestCase;
use Vanthao03596\LaravelCursorPaginate\Tests\Models\Post;
use Vanthao03596\LaravelCursorPaginate\Tests\Resources\PostCollectionResource;

class CursorPaginateResourceTest extends TestCase
{
    public function testCursorPaginatorReceiveLinks()
    {
        Route::get('/', function () {
            $paginator = new CursorPaginator(
                collect([new Post(['id' => 5, 'title' => 'Test Title']), new Post(['id' => 6, 'title' => 'Hello'])]),
                1, null, ['parameters' => ['id']]
            );

            return new PostCollectionResource($paginator);
        });

        $response = $this->withoutExceptionHandling()->get(
            '/', ['Accept' => 'application/json']
        );

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                [
                    'id' => 5,
                    'title' => 'Test Title',
                ],
            ],
            'links' => [
                'first' => null,
                'last' => null,
                'prev' => null,
                'next' => '/?cursor='.(new Cursor(['id' => 5]))->encode(),
            ],
            'meta' => [
                'path' => '/',
                'per_page' => 1,
            ],
        ]);
    }

    public function testCursorPaginatorResourceCanPreserveQueryParameters()
    {
        Route::get('/', function () {
            $collection = collect([new Post(['id' => 5, 'title' => 'Test Title']), new Post(['id' => 6, 'title' => 'Hello'])]);
            $paginator = new CursorPaginator(
                $collection, 1, null, ['parameters' => ['id']]
            );

            return PostCollectionResource::make($paginator)->preserveQuery();
        });

        $response = $this->withoutExceptionHandling()->get(
            '/?framework=laravel&author=Otwell', ['Accept' => 'application/json']
        );

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                [
                    'id' => 5,
                    'title' => 'Test Title',
                ],
            ],
            'links' => [
                'first' => null,
                'last' => null,
                'prev' => null,
                'next' => '/?framework=laravel&author=Otwell&cursor='.(new Cursor(['id' => 5]))->encode(),
            ],
            'meta' => [
                'path' => '/',
                'per_page' => 1,
            ],
        ]);
    }

    public function testCursorPaginatorResourceCanReceiveQueryParameters()
    {
        Route::get('/', function () {
            $collection = collect([new Post(['id' => 5, 'title' => 'Test Title']), new Post(['id' => 6, 'title' => 'Hello'])]);
            $paginator = new CursorPaginator(
                $collection, 1, null, ['parameters' => ['id']]
            );

            return PostCollectionResource::make($paginator)->withQuery(['author' => 'Taylor']);
        });

        $response = $this->withoutExceptionHandling()->get(
            '/?framework=laravel&author=Otwell', ['Accept' => 'application/json']
        );

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                [
                    'id' => 5,
                    'title' => 'Test Title',
                ],
            ],
            'links' => [
                'first' => null,
                'last' => null,
                'prev' => null,
                'next' => '/?author=Taylor&cursor='.(new Cursor(['id' => 5]))->encode(),
            ],
            'meta' => [
                'path' => '/',
                'per_page' => 1,
            ],
        ]);
    }
}
