<?php

namespace Vanthao03596\LaravelCursorPaginate\Tests;

use Illuminate\Support\Facades\DB;
use Vanthao03596\LaravelCursorPaginate\Cursor;
use Vanthao03596\LaravelCursorPaginate\Tests\Models\TestPost;
use Vanthao03596\LaravelCursorPaginate\Tests\Models\TestUser;

class EloquentCursorPaginateTest extends TestCase
{
    public function testCursorPaginationOnTopOfColumns()
    {
        for ($i = 1; $i <= 50; $i++) {
            TestPost::create([
                'title' => 'Title '.$i,
            ]);
        }

        $this->assertCount(15, TestPost::cursorPaginate(15, ['id', 'title']));
    }

    public function testPaginationWithDistinct()
    {
        for ($i = 1; $i <= 3; $i++) {
            TestPost::create(['title' => 'Hello world']);
            TestPost::create(['title' => 'Goodbye world']);
        }

        $query = TestPost::query()->distinct();

        $this->assertEquals(6, $query->get()->count());
        $this->assertEquals(6, $query->count());
        $this->assertCount(6, $query->cursorPaginate()->items());
    }

    public function testPaginationWithWhereClause()
    {
        for ($i = 1; $i <= 3; $i++) {
            TestPost::create(['title' => 'Hello world', 'user_id' => null]);
            TestPost::create(['title' => 'Goodbye world', 'user_id' => 2]);
        }

        $query = TestPost::query()->whereNull('user_id');

        $this->assertEquals(3, $query->get()->count());
        $this->assertEquals(3, $query->count());
        $this->assertCount(3, $query->cursorPaginate()->items());
    }

    public function testPaginationWithHasClause()
    {
        for ($i = 1; $i <= 3; $i++) {
            TestUser::create(['id' => $i]);
            TestPost::create(['title' => 'Hello world', 'user_id' => null]);
            TestPost::create(['title' => 'Goodbye world', 'user_id' => 2]);
            TestPost::create(['title' => 'Howdy', 'user_id' => 3]);
        }

        $query = TestUser::query()->has('posts');

        $this->assertEquals(2, $query->get()->count());
        $this->assertEquals(2, $query->count());
        $this->assertCount(2, $query->cursorPaginate()->items());
    }

    public function testPaginationWithWhereHasClause()
    {
        for ($i = 1; $i <= 3; $i++) {
            TestUser::create(['id' => $i]);
            TestPost::create(['title' => 'Hello world', 'user_id' => null]);
            TestPost::create(['title' => 'Goodbye world', 'user_id' => 2]);
            TestPost::create(['title' => 'Howdy', 'user_id' => 3]);
        }

        $query = TestUser::query()->whereHas('posts', function ($query) {
            $query->where('title', 'Howdy');
        });

        $this->assertEquals(1, $query->get()->count());
        $this->assertEquals(1, $query->count());
        $this->assertCount(1, $query->cursorPaginate()->items());
    }

    public function testPaginationWithWhereExistsClause()
    {
        for ($i = 1; $i <= 3; $i++) {
            TestUser::create(['id' => $i]);
            TestPost::create(['title' => 'Hello world', 'user_id' => null]);
            TestPost::create(['title' => 'Goodbye world', 'user_id' => 2]);
            TestPost::create(['title' => 'Howdy', 'user_id' => 3]);
        }

        $query = TestUser::query()->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('test_posts')
                ->whereColumn('test_posts.user_id', 'test_users.id');
        });

        $this->assertEquals(2, $query->get()->count());
        $this->assertEquals(2, $query->count());
        $this->assertCount(2, $query->cursorPaginate()->items());
    }

    public function testPaginationWithMultipleWhereClauses()
    {
        for ($i = 1; $i <= 4; $i++) {
            TestUser::create(['id' => $i]);
            TestPost::create(['title' => 'Hello world', 'user_id' => null]);
            TestPost::create(['title' => 'Goodbye world', 'user_id' => 2]);
            TestPost::create(['title' => 'Howdy', 'user_id' => 3]);
            TestPost::create(['title' => 'Howdy', 'user_id' => 4]);
        }

        $query = TestUser::query()->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('test_posts')
                ->whereColumn('test_posts.user_id', 'test_users.id');
        })->whereHas('posts', function ($query) {
            $query->where('title', 'Howdy');
        })->where('id', '<', 5)->orderBy('id');

        $clonedQuery = clone $query;
        $anotherQuery = clone $query;

        $this->assertEquals(2, $query->get()->count());
        $this->assertEquals(2, $query->count());
        $this->assertCount(2, $query->cursorPaginate()->items());
        $this->assertCount(1, $clonedQuery->cursorPaginate(1)->items());
        $this->assertCount(
            1,
            $anotherQuery->cursorPaginate(5, ['*'], 'cursor', new Cursor(['id' => 3]))
                ->items()
        );
    }

    public function testPaginationWithAliasedOrderBy()
    {
        for ($i = 1; $i <= 6; $i++) {
            TestUser::create(['id' => $i]);
        }

        $query = TestUser::query()->select('id as user_id')->orderBy('user_id');
        $clonedQuery = clone $query;
        $anotherQuery = clone $query;

        $this->assertEquals(6, $query->get()->count());
        $this->assertEquals(6, $query->count());
        $this->assertCount(6, $query->cursorPaginate()->items());
        $this->assertCount(3, $clonedQuery->cursorPaginate(3)->items());
        $this->assertCount(
            4,
            $anotherQuery->cursorPaginate(10, ['*'], 'cursor', new Cursor(['user_id' => 2]))
                ->items()
        );
    }

    public function testPaginationWithDistinctColumnsAndSelect()
    {
        for ($i = 1; $i <= 3; $i++) {
            TestPost::create(['title' => 'Hello world']);
            TestPost::create(['title' => 'Goodbye world']);
        }

        $query = TestPost::query()->distinct('title')->select('title');

        $this->assertEquals(2, $query->get()->count());
        $this->assertEquals(2, $query->count());
        $this->assertCount(2, $query->cursorPaginate()->items());
    }

    public function testPaginationWithDistinctColumnsAndSelectAndJoin()
    {
        for ($i = 1; $i <= 5; $i++) {
            $user = TestUser::create();
            for ($j = 1; $j <= 10; $j++) {
                TestPost::create([
                    'title' => 'Title '.$i,
                    'user_id' => $user->id,
                ]);
            }
        }

        $query = TestUser::query()->join('test_posts', 'test_posts.user_id', '=', 'test_users.id')
            ->distinct('test_users.id')->select('test_users.*');

        $this->assertEquals(5, $query->get()->count());
        $this->assertEquals(5, $query->count());
        $this->assertCount(5, $query->cursorPaginate()->items());
    }
}
