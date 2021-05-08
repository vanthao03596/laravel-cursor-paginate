<?php

namespace Vanthao03596\LaravelCursorPaginate\Connections;

use Illuminate\Database\SqlServerConnection as Base;

class SqlServerConnection extends Base
{
    use CreatesQueryBuilder;
}
