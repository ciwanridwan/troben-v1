<?php

namespace App\Http\Resources\Api\Package;

use App\Models\Packages\Item;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ItemResource
 * @package App\Http\Resources\Api\Package
 *
 * @property  Item $resource
 */
class ItemResource extends JsonResource
{
    public function toArray($request)
    {
        if (! $this->resource->relationLoaded('code')) {
            $this->resource->load('code');
        }

        return parent::toArray($request);
    }
}
