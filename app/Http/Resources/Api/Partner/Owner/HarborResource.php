<?php

namespace App\Http\Resources\Api\Partner\Owner;

use Illuminate\Http\Resources\Json\JsonResource;

class HarborResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

    public function toArray($request)
    {
        /**
         * @var Harbor
         */

        $data = [
            'id' => $this->id,
            'origin_name' => $this->origin_name,
            'destination_name' => $this->destination_name,
        ];

        return $data;
    }
}
