<?php

namespace App\Http\Resources\Geo\Web;

use App\Http\Resources\Geo\RegencyResource;
use Illuminate\Http\Resources\Json\JsonResource;

class KelurahanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var \App\Models\Geo\SubDistrict $this */
        $data = [
            'regency' => $this->regency,
            'district' => $this->district,
            'sub_district' => $this->sub_district,
            'zip_code' => $this->zip_code,
            'regency_id' => $this->regency_id,
            'district_id' => $this->district_id,
            'sub_district_id' => $this->id,
        ];

        if ($this->relationLoaded('regency')) {
            $data['regency'] = RegencyResource::make($this->regency);
        }

        if ($this->relationLoaded('district')) {
            $data['district'] = RegencyResource::make($this->district);
        }

        return $data;
    }
}
