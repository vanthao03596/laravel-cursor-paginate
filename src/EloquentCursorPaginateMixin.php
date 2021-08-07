<?php

namespace Vanthao03596\LaravelCursorPaginate;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

class EloquentCursorPaginateMixin
{
    protected function paginateUsingCursor()
    {
        return function ($perPage, $columns = ['*'], $cursorName = 'cursor', $cursor = null) {
            if (! $cursor instanceof Cursor) {
                $cursor = is_string($cursor)
                    ? Cursor::fromEncoded($cursor)
                    : CursorPaginator::resolveCurrentCursor($cursorName, $cursor);
            }
    
            $orders = $this->ensureOrderForCursorPagination(! is_null($cursor) && $cursor->pointsToPreviousItems());
    
            if (! is_null($cursor)) {
                $addCursorConditions = function (self $builder, $previousColumn, $i) use (&$addCursorConditions, $cursor, $orders) {
                    if (! is_null($previousColumn)) {
                        $builder->where(
                            $this->getOriginalColumnNameForCursorPagination($this, $previousColumn),
                            '=',
                            $cursor->parameter($previousColumn)
                        );
                    }
    
                    $builder->where(function (self $builder) use ($addCursorConditions, $cursor, $orders, $i) {
                        ['column' => $column, 'direction' => $direction] = $orders[$i];
    
                        $builder->where(
                            $this->getOriginalColumnNameForCursorPagination($this, $column),
                            $direction === 'asc' ? '>' : '<',
                            $cursor->parameter($column)
                        );
    
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
        };
    }

    public function cursorPaginate()
    {
        return function ($perPage = null, $columns = ['*'], $cursorName = 'cursor', $cursor = null) {
            $perPage = $perPage ?: $this->model->getPerPage();

            return $this->paginateUsingCursor($perPage, $columns, $cursorName, $cursor);
        };
    }

    protected function ensureOrderForCursorPagination()
    {
        return function ($shouldReverse = false) {
            $orders = collect($this->query->orders);

            if ($orders->count() === 0) {
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

    protected function getOriginalColumnNameForCursorPagination()
    {
        return function ($builder, string $parameter) {
            $columns = $builder instanceof Builder ? $builder->getQuery()->columns : $builder->columns;
    
            if (! is_null($columns)) {
                foreach ($columns as $column) {
                    if (($position = stripos($column, ' as ')) !== false) {
                        $as = substr($column, $position, 4);
    
                        [$original, $alias] = explode($as, $column);
    
                        if ($parameter === $alias) {
                            return $original;
                        }
                    }
                }
            }
    
            return $parameter;
        };
    }
}
