<?php

namespace App\Http\Resources\Api\Package;

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
            'origin_sub_district' => SubDistrictResource::make($this->origin_sub_district),
            'destination_sub_district' => SubDistrictResource::make($this->destination_sub_district),
        ]);
    }
}
