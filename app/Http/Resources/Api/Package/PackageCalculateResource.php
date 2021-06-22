<?php

namespace App\Http\Resources\Api\Package;

use App\Actions\Pricing\PricingCalculator;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageCalculateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (!$this->resource->relationLoaded('items')) {
            $this->resource->load('items');
        }
        $items = $this->resource->items->toArray();

        $data = parent::toArray($request);

        $data['weight_borne_total'] = PricingCalculator::getTotalWeightBorne($items);


        $data['tier'] = 100;
        return $data;
    }
}
