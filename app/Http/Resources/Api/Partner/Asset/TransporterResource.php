<?php

namespace App\Http\Resources\Api\Partner\Asset;

use Illuminate\Http\Resources\Json\JsonResource;

class TransporterResource extends JsonResource
{
    protected array $data = [];
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        foreach ($this->resource as $transporter) {
            $this->data[] = [
                'hash' => $transporter->hash,
                'name' => $transporter->name,
                'registration_number' => $transporter->registration_number,
                'is_verified' => $transporter->is_verified,
                'verified_at' => $transporter->verified_at,
                'type' => $transporter->type,
            ];
        }

        return $this->data;
    }
}
