<?php

namespace App\Http\Resources\Api\Delivery;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class DeliveryResource.
 *
 * @property-read  \App\Models\Deliveries\Delivery $resource
 */
class DeliveryPickupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        if (! $this->resource->relationLoaded('packages')) {
            $this->resource->load([
                'packages', 'packages.code',
                'packages.origin_regency',
                'packages.origin_district',
                'packages.origin_sub_district',
                'packages.destination_regency',
                'packages.destination_district',
                'packages.destination_sub_district',
            ]);
        }
        $packageMulti = $this->resource->packages()->get();
        $multiDestination = null;

        if ($packageMulti->isNotEmpty()) {
            $multiDestination = $packageMulti->map(function ($q) {
                $result = [
                    'code' => $q->code->content
                ];
                return $result;
            })->values()->toArray();
        }

        $package =  $this->resource->packages->first()->toArray();
        $this->resource->unsetRelations('packages');

        $data = parent::toArray($request);
        if (isset($package)) {
            $data['package_multi'] = $multiDestination;
            $data['package'] = $package;
        }
        return $data;
    }
}
