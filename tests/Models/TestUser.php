<?php

namespace Vanthao03596\LaravelCursorPaginate\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class TestUser extends Model
{
    protected $guarded = [];

    public function posts()
    {
        return $this->hasMany(TestPost::class, 'user_id');
    }
}
