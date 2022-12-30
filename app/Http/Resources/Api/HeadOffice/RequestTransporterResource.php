<?php

namespace App\Http\Resources\Api\HeadOffice;

use Illuminate\Http\Resources\Json\JsonResource;

class RequestTransporterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'hash' => $this->hash,
            'manifest_code' => $this->code ? $this->code->content : null,
            'origin_partner' => [
                'code' => $this->origin_partner ? $this->origin_partner->code : null,
                'address' => $this->origin_partner ? $this->origin_partner->address : null,
            ],
            'destination_partner' => [
                'code' => $this->partner ? $this->partner->code : null,
                'address' => $this->partner ? $this->partner->address : null,
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'total_weight' => $this->weight_borne_total
        ];
    }
}
