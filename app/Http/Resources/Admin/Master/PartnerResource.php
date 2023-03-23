<?php

namespace App\Http\Resources\Admin\Master;

use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'hash' => $this->hash,
            'address' => $this->address,
            'geo_address' => $this->geo_address,
            'regency' => $this->regency ? $this->regency->only('id', 'name') : null,
            'district' => $this->district ? $this->district->only('id', 'name') : null,
            'sub_district' => $this->sub_district ? $this->sub_district->only('id', 'name') : null,
            'province' => $this->province ? $this->province->only('id', 'name') : null
        ];
    }
}
