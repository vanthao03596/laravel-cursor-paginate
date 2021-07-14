<?php

namespace Vanthao03596\LaravelCursorPaginate;

class BelongsToManyCursorPaginateMixin
{
    public function cursorPaginate()
    {
        return function($perPage = null, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
        {
            $this->query->addSelect($this->shouldSelect($columns));
    
            return tap($this->query->cursorPaginate($perPage, $columns, $cursorName, $cursor), function ($paginator) {
                $this->hydratePivotRelation($paginator->items());
            });
        };
    }
}