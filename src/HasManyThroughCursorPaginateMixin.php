<?php

namespace Vanthao03596\LaravelCursorPaginate;

class HasManyThroughCursorPaginateMixin
{
    public function cursorPaginate()
    {
        return function ($perPage = null, $columns = ['*'], $cursorName = 'cursor', $cursor = null) {
            $this->query->addSelect($this->shouldSelect($columns));
    
            return $this->query->cursorPaginate($perPage, $columns, $cursorName, $cursor);
        };
    }
}
