<?php

namespace Vanthao03596\LaravelCursorPaginate\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = [];

    public function getIsPublishedAttribute()
    {
        return true;
    }
}
