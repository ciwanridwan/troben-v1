<?php

namespace App\Http\Resources\Api\Partner\Owner;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ScheduleHarborOriginResource.
 */
class ScheduleHarborOriginResource extends JsonResource
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
            'origin_name' => $this->origin_name,
        ];
        return $data;
    }
}
