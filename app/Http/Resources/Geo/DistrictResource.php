<?php

namespace App\Http\Resources\Geo;

use Illuminate\Http\Resources\Json\JsonResource;

class DistrictResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var \App\Models\Geo\District $this */
        $data = [
            'id' => $this->id,
            'name' => $this->name,
        ];

        if ($this->relationLoaded('country')) {
            $data['country'] = CountryResource::make($this->country);
        }

        if ($this->relationLoaded('province')) {
            $data['province'] = ProvinceResource::make($this->province);
        }

        if ($this->relationLoaded('regency')) {
            $data['regency'] = RegencyResource::make($this->regency);
        }

        return $data;
    }
}
