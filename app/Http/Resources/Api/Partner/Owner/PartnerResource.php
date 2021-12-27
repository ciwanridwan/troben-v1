<?php

namespace App\Http\Resources\Api\Partner\Owner;

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
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
        ];


        return $data;
    }
}
