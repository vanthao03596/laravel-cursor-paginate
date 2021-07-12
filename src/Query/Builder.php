<?php

namespace Vanthao03596\LaravelCursorPaginate\Query;

use Illuminate\Container\Container;
use Illuminate\Database\Query\Builder as Base;
use Illuminate\Pagination\Paginator;
use Vanthao03596\LaravelCursorPaginate\CursorPaginator;

class Builder extends Base
{
    /**
     * Paginate the given query using a cursor paginator.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @param  string  $cursorName
     * @param  string|null  $cursor
     * @return \Illuminate\Contracts\Pagination\CursorPaginator
     */
    protected function paginateUsingCursor($perPage, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
    {
        $cursor = $cursor ?: CursorPaginator::resolveCurrentCursor($cursorName);

        $orders = $this->ensureOrderForCursorPagination(! is_null($cursor) && $cursor->pointsToPreviousItems());

        if (! is_null($cursor)) {
            $addCursorConditions = function (self $builder, $previousColumn, $i) use (&$addCursorConditions, $cursor, $orders) {
                if (! is_null($previousColumn)) {
                    $builder->where($previousColumn, '=', $cursor->parameter($previousColumn));
                }

                $builder->where(function (self $builder) use ($addCursorConditions, $cursor, $orders, $i) {
                    ['column' => $column, 'direction' => $direction] = $orders[$i];

                    $builder->where($column, $direction === 'asc' ? '>' : '<', $cursor->parameter($column));

                    if ($i < $orders->count() - 1) {
                        $builder->orWhere(function (self $builder) use ($addCursorConditions, $column, $i) {
                            $addCursorConditions($builder, $column, $i + 1);
                        });
                    }
                });
            };

            $addCursorConditions($this, null, 0);
        }

        $this->limit($perPage + 1);

        return $this->cursorPaginator($this->get($columns), $perPage, $cursor, [
            'path' => Paginator::resolveCurrentPath(),
            'cursorName' => $cursorName,
            'parameters' => $orders->pluck('column')->toArray(),
        ]);
    }

    /**
     * Get a paginator only supporting simple next and previous links.
     *
     * This is more efficient on larger data-sets, etc.
     *
     * @param  int|null  $perPage
     * @param  array  $columns
     * @param  string  $cursorName
     * @param  string|null  $cursor
     * @return \Illuminate\Contracts\Pagination\Paginator
     * @throws \Vanthao03596\LaravelCursorPaginate\CursorPaginationException
     */
    public function cursorPaginate($perPage = 15, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
    {
        return $this->paginateUsingCursor($perPage, $columns, $cursorName, $cursor);
    }

    /**
     * Ensure the proper order by required for cursor pagination.
     *
     * @param  bool  $shouldReverse
     * @return \Illuminate\Support\Collection
     * @throws \Vanthao03596\LaravelCursorPaginate\CursorPaginationException
     */
    protected function ensureOrderForCursorPagination($shouldReverse = false)
    {
        $this->enforceOrderBy();

        if ($shouldReverse) {
            $this->orders = collect($this->orders)->map(function ($order) {
                $order['direction'] = $order['direction'] === 'asc' ? 'desc' : 'asc';

                return $order;
            })->toArray();
        }

        return collect($this->orders);
    }

    /**
     * Create a new cursor paginator instance.
     *
     * @param  \Illuminate\Support\Collection  $items
     * @param  int  $perPage
     * @param  \Vanthao03596\LaravelCursorPaginate\Cursor  $cursor
     * @param  array  $options
     * @return \Illuminate\Pagination\Paginator
     */
    protected function cursorPaginator($items, $perPage, $cursor, $options)
    {
        return Container::getInstance()->makeWith(CursorPaginator::class, compact(
            'items',
            'perPage',
            'cursor',
            'options'
        ));
    }
}
