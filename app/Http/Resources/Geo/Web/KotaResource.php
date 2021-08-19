<?php

namespace App\Http\Resources\Geo\Web;

use App\Http\Resources\Geo\ProvinceResource;
use Illuminate\Http\Resources\Json\JsonResource;

class KotaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var \App\Models\Geo\Regency $this */
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'capital' => $this->capital,
            'bsn_code' => $this->bsn_code,
        ];

        if ($this->relationLoaded('province')) {
            $data['province'] = ProvinceResource::make($this->province);
        }

        return $data;
    }
}
