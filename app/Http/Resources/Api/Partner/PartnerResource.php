<?php

namespace App\Http\Resources\Api\Partner;

use App\Models\Geo\Regency;
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
        /** @var \App\Models\Partners\Partner $this */
        $data = [
            'hash' => $this->hash,
            'name' => $this->name,
            'code' => $this->code,
            'regency' => Regency::find($this->geo_regency_id),
            'type' => $this->type,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address' => $this->address,
            'transporter' => $this->transporters,
        ];


        return $data;
    }
}
