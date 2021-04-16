<?php

namespace App\Http\Resources\Api\Package;

use App\Http\Resources\Geo\RegencyResource;
use App\Http\Resources\Geo\DistrictResource;
use App\Http\Resources\Geo\SubDistrictResource;
use App\Models\Packages\Package;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PackageResource
 * @package App\Http\Resources\Api\Package
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

        $data = array_merge(parent::toArray($request), [
            'origin_regency' => $this->resource->origin_regency ? RegencyResource::make($this->resource->origin_regency) : null,
            'destination_regency' => $this->resource->destination_regency ? RegencyResource::make($this->resource->destination_regency) : null,
            'destination_district' => $this->resource->destination_district ? DistrictResource::make($this->resource->destination_district) : null,
            'destination_sub_district' => SubDistrictResource::make($this->resource->destination_sub_district),
        ]);

        if (isset($items)) {
            $data['items'] = $items;
        }

        return $data;
    }
}
