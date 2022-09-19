<?php

namespace App\Http\Resources\Api\Delivery;

use App\Models\Deliveries\Delivery;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

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
        if (! $this->resource->relationLoaded('code.scan_item_codes.codeable')) {
            $this->resource->load(['code.scan_receipt_codes', 'code.scan_item_codes.codeable']);

            if ($this->resource->code != null) {
                $this->resource->code->scan_receipt_codes = $this->resource->code->scan_receipt_codes->map(function ($item) {
                    $item->status = $item->pivot->status;
                    $item->updated_at = $item->pivot->created_at;
                    return $item;
                });

                $this->resource->code->scan_item_codes = $this->resource->code->scan_item_codes->map(function ($item) {
                    $item->status = $item->pivot->status;
                    $item->updated_at = $item->pivot->created_at;
                    return $item;
                });
            }
            // $this->resource->code->scan_item_codes->makeHidden(['pivot_code_logable_id', 'pivot_code_logable_type', 'pivot_code_id']);
        }

        if ($this->resource->type === Delivery::TYPE_TRANSIT) {
            if (! $this->resource->relationLoaded('partner')) {
                $this->resource->load('partner');
            }
            $this->resource->load('origin_partner');
        }

        if ($this->resource->type === Delivery::TYPE_PICKUP) {
            if (! $this->resource->relationLoaded('partner')) {
                $this->resource->load('partner');
            }
            $this->resource->load('origin_partner');
        }

        if ($this->resource->type === Delivery::TYPE_DOORING) {
            if (! $this->resource->relationLoaded('partner')) {
                $this->resource->load('partner');
            }
            $this->resource->load('origin_partner');
        }

        $this->resource->load('packages.motoBikes');

        if ($this->resource->relationLoaded('packages')) {
            //     $packages = PackageResource::collection($this->resource->packages->load('items'));
            //     $this->resource->unsetRelation('packages');
            foreach ($this->resource->packages as $key => $package) {
                $this->resource->packages[$key]->customer_hash = (string) $package->customer_id;
            }
        }

        $this->resource->append('as');
        if (! $this->resource->relationLoaded('item_codes')) {
            $this->resource->load('item_codes');
        }
        $this->resource->item_codes = $this->resource->item_codes->map(function ($item) {
            $item->status = $item->pivot->status;
            $item->updated_at = $item->pivot->created_at;
            return $item;
        });

        $this->resource->load('driver');

        $data = parent::toArray($request);
        // if (isset($packages)) {
        //     $data['packages'] = $packages;
        // }
        if ($this->resource->relationLoaded('partner_performance')) {
            $data = Arr::except($data, 'partner_performance');
            if ($this->resource->partner_performance) {
                $dataPerformance = [
                'level' => $this->resource->partner_performance->level,
                'deadline_time' => $this->resource->partner_performance->deadline
            ];
            } else {
                $dataPerformance = [
                'level' => null,
                'deadline_time' => null
            ];
            }
            $this->resource->unsetRelation('partner_performance');
            $data = array_merge($data, $dataPerformance);
        }

        return $data;
    }
}
