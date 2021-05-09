<?php

namespace Vanthao03596\LaravelCursorPaginate\Tests\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request)
    {
        return ['id' => $this->id, 'title' => $this->title, 'custom' => true];
    }

    public function withResponse($request, $response)
    {
        $response->header('X-Resource', 'True');
    }
}
