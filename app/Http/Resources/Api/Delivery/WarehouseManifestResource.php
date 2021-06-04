<?php

namespace App\Http\Resources\Api\Delivery;

use App\Models\Code;
use App\Models\Packages\Package;

/**
 * Class WarehouseManifestResource.
 *
 * @property-read  \App\Models\Deliveries\Delivery $resource
 */
class WarehouseManifestResource extends DeliveryResource
{
    public function toArray($request): array
    {
        if (! $this->resource->relationLoaded('item_codes')) {
            $this->resource->load('item_codes');
        }
        if ($this->resource->relationLoaded('code')) {
            $this->resource->load(['code.scan_receipt_codes', 'code.scan_item_codes', 'code.scan_item_codes.codeable']);
        }
        $this->resource->makeHidden('item_codes');

        $itemCodes = $this->resource->item_codes->groupBy(fn (Code $code) => $code->codeable->package_id);

        $this->resource->packages->each(
            fn (Package $package) => $package->setRelation('item_codes', $itemCodes->get($package->id))
        );

        return parent::toArray($request);
    }
}
