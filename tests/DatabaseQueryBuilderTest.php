<?php


namespace Vanthao03596\LaravelCursorPaginate\Tests;
use Illuminate\Pagination\Paginator;
use PHPUnit\Framework\TestCase;
use Vanthao03596\LaravelCursorPaginate\Cursor;
use Vanthao03596\LaravelCursorPaginate\CursorPaginator;
use Mockery as m;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Query\Grammars\Grammar;
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
        $builder = $this->getMockQueryBuilder()->orderBy('test');
        $path = 'http://foo.bar?cursor='.$cursor->encode();

        $results = collect([['test' => 'foo'], ['test' => 'bar']]);

        $builder->shouldReceive('where')->with('test', '>', 'bar')->once()->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn($results);

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
        $columns = ['test'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['test' => 'bar', 'another' => 'foo']);
        $builder = $this->getMockQueryBuilder()->orderBy('test')->orderBy('another');
        $path = 'http://foo.bar?cursor='.$cursor->encode();

        $results = collect([['test' => 'foo'], ['test' => 'bar']]);

        $builder->shouldReceive('whereRowValues')->with(['test', 'another'], '>', ['bar', 'foo'])->once()->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn($results);

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

    public function testCursorPaginateWithDefaultArguments()
    {
        $perPage = 15;
        $cursorName = 'cursor';
        $cursor = new Cursor(['test' => 'bar']);
        $builder = $this->getMockQueryBuilder()->orderBy('test');
        $path = 'http://foo.bar?cursor='.$cursor->encode();

        $results = collect([['test' => 'foo'], ['test' => 'bar']]);

        $builder->shouldReceive('get')->once()->andReturn($results);

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
        $builder = $this->getMockQueryBuilder()->orderBy('id');
        $path = 'http://foo.bar?cursor=3';

        $results = collect([['id' => 3, 'name' => 'Taylor'], ['id' => 5, 'name' => 'Mohamed']]);

        $builder->shouldReceive('get')->once()->andReturn($results);

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
     * @return m\MockInterface
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
