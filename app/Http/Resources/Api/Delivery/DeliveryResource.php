<?php

namespace App\Http\Resources\Api\Delivery;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class DeliveryResource.
 *
 * @property-read  \App\Models\Deliveries\Delivery $resource
 */
class DeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        if (!$this->resource->relationLoaded('code')) {
            $this->resource->load('code');
        }
        if ($this->resource->type === 'transit') {
            $this->resource->load('origin_partner');
        }

        $this->resource->append('as');

        return parent::toArray($request);
    }
}
