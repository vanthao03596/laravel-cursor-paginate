<?php

namespace Vanthao03596\LaravelCursorPaginate\Tests;

use Illuminate\Database\Eloquent\Collection;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Vanthao03596\LaravelCursorPaginate\AbstractCursorPaginator;

class CursorPaginatorLoadMorphCountTest extends TestCase
{
    public function testCollectionLoadMorphCountCanChainOnThePaginator()
    {
        $relations = [
            'App\\User' => 'photos',
            'App\\Company' => ['employees', 'calendars'],
        ];

        $items = m::mock(Collection::class);
        $items->shouldReceive('loadMorphCount')->once()->with('parentable', $relations);

        $p = (new class extends AbstractCursorPaginator {
            //
        })->setCollection($items);

        $this->assertSame($p, $p->loadMorphCount('parentable', $relations));
    }
}
