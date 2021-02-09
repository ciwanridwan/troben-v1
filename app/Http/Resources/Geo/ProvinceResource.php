<?php

namespace App\Http\Resources\Geo;

use Illuminate\Http\Resources\Json\JsonResource;

class ProvinceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var \App\Models\Geo\Province $this */
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'iso_code' => $this->iso_code,
        ];

        if ($this->relationLoaded('country')) {
            $data['country'] = CountryResource::make($this->country);
        }

        return $data;
    }
}
