<?php

namespace App\Http\Resources\Api\HeadOffice;

use Illuminate\Http\Resources\Json\JsonResource;

class PartnersTransporterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'hash' => $this->hash,
            'name' => $this->name,
            'code' => $this->code,
            'address' => $this->address
        ];
    }
}
