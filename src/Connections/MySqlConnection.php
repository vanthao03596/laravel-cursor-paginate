<?php

namespace Vanthao03596\LaravelCursorPaginate\Connections;

use Illuminate\Database\MySqlConnection as Base;

class MySqlConnection extends Base
{
    use CreatesQueryBuilder;
}
