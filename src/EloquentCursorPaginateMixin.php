<?php

namespace Vanthao03596\LaravelCursorPaginate;

use Illuminate\Container\Container;
use Illuminate\Pagination\Paginator;

class EloquentCursorPaginateMixin
{
    public function cursorPaginate()
    {
        return function ($perPage = null, $columns = ['*'], $cursorName = 'cursor', $cursor = null) {
            $cursor = $cursor ?: CursorPaginator::resolveCurrentCursor($cursorName);

            $perPage = $perPage ?: $this->model->getPerPage();

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

            $this->take($perPage + 1);

            return $this->cursorPaginator($this->get($columns), $perPage, $cursor, [
                'path' => Paginator::resolveCurrentPath(),
                'cursorName' => $cursorName,
                'parameters' => $parameters,
            ]);
        };
    }

    protected function ensureOrderForCursorPagination()
    {
        return function ($shouldReverse = false) {
            $orderDirections = collect($this->query->orders)->pluck('direction')->unique();

            if ($orderDirections->count() > 1) {
                throw new CursorPaginationException('Only a single order by direction is supported when using cursor pagination.');
            }

            if ($orderDirections->count() === 0) {
                $this->enforceOrderBy();
            }

            if ($shouldReverse) {
                $this->query->orders = collect($this->query->orders)->map(function ($order) {
                    $order['direction'] = $order['direction'] === 'asc' ? 'desc' : 'asc';

                    return $order;
                })->toArray();
            }

            return collect($this->query->orders);
        };
    }

    protected function cursorPaginator()
    {
        return function ($items, $perPage, $cursor, $options) {
            return Container::getInstance()->makeWith(CursorPaginator::class, compact(
                'items',
                'perPage',
                'cursor',
                'options'
            ));
        };
    }
}
