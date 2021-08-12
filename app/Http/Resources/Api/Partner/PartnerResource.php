<?php

namespace App\Http\Resources\Api\Partner;

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
            'type' => $this->type,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'transporter' => $this->transporters,
        ];


        return $data;
    }
}
