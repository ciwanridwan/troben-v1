<?php

namespace App\Http\Resources\Geo;

use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var \App\Models\Geo\Country $this */
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'alpha2' => $this->alpha2,
            'alpha3' => $this->alpha3,
            'numeric' => $this->numeric,
            'phone_prefix' => $this->phone_prefix,
        ];

        if ($this->relationLoaded('provinces')) {
            $data['provinces'] = ProvinceResource::collection($this->provinces);
        }

        return $data;
    }
}
