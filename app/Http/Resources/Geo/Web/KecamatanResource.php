<?php

namespace App\Http\Resources\Geo\Web;

use App\Http\Resources\Geo\CountryResource;
use App\Http\Resources\Geo\ProvinceResource;
use App\Http\Resources\Geo\RegencyResource;
use Illuminate\Http\Resources\Json\JsonResource;

class KecamatanResource extends JsonResource
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

        if ($this->relationLoaded('regency')) {
            $data['regency'] = RegencyResource::make($this->regency);
        }

        return $data;
    }
}
