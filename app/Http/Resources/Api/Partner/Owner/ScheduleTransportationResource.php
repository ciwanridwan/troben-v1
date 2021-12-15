<?php

namespace App\Http\Resources\Api\Partner\Owner;

use App\Models\Geo\Regency;
use App\Models\Partners\ScheduleTransportation;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleTransportationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        /** @var ScheduleTransportation $this */
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'vehicle_type' => $this->vehicle_type,
            'origin_regency' => Regency::find($this->origin_regency_id),
            'destination_regency' => Regency::find($this->destination_regency_id),
            'departure_at' => $this->departed_at->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];


        return $data;
    }
}
