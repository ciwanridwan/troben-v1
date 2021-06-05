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
        if (! $this->resource->relationLoaded('code')) {
            $this->resource->load(['code', 'code.scan_receipt_codes', 'code.scan_item_codes', 'code.scan_item_codes.codeable']);

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
        } else {
            $this->resource->load(['code.scan_receipt_codes', 'code.scan_item_codes', 'code.scan_item_codes.codeable']);

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
        if ($this->resource->type === 'transit') {
            if (! $this->resource->relationLoaded('partner')) {
                $this->resource->load('partner');
            }
            $this->resource->load('origin_partner');
        }

        $this->resource->append('as');
        $this->resource->load('item_codes');

        return parent::toArray($request);
    }
}
