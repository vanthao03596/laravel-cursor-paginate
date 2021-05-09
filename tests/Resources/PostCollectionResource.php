<?php

namespace Vanthao03596\LaravelCursorPaginate\Tests\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Vanthao03596\LaravelCursorPaginate\HasCursorPaginate;

class PostCollectionResource extends ResourceCollection
{
    use HasCursorPaginate;

    public $collects = PostResource::class;

    public function toArray($request)
    {
        return ['data' => $this->collection];
    }
}
