<?php

namespace App\Http\Resources\Api\Delivery;

use App\Http\Resources\Api\Package\PackageResource;
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
        if (!$this->resource->relationLoaded('code.scan_item_codes.codeable')) {
            $this->resource->load(['code.scan_receipt_codes', 'code.scan_item_codes.codeable']);

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
            // $this->resource->code->scan_item_codes->makeHidden(['pivot_code_logable_id', 'pivot_code_logable_type', 'pivot_code_id']);
        }

        if ($this->resource->type === 'transit') {
            if (!$this->resource->relationLoaded('partner')) {
                $this->resource->load('partner');
            }
            $this->resource->load('origin_partner');
        }

        // if ($this->resource->relationLoaded('packages')) {
        //     $packages = PackageResource::collection($this->resource->packages->load('items'));
        //     $this->resource->unsetRelation('packages');
        // }

        $this->resource->append('as');
        $this->resource->load('item_codes');
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

        return $data;
    }
}
