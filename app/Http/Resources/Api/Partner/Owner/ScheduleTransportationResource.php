<?php

namespace App\Http\Resources\Api\Partner\Owner;

use App\Http\Resources\Geo\RegencyResource;
use App\Models\Partners\ScheduleTransportation;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ScheduleTransportationResource.
 * @property  ScheduleTransportation $resource
 */
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
            // 'assignable_partner' => $this->partner,
            'assignable_partner' => PartnerResource::make($this->partner),
            // 'origin_regency' => $this->origin_regency,
            'origin_regency' => RegencyResource::make($this->origin_regency),
            // 'destination_regency' => $this->destination_regency,
            'destination_regency' => RegencyResource::make($this->destination_regency),
            'departure_at' => $this->departed_at->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];


        return $data;
    }
}
