<?php

namespace App\Http\Resources\Api\Package;

use App\Http\Resources\Geo\DistrictResource;
use App\Http\Resources\Geo\RegencyResource;
use App\Http\Resources\Geo\SubDistrictResource;
use Illuminate\Http\Resources\Json\JsonResource;

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
        return array_merge(parent::toArray($request), [
            'origin_regency' => $this->resource->origin_regency ? RegencyResource::make($this->resource->origin_regency) : null,
            'destination_regency' => $this->resource->destination_regency ? RegencyResource::make($this->resource->destination_regency) : null,
            'destination_district' => $this->resource->destination_district ? DistrictResource::make($this->resource->destination_district) : null,
            'destination_sub_district' => SubDistrictResource::make($this->resource->destination_sub_district),
        ]);
    }
}
