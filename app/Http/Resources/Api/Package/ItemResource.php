<?php

namespace App\Http\Resources\Api\Package;

use App\Models\Packages\Item;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ItemResource.
 *
 * @property  Item $resource
 */
class ItemResource extends JsonResource
{
    public function toArray($request)
    {
        if (! $this->resource->relationLoaded('codes')) {
            $this->resource->load('codes');
        }

        return parent::toArray($request);
    }
}
