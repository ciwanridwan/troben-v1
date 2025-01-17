<?php

namespace App\Http\Resources\Api\Partner;

use App\Supports\DistanceMatrix;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerNearbyResource extends JsonResource
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
            'satellite' => $this->partner_satellite,
            'regency_id' => $this->geo_regency_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address' => $this->address,
            // 'transporter' => $this->transporters,
            'distance_radian' => (float) DistanceMatrix::toXDigit($this->distance_radian, 2, ''),
            'distance_matrix' => (float) DistanceMatrix::toXDigit($this->distance_matrix, 2, ''),
        ];


        return $data;
    }
}
