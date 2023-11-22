<?php

namespace App\Http\Resources\Api\Delivery;

use App\Models\Code;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;

/**
 * Class WarehouseManifestResource.
 *
 * @property-read  \App\Models\Deliveries\Delivery $resource
 */
class WarehouseManifestResource extends DeliveryResource
{
    public function toArray($request): array
    {
        if (!$this->resource->relationLoaded('item_codes')) {
            $this->resource->load('item_codes');
        }
        if ($this->resource->relationLoaded('code')) {
            $this->resource->load(['code.scan_receipt_codes', 'code.scan_item_codes', 'code.scan_item_codes.codeable']);
        }

        $itemCodes = $this->resource->item_codes->groupBy(fn (Code $code) => $code->codeable->package_id);

        $this->resource->packages->each(
            fn (Package $package) => $package->setRelation('item_codes', $itemCodes->get($package->id))
        );

        // ADD TOTAL WEIGHT ACTUAL BEFORE CHARGED OR CHARGED
        $partner = $request->user()->partners->first();
        if ($partner->type === Partner::TYPE_TRANSPORTER) {
            $totalWeightMin = array();
            $itemCodes = $this->resource->item_codes;
            foreach ($itemCodes as $key => $value) {
                $totalWeight = $value->codeable->weight_borne_total;

                array_push($totalWeightMin, $totalWeight);
            }

            $this->resource->total_weight_min = array_sum($totalWeightMin);
        } else {
            if (isset($request->delivery_type) && $request->delivery_type[0] === Delivery::TYPE_DOORING) {
                $totalWeightMin = array();
                $itemCodes = $this->resource->item_codes;
                foreach ($itemCodes as $key => $value) {
                    $totalWeight = $value->codeable->weight_borne_total;

                    array_push($totalWeightMin, $totalWeight);
                }

                $this->resource->total_weight_min = array_sum($totalWeightMin);
            } elseif (isset($request->delivery_type) && $request->delivery_type[0] === Delivery::TYPE_TRANSIT) {
                switch (true) {
                        // ini keberangkatan manifest transit
                    case $request->arrival == 1:
                        $totalWeightMin = $this->resource->packages->sum('total_weight');

                        $this->resource->total_weight_min = $totalWeightMin;
                        break;
                        // ini kedatangan manifest transit
                    case $request->departure == 1:
                        $totalWeightMin = array();
                        $itemCodes = $this->resource->item_codes;
                        foreach ($itemCodes as $key => $value) {
                            $totalWeight = $value->codeable->weight_borne_total;

                            array_push($totalWeightMin, $totalWeight);
                        }

                        $this->resource->total_weight_min = array_sum($totalWeightMin);
                        break;
                    default:
                        $isDeparture = false;
                        $partner = $request->user()->partners->first();

                        if ($this->resource->origin_partner_id === $partner->id && $this->resource->partner_id !== $partner->id) {
                            $isDeparture = true;
                        }

                        // set total on weight on departure condition
                        if ($isDeparture) {
                            $totalWeightMin = array();
                            $itemCodes = $this->resource->item_codes;
                            foreach ($itemCodes as $key => $value) {
                                $totalWeight = $value->codeable->weight_borne_total;

                                array_push($totalWeightMin, $totalWeight);
                            }

                            $this->resource->total_weight_min = array_sum($totalWeightMin);
                        } else {
                            // set total weight on arrival condition
                            $totalWeightMin = $this->resource->packages->sum('total_weight');

                            $this->resource->total_weight_min = $totalWeightMin;
                        }
                        break;
                }
            } else {
                $this->resource->total_weight_min = $this->resource->packages->sum('total_weight');
            }
        }

        return $this->resource->toArray();

        // disable it
        return parent::toArray($request);
    }
}
