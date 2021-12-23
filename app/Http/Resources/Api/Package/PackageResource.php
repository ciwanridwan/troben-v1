<?php

namespace App\Http\Resources\Api\Package;

use App\Http\Resources\Admin\Master\PartnerResource;
use App\Models\Packages\Package;
use App\Http\Resources\Geo\RegencyResource;
use App\Http\Resources\Geo\DistrictResource;
use App\Http\Resources\Geo\SubDistrictResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PackageResource.
 *
 * @property  Package $resource
 */
class PackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (! $this->resource->relationLoaded('code')) {
            $this->resource->load('code');
        }

        if ($this->resource->relationLoaded('items')) {
            $items = ItemResource::collection($this->resource->items)->toArray($request);

            $this->resource->unsetRelation('items');
        }

        $this->resource->load('picked_up_by');
        if ($this->resource->relationLoaded('picked_up_by')) {
            $pickedUpPartner = $this->resource->picked_up_by->first();
            $this->resource->unsetRelation('picked_up_by');
        }


        $data = array_merge(parent::toArray($request), [
            'origin_regency' => $this->resource->origin_regency ? RegencyResource::make($this->resource->origin_regency) : null,
            'destination_regency' => $this->resource->destination_regency ? RegencyResource::make($this->resource->destination_regency) : null,
            'destination_district' => $this->resource->destination_district ? DistrictResource::make($this->resource->destination_district) : null,
            'destination_sub_district' => SubDistrictResource::make($this->resource->destination_sub_district),
        ]);

        if (isset($pickedUpPartner)) {
            $data['picked_up_by'] = $pickedUpPartner->partner ? PartnerResource::make($pickedUpPartner->partner) : null;
        }

        if (isset($items)) {
            $data['items'] = $items;
        }

        return $data;
    }
}
