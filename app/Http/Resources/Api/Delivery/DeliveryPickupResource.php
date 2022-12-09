<?php

namespace App\Http\Resources\Api\Delivery;

use App\Models\Service;
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
                'packages.multiDestination',
                'packages.items',
                'packages.prices'
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

        $order_mode = true;

        $package =  $this->resource->packages->last()->toArray();
        $this->resource->unsetRelations('packages');

        $data = parent::toArray($request);
        if (isset($package)) {
            $data['package_multi'] = $multiDestination;
            $data['package'] = $package;
        }

        $shipping_method = 'Standart';
        $order_mode = true;
        if ($package['service_code'] == Service::TRAWLPACK_EXPRESS) {
            $shipping_method = 'Express';
        }
        if ($package['service_code'] == Service::TRAWLPACK_CUBIC) {
            $shipping_method = 'Cubic';
        }
        if (! is_null($multiDestination) && count($multiDestination) > 1) {
            $order_mode = false;
        }

        $data['shipping_method'] = $shipping_method;
        $data['order_mode'] = $order_mode ? 'Single' : 'Multiple';

        return $data;
    }
}
