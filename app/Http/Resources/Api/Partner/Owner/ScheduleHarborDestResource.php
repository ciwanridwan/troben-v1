<?php

namespace App\Http\Resources\Api\Partner\Owner;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ScheduleHarborDestResource.
 */
class ScheduleHarborDestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'harbor_name' => $this->harbor_name,
            'destination_name' => $this->destination_name,
        ];
        return $data;
    }
}
