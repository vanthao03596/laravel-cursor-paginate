<?php

namespace Vanthao03596\LaravelCursorPaginate\Connections;

use Illuminate\Database\SQLiteConnection as Base;

class SQLiteConnection extends Base
{
    use CreatesQueryBuilder;
}
