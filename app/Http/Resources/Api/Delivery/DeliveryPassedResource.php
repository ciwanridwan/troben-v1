<?php

namespace App\Http\Resources\Api\Delivery;

use App\Models\HistoryReject;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class DeliveryResource.
 *
 * @property-read  HistoryReject $resource
 */
class DeliveryPassedResource extends JsonResource
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
                'packages.code',
                'packages.origin_regency',
                'packages.origin_district',
                'packages.origin_sub_district',
                'packages.destination_regency',
                'packages.destination_district',
                'packages.destination_sub_district',
            ]);
        }

        $package =  $this->resource->packages->first()->toArray();

        $data = parent::toArray($request);
        if (isset($package)) {
            $data['package'] = $package;
        }
        return $data;
    }
}
