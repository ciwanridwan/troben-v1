<?php

namespace App\Http\Resources;

use App\Http\Resources\Geo\RegencyResource;
use App\Http\Resources\Geo\DistrictResource;
use App\Http\Resources\Geo\ProvinceResource;
use App\Http\Resources\Geo\SubDistrictResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'hash' => $this->hash,
            'origin_province' => ProvinceResource::make($this->province),
            'origin_regency' => RegencyResource::make($this->regency),
            'origin_district' => $this->origin_district_id ? DistrictResource::make($this->district) : null,
            'origin_sub_district' => $this->origin_sub_district_id ? SubDistrictResource::make($this->subdistrict) : null,
            'destination' => SubDistrictResource::make($this->destination),
            'zip_code' => $this->zip_code,
            'tier_1' => $this->tier_1,
            'tier_2' => $this->tier_2,
            'tier_3' => $this->tier_3,
            'tier_4' => $this->tier_4,
            'tier_5' => $this->tier_5,
            'tier_6' => $this->tier_6,
            'tier_7' => $this->tier_7,
            'tier_8' => $this->tier_8,
            'tier_9' => $this->tier_9,
            'tier_10' => $this->tier_10,
            'service' => $this->service,
//            'service' => ServiceResource::make($this->service),
            'notes' => $this->notes,
        ];
    }
}
