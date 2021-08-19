<?php

namespace App\Http\Resources;

use App\Http\Resources\Geo\DistrictResource;
use App\Http\Resources\Geo\ProvinceResource;
use App\Http\Resources\Geo\RegencyResource;
use App\Http\Resources\Geo\SubDistrictResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TarifResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var \App\Models\Price $this  */
        return [
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
            'notes' => $this->notes,
        ];
    }
}
