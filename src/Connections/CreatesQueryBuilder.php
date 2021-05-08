<?php

namespace Vanthao03596\LaravelCursorPaginate\Connections;

use Vanthao03596\LaravelCursorPaginate\Query\Builder;

trait CreatesQueryBuilder
{
    /**
     * Get a new query builder instance.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return new Builder($this);
    }
}
