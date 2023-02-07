<?php

namespace App\Http\Resources\Api\Order\Dashboard;

use App\Models\Packages\Package;
use App\Models\Service;
use Illuminate\Http\Resources\Json\JsonResource;

class ListOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
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
                'packages.prices',
                'packages.motoBikes'
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

        $package =  $this->resource->packages->last()->toArray();
        $this->resource->unsetRelations('packages');

        $data = parent::toArray($request);
        if (isset($package)) {
            $data['package_multi'] = $multiDestination;
            $data['package'] = $package;
        }

        $order_mode = true;
        if (! is_null($multiDestination) && count($multiDestination) > 1) {
            $order_mode = false;
        }

        $data['order_mode'] = $order_mode ? 'Single' : 'Multiple';

        $pickupPrice = $this->resource->packages->map(function ($q) {
            $pickupFee = $q->prices()->where('type', 'delivery')->where('description', 'pickup')->first();
            return $pickupFee;
        })->values()->toArray();

        $orderType = $this->resource->packages->map(function ($q) {
            $type = 'Item';
            if (!is_null($q->motoBikes)){
                $type = 'Bike';
            }

            return ['type' => $type];
        })->values()->toArray();

        $result = [
            'type' => $data['type'],
            'status' => $data['status'],
            'hash' => $data['hash'],
            'package' => [
                'hash' => $data['package']['hash'],
                'code' => $data['package']['code']['content'],
                'transporter_type' => $data['package']['transporter_type'],
                'pickup_address' => $data['package']['sender_address'],
                'created_at' => $data['package']['created_at'],
                'pickup_price' => $pickupPrice[0]['amount'],
                'order_type' => $orderType[0]['type'],
                'order_mode' => $data['order_mode']
            ]
        ];

        return $result;
        // return $data;
    }

    public function getStatus($package)
    {
        switch (true) {
            case $package->status === Package::STATUS_PENDING:
                break;

            default:
                # code...
                break;
        }
    }
}
