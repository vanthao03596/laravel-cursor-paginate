<?php


namespace Vanthao03596\LaravelCursorPaginate\Tests;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Pagination\Paginator;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Vanthao03596\LaravelCursorPaginate\Cursor;
use Vanthao03596\LaravelCursorPaginate\CursorPaginator;
use Vanthao03596\LaravelCursorPaginate\Query\Builder;

class DatabaseQueryBuilderTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testCursorPaginate()
    {
        $perPage = 16;
        $columns = ['test'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['test' => 'bar']);
        $builder = $this->getMockQueryBuilder();
        $builder->from('foobar')->orderBy('test');
        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor='.$cursor->encode();

        $results = collect([['test' => 'foo'], ['test' => 'bar']]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results) {
            $this->assertEquals(
                'select * from "foobar" where ("test" > ?) order by "test" asc limit 17',
                $builder->toSql()
            );
            $this->assertEquals(['bar'], $builder->bindings['where']);

            return $results;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['test'],
        ]), $result);
    }

    public function testCursorPaginateMultipleOrderColumns()
    {
        $perPage = 16;
        $columns = ['test', 'another'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['test' => 'bar', 'another' => 'foo']);
        $builder = $this->getMockQueryBuilder();
        $builder->from('foobar')->orderBy('test')->orderBy('another');
        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor='.$cursor->encode();

        $results = collect([['test' => 'foo', 'another' => 1], ['test' => 'bar', 'another' => 2]]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results) {
            $this->assertEquals(
                'select * from "foobar" where ("test" > ? or ("test" = ? and ("another" > ?))) order by "test" asc, "another" asc limit 17',
                $builder->toSql()
            );
            $this->assertEquals(['bar', 'bar', 'foo'], $builder->bindings['where']);

            return $results;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['test', 'another'],
        ]), $result);
    }

    public function testCursorPaginateWithMixedOrders()
    {
        $perPage = 16;
        $columns = ['foo', 'bar', 'baz'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['foo' => 1, 'bar' => 2, 'baz' => 3]);
        $builder = $this->getMockQueryBuilder();
        $builder->from('foobar')->orderBy('foo')->orderByDesc('bar')->orderBy('baz');
        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor='.$cursor->encode();

        $results = collect([['foo' => 1, 'bar' => 2, 'baz' => 4], ['foo' => 1, 'bar' => 1, 'baz' => 1]]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results) {
            $this->assertEquals(
                'select * from "foobar" where ("foo" > ? or ("foo" = ? and ("bar" < ? or ("bar" = ? and ("baz" > ?))))) order by "foo" asc, "bar" desc, "baz" asc limit 17',
                $builder->toSql()
            );
            $this->assertEquals([1, 1, 2, 2, 3], $builder->bindings['where']);

            return $results;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['foo', 'bar', 'baz'],
        ]), $result);
    }

    public function testCursorPaginateWithDefaultArguments()
    {
        $perPage = 15;
        $cursorName = 'cursor';
        $cursor = new Cursor(['test' => 'bar']);
        $builder = $this->getMockQueryBuilder();
        $builder->from('foobar')->orderBy('test');
        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor='.$cursor->encode();

        $results = collect([['test' => 'foo'], ['test' => 'bar']]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results) {
            $this->assertEquals(
                'select * from "foobar" where ("test" > ?) order by "test" asc limit 16',
                $builder->toSql()
            );
            $this->assertEquals(['bar'], $builder->bindings['where']);

            return $results;
        });

        CursorPaginator::currentCursorResolver(function () use ($cursor) {
            return $cursor;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate();

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['test'],
        ]), $result);
    }

    public function testCursorPaginateWhenNoResults()
    {
        $perPage = 15;
        $cursorName = 'cursor';
        $builder = $this->getMockQueryBuilder()->orderBy('test');
        $path = 'http://foo.bar?cursor=3';

        $results = [];

        $builder->shouldReceive('get')->once()->andReturn($results);

        CursorPaginator::currentCursorResolver(function () {
            return null;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate();

        $this->assertEquals(new CursorPaginator($results, $perPage, null, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['test'],
        ]), $result);
    }

    public function testCursorPaginateWithSpecificColumns()
    {
        $perPage = 16;
        $columns = ['id', 'name'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['id' => 2]);
        $builder = $this->getMockQueryBuilder();
        $builder->from('foobar')->orderBy('id');
        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor=3';

        $results = collect([['id' => 3, 'name' => 'Taylor'], ['id' => 5, 'name' => 'Mohamed']]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results) {
            $this->assertEquals(
                'select * from "foobar" where ("id" > ?) order by "id" asc limit 17',
                $builder->toSql()
            );
            $this->assertEquals([2], $builder->bindings['where']);

            return $results;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['id'],
        ]), $result);
    }

    /**
     * @return \Mockery\MockInterface|\Illuminate\Database\Query\Builder
     */
    protected function getMockQueryBuilder()
    {
        return m::mock(Builder::class, [
            m::mock(ConnectionInterface::class),
            new Grammar,
            m::mock(Processor::class),
        ])->makePartial();
    }
}
