<?php

namespace Vanthao03596\LaravelCursorPaginate\Query;

use Illuminate\Container\Container;
use Illuminate\Database\Query\Builder as Base;
use Illuminate\Pagination\Paginator;
use Vanthao03596\LaravelCursorPaginate\CursorPaginationException;
use Vanthao03596\LaravelCursorPaginate\CursorPaginator;

class Builder extends Base
{
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
        $cursor = $cursor ?: CursorPaginator::resolveCurrentCursor($cursorName);

        $orders = $this->ensureOrderForCursorPagination(! is_null($cursor) && $cursor->pointsToPreviousItems());

        $orderDirection = $orders->first()['direction'] ?? 'asc';

        $comparisonOperator = $orderDirection === 'asc' ? '>' : '<';

        $parameters = $orders->pluck('column')->toArray();

        if (! is_null($cursor)) {
            if (count($parameters) === 1) {
                $this->where($column = $parameters[0], $comparisonOperator, $cursor->parameter($column));
            } elseif (count($parameters) > 1) {
                $this->whereRowValues($parameters, $comparisonOperator, $cursor->parameters($parameters));
            }
        }

        $this->limit($perPage + 1);

        return $this->cursorPaginator($this->get($columns), $perPage, $cursor, [
            'path' => Paginator::resolveCurrentPath(),
            'cursorName' => $cursorName,
            'parameters' => $parameters,
        ]);
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

        $orderDirections = collect($this->orders)->pluck('direction')->unique();

        if ($orderDirections->count() > 1) {
            throw new CursorPaginationException('Only a single order by direction is supported when using cursor pagination.');
        }

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
