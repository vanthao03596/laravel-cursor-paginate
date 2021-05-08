<?php


namespace Vanthao03596\LaravelCursorPaginate\Connections;

use Illuminate\Database\PostgresConnection as Base;

class PostgresConnection extends Base
{
    use CreatesQueryBuilder;
}
